<?php

namespace App\Http\Controllers;

use App\Penjualan;
use App\PenjualanItem;
use App\Produk;
use App\Gudang;
use App\Kontak;
use App\GudangProduk;
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    /**
     * Menampilkan daftar penjualan (berdasarkan role).
     */
    public function index()
    {
        $query = null;
        if (Auth::user()->role == 'admin') {
            $query = Penjualan::with('user', 'gudang'); // Muat relasi gudang
        } else {
            // User biasa hanya melihat data dari gudangnya sendiri
            $userGudangId = Auth::user()->gudang_id;
            $query = Penjualan::where('gudang_id', $userGudangId)->with('user', 'gudang');
        }

        // Kalkulasi Kartu Ringkasan
        $totalBelumDibayar = (clone $query)->whereIn('status', ['Pending', 'Approved'])->sum('grand_total');
        
        $totalTelatDibayar = (clone $query)
            ->where('status', 'Approved') // Hanya yang sudah disetujui
            ->whereNotNull('tgl_jatuh_tempo')
            ->where('tgl_jatuh_tempo', '<', Carbon::now())
            ->sum('grand_total');

        $pelunasan30Hari = (clone $query)
            ->where('status', 'Lunas')
            ->where('updated_at', '>=', Carbon::now()->subDays(30))
            ->sum('grand_total');

        // Ambil data untuk tabel
        $allPenjualan = $query->latest()->get();

        return view('penjualan.index', [
            'penjualans' => $allPenjualan,
            'totalBelumDibayar' => $totalBelumDibayar,
            'totalTelatDibayar' => $totalTelatDibayar,
            'pelunasan30Hari' => $pelunasan30Hari,
        ]);
    }

    /**
     * Menampilkan form untuk membuat data baru.
     */
    public function create()
    {
        $produks = Produk::all();
        $gudangs = Gudang::all();
        $kontaks = Kontak::all();
        return view('penjualan.create', compact('produks', 'gudangs', 'kontaks'));
    }

    /**
     * Menyimpan data baru ke database (Struktur Induk & Rincian).
     */
    public function store(Request $request)
    {
        $request->validate([
            'pelanggan' => 'required|string|max:255',
            'tgl_transaksi' => 'required|date',
            'gudang_id' => 'required|exists:gudangs,id', // Validasi gudang
            'tax_percentage' => 'required|numeric|min:0',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048',
            
            'produk_id' => 'required|array|min:1',
            'kuantitas' => 'required|array|min:1',
            'harga_satuan' => 'required|array|min:1',
            
            'produk_id.*' => 'required|exists:produks,id',
            'kuantitas.*' => 'required|numeric|min:1',
            'harga_satuan.*' => 'required|numeric|min:0',
        ]);

        $path = null;
        if ($request->hasFile('lampiran')) {
            $path = $request->file('lampiran')->store('lampiran_penjualan', 'public');
        }

        $subTotal = 0;
        foreach ($request->produk_id as $index => $produkId) {
            $quantity = $request->kuantitas[$index] ?? 0;
            $price = $request->harga_satuan[$index] ?? 0;
            $discount = $request->diskon[$index] ?? 0;
            $subTotal += ($quantity * $price) * (1 - ($discount / 100));
        }
        
        $pajakPersen = $request->tax_percentage ?? 0;
        $jumlahPajak = $subTotal * ($pajakPersen / 100);
        $grandTotal = $subTotal + $jumlahPajak;

        DB::beginTransaction();
        try {
            $penjualanInduk = Penjualan::create([
                'user_id' => Auth::id(),
                'status' => 'Pending',
                'gudang_id' => $request->gudang_id,
                'pelanggan' => $request->pelanggan,
                'email' => $request->email,
                'alamat_penagihan' => $request->alamat_penagihan,
                'tgl_transaksi' => $request->tgl_transaksi,
                'tgl_jatuh_tempo' => $request->tgl_jatuh_tempo,
                'syarat_pembayaran' => $request->syarat_pembayaran,
                'no_referensi' => $request->no_referensi,
                'tag' => $request->tag,
                'memo' => $request->memo,
                'lampiran_path' => $path,
                'tax_percentage' => $pajakPersen,
                'grand_total' => $grandTotal,
            ]);

            foreach ($request->produk_id as $index => $produkId) {
                $quantity = $request->kuantitas[$index] ?? 0;
                $price = $request->harga_satuan[$index] ?? 0;
                $discount = $request->diskon[$index] ?? 0;
                $jumlah_baris = ($quantity * $price) * (1 - ($discount / 100));

                PenjualanItem::create([
                    'penjualan_id' => $penjualanInduk->id,
                    'produk_id' => $produkId,
                    'deskripsi' => $request->deskripsi[$index] ?? null,
                    'kuantitas' => $quantity,
                    'unit' => $request->unit[$index] ?? null,
                    'harga_satuan' => $price,
                    'diskon' => $discount,
                    'jumlah_baris' => $jumlah_baris,
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('penjualan.index')->with('success', 'Data penjualan berhasil diajukan.');
    }

    /**
     * Menampilkan halaman detail.
     */
    public function show(Penjualan $penjualan)
    {
        if (auth()->user()->role != 'admin' && $penjualan->user_id != auth()->id()) {
            return redirect()->route('penjualan.index')->with('error', 'Akses ditolak.');
        }
        $penjualan->load('items', 'user', 'gudang', 'items.produk');
        return view('penjualan.show', compact('penjualan'));
    }

    /**
     * Menampilkan form edit.
     */
    public function edit(Penjualan $penjualan)
    {
        // Hanya Admin yang bisa edit
        if (Auth::user()->role != 'admin') {
             return redirect()->route('penjualan.index')->with('error', 'Hanya Admin yang dapat mengedit data.');
        }

        $produks = Produk::all();
        $gudangs = Gudang::all();
        $kontaks = Kontak::all();
        $penjualan->load('items');
        
        return view('penjualan.edit', compact('penjualan', 'produks', 'gudangs', 'kontaks'));
    }

    /**
     * Mengupdate data induk DAN rincian.
     */
    public function update(Request $request, Penjualan $penjualan)
    {
         // Hanya Admin yang bisa update
         if (auth()->user()->role != 'admin') {
             return redirect()->route('penjualan.index')->with('error', 'Hanya Admin yang dapat mengedit data.');
        }
        
        $request->validate([
            'pelanggan' => 'required|string|max:255',
            'tgl_transaksi' => 'required|date',
            'gudang_id' => 'required|exists:gudangs,id',
            'tax_percentage' => 'required|numeric|min:0',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048',
            'produk_id' => 'required|array|min:1',
            'produk_id.*' => 'required|exists:produks,id',
            'kuantitas.*' => 'required|numeric|min:1',
            'harga_satuan.*' => 'required|numeric|min:0',
        ]);
        
        $path = $penjualan->lampiran_path;
        if ($request->hasFile('lampiran')) {
            // TODO: Hapus file lama jika perlu
            $path = $request->file('lampiran')->store('lampiran_penjualan', 'public');
        }

        $subTotal = 0;
        foreach ($request->produk_id as $index => $produkId) {
            $quantity = $request->kuantitas[$index] ?? 0;
            $price = $request->harga_satuan[$index] ?? 0;
            $discount = $request->diskon[$index] ?? 0;
            $subTotal += ($quantity * $price) * (1 - ($discount / 100));
        }
        $pajakPersen = $request->tax_percentage ?? 0;
        $jumlahPajak = $subTotal * ($pajakPersen / 100);
        $grandTotal = $subTotal + $jumlahPajak;

        DB::beginTransaction();
        try {
            $penjualan->update([
                'status' => 'Pending',
                'pelanggan' => $request->pelanggan,
                'email' => $request->email,
                'alamat_penagihan' => $request->alamat_penagihan,
                'tgl_transaksi' => $request->tgl_transaksi,
                'tgl_jatuh_tempo' => $request->tgl_jatuh_tempo,
                'syarat_pembayaran' => $request->syarat_pembayaran,
                'no_referensi' => $request->no_referensi,
                'tag' => $request->tag,
                'gudang_id' => $request->gudang_id,
                'memo' => $request->memo,
                'lampiran_path' => $path,
                'tax_percentage' => $pajakPersen,
                'grand_total' => $grandTotal,
            ]);

            $penjualan->items()->delete();

            foreach ($request->produk_id as $index => $produkId) {
                $quantity = $request->kuantitas[$index] ?? 0;
                $price = $request->harga_satuan[$index] ?? 0;
                $discount = $request->diskon[$index] ?? 0;
                $jumlah_baris = ($quantity * $price) * (1 - ($discount / 100));

                PenjualanItem::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id' => $produkId,
                    'deskripsi' => $request->deskripsi[$index] ?? null,
                    'kuantitas' => $quantity,
                    'unit' => $request->unit[$index] ?? null,
                    'harga_satuan' => $price,
                    'diskon' => $discount,
                    'jumlah_baris' => $jumlah_baris,
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('penjualan.index')->with('success', 'Data penjualan berhasil diperbarui.');
    }

    /**
     * Menghapus data induk.
     */
    public function destroy(Penjualan $penjualan)
    {
        if (auth()->user()->role != 'admin' && $penjualan->user_id != auth()->id()) {
             return redirect()->route('penjualan.index')->with('error', 'Akses ditolak.');
        }
        if ($penjualan->status != 'Pending' && auth()->user()->role != 'admin') {
            return redirect()->route('penjualan.index')->with('error', 'Data yang sudah diproses tidak bisa dihapus.');
         }
        
        $penjualan->delete();
        return redirect()->route('penjualan.index')->with('success', 'Data penjualan berhasil dihapus.');
    }

    /**
     * Menyetujui data penjualan DAN MENGURANGI STOK.
     */
    public function approve(Penjualan $penjualan)
    {
        if (auth()->user()->role != 'admin') {
             return redirect()->route('penjualan.index')->with('error', 'Akses ditolak.');
        }
        $gudangId = $penjualan->gudang_id; 
        if (!$gudangId) {
            return redirect()->route('penjualan.index')->with('error', 'Gagal! Transaksi ini tidak terhubung ke gudang.');
        }

        DB::beginTransaction();
        try {
            foreach ($penjualan->items as $item) {
                $stok = GudangProduk::where('gudang_id', $gudangId) 
                                    ->where('produk_id', $item->produk_id)
                                    ->first();
                if (!$stok || $stok->stok < $item->kuantitas) {
                    DB::rollBack();
                    $namaProduk = $item->produk->nama_produk ?? 'ID: ' . $item->produk_id;
                    return redirect()->route('penjualan.index')->with('error', "Stok tidak cukup di gudang terkait untuk produk: $namaProduk. Transaksi dibatalkan.");
                }
                $stok->decrement('stok', $item->kuantitas);
            }
            $penjualan->status = 'Approved';
            $penjualan->save();
            DB::commit();
            return redirect()->route('penjualan.index')->with('success', 'Penjualan disetujui. Stok telah dikurangi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('penjualan.index')->with('error', 'Error! Gagal perbarui stok. ' . $e->getMessage());
        }
    }

    /**
     * Menandai data sebagai Lunas (Admin).
     */
    public function markAsPaid(Penjualan $penjualan)
    {
        if (auth()->user()->role != 'admin') {
             return redirect()->route('penjualan.index')->with('error', 'Akses ditolak.');
        }
        $penjualan->status = 'Lunas';
        $penjualan->save();
        return redirect()->route('penjualan.index')->with('success', 'Penjualan telah ditandai LUNAS.');
    }

    /**
     * Menampilkan halaman print struk untuk penjualan.
     */
    public function print(Penjualan $penjualan)
    {
        if (auth()->user()->role != 'admin' && $penjualan->user_id != auth()->id()) {
            return redirect()->route('penjualan.index')->with('error', 'Akses ditolak.');
        }
        $penjualan->load('items', 'user', 'gudang', 'items.produk');
        return view('penjualan.print', compact('penjualan'));
    }
}