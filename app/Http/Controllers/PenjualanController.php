<?php

namespace App\Http\Controllers;

use App\Penjualan;
use App\PenjualanItem;
use App\Produk;
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
            $query = Penjualan::with('user');
        } else {
            $query = Penjualan::where('user_id', Auth::id())->with('user');
        }

        $totalBelumDibayar = (clone $query)->whereIn('status', ['Pending', 'Approved'])->sum('grand_total');
        $totalTelatDibayar = (clone $query)->where('status', 'Approved')
                                          ->whereNotNull('tgl_jatuh_tempo')
                                          ->where('tgl_jatuh_tempo', '<', Carbon::now())
                                          ->sum('grand_total');
        $pelunasan30Hari = (clone $query)->where('status', 'Lunas')
                                        ->where('updated_at', '>=', Carbon::now()->subDays(30))
                                        ->sum('grand_total');

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
        return view('penjualan.create', compact('produks'));
    }

    /**
     * Menyimpan data baru ke database (Struktur Induk & Rincian).
     */
    public function store(Request $request)
    {
        $request->validate([
            // Validasi field utama
            'pelanggan' => 'required|string|max:255',
            'tgl_transaksi' => 'required|date',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048',
            
            // Validasi Pajak Persen
            'tax_percentage' => 'required|numeric|min:0',
            
            // Validasi Array
            'produk_id' => 'required|array|min:1',
            'kuantitas' => 'required|array|min:1',
            'harga_satuan' => 'required|array|min:1',
            'produk_id.*' => 'required|exists:produks,id',
            'kuantitas.*' => 'required|numeric|min:1',
            'harga_satuan.*' => 'required|numeric|min:0',
        ]);

        // 1. Proses upload file
        $path = null;
        if ($request->hasFile('lampiran')) {
            $path = $request->file('lampiran')->store('lampiran_penjualan', 'public');
        }

        // 2. Hitung Subtotal dan Grand Total
        $subTotal = 0;
        $itemsData = []; // Tampung data item sementara

        foreach ($request->produk_id as $index => $produkId) {
            $quantity = $request->kuantitas[$index] ?? 0;
            $price = $request->harga_satuan[$index] ?? 0;
            $discount = $request->diskon[$index] ?? 0;
            $jumlah_baris = ($quantity * $price) * (1 - ($discount / 100));
            
            $subTotal += $jumlah_baris;

            // Simpan data rincian untuk loop berikutnya
            $itemsData[] = [
                'produk_id' => $produkId,
                'deskripsi' => $request->deskripsi[$index] ?? null,
                'kuantitas' => $quantity,
                'unit' => $request->unit[$index] ?? null,
                'harga_satuan' => $price,
                'diskon' => $discount,
                'jumlah_baris' => $jumlah_baris,
            ];
        }
        
        $pajakPersen = $request->tax_percentage ?? 0;
        $jumlahPajak = $subTotal * ($pajakPersen / 100);
        $grandTotal = $subTotal + $jumlahPajak;

        // 3. Buat Data Induk (Penjualan)
        $penjualanInduk = Penjualan::create([
            'user_id' => Auth::id(),
            'status' => 'Pending',
            'pelanggan' => $request->pelanggan,
            'email' => $request->email,
            'alamat_penagihan' => $request->alamat_penagihan,
            'tgl_transaksi' => $request->tgl_transaksi,
            'tgl_jatuh_tempo' => $request->tgl_jatuh_tempo,
            'syarat_pembayaran' => $request->syarat_pembayaran,
            'no_referensi' => $request->no_referensi,
            'tag' => $request->tag,
            'gudang' => $request->gudang,
            'memo' => $request->memo,
            'lampiran_path' => $path,
            'tax_percentage' => $pajakPersen, // Simpan persentase
            'grand_total' => $grandTotal, // Simpan total akhir
        ]);

        // 4. Looping untuk menyimpan Data Rincian (PenjualanItem)
        foreach ($itemsData as $item) {
            $item['penjualan_id'] = $penjualanInduk->id; // Hubungkan ke ID Induk
            PenjualanItem::create($item);
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
        $penjualan->load('items', 'user', 'items.produk');
        return view('penjualan.show', compact('penjualan'));
    }

    /**
     * Menampilkan form edit.
     */
    public function edit(Penjualan $penjualan)
    {
        if (auth()->user()->role != 'admin' && $penjualan->user_id != auth()->id()) {
             return redirect()->route('penjualan.index')->with('error', 'Akses ditolak.');
        }
        $produks = Produk::all();
        $penjualan->load('items');
        return view('penjualan.edit', compact('penjualan', 'produks'));
    }

    /**
     * Mengupdate data induk.
     */
    public function update(Request $request, Penjualan $penjualan)
    {
         // (Logika update lengkap perlu dibuat, saat ini disederhanakan)
         if (auth()->user()->role != 'admin' && $penjualan->user_id != auth()->id()) {
             return redirect()->route('penjualan.index')->with('error', 'Akses ditolak.');
        }
        
        $request->validate([
            'pelanggan' => 'required|string|max:255',
            'tgl_transaksi' => 'required|date',
            'status' => 'required|string',
        ]);
        
        $penjualan->update($request->all());
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

        $userGudang = $penjualan->user->gudang_id;
        if (!$userGudang) {
            return redirect()->route('penjualan.index')->with('error', 'Gagal! User pembuat tidak terhubung ke gudang manapun.');
        }

        DB::beginTransaction();
        try {
            foreach ($penjualan->items as $item) {
                $stok = GudangProduk::where('gudang_id', $userGudang)
                                    ->where('produk_id', $item->produk_id)
                                    ->first();

                if (!$stok || $stok->stok < $item->kuantitas) {
                    DB::rollBack();
                    $namaProduk = $item->produk->nama_produk ?? 'ID: ' . $item->produk_id;
                    return redirect()->route('penjualan.index')->with('error', "Stok tidak cukup untuk produk: $namaProduk. Transaksi dibatalkan.");
                }
                $stok->decrement('stok', $item->kuantitas);
            }

            $penjualan->status = 'Approved';
            $penjualan->save();

            DB::commit();

            return redirect()->route('penjualan.index')->with('success', 'Penjualan disetujui. Stok telah dikurangi dari gudang.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('penjualan.index')->with('error', 'Error! Gagal memperbarui stok. ' . $e->getMessage());
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
        $penjualan->load('items', 'user', 'items.produk');
        return view('penjualan.print', compact('penjualan'));
    }
}