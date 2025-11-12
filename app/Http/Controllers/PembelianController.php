<?php

namespace App\Http\Controllers;

use App\Pembelian;
use App\PembelianItem;
use App\Produk;
use App\Gudang;
use App\Kontak; // <-- Pastikan ini ada
use App\GudangProduk;
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class PembelianController extends Controller
{
    /**
     * Menampilkan daftar pembelian (berdasarkan role).
     */
    public function index()
    {
        $query = null;
        if (Auth::user()->role == 'admin') {
            $query = Pembelian::with('user', 'gudang');
        } else {
            $userGudangId = Auth::user()->gudang_id;
            $query = Pembelian::where('gudang_id', $userGudangId)->with('user', 'gudang');
        }
        
        $allPembelian = $query->latest()->get();

        $fakturBelumDibayar = (clone $query)->whereIn('status', ['Pending', 'Approved'])->sum('grand_total');
        $fakturTelatBayar = (clone $query)->where('status', 'Approved')
                                          ->whereNotNull('tgl_jatuh_tempo')
                                          ->where('tgl_jatuh_tempo', '<', Carbon::now())
                                          ->sum('grand_total');

        return view('pembelian.index', [
            'pembelians' => $allPembelian,
            'fakturBelumDibayar' => $fakturBelumDibayar,
            'fakturTelatBayar' => $fakturTelatBayar,
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
        return view('pembelian.create', compact('produks', 'gudangs', 'kontaks'));
    }

    /**
     * Menyimpan data baru ke database (Struktur Induk & Rincian).
     */
    public function store(Request $request)
    {
        $request->validate([
            'staf_penyetuju' => 'required|string|max:255',
            'tgl_transaksi' => 'required|date',
            'urgensi' => 'required|string',
            'gudang_id' => 'required|exists:gudangs,id',
            'tax_percentage' => 'required|numeric|min:0',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048',
            'produk_id' => 'required|array|min:1',
            'produk_id.*' => 'required|exists:produks,id',
            'kuantitas.*' => 'required|numeric|min:1',
            'harga_satuan.*' => 'required|numeric|min:0',
        ]);

        $path = null;
        if ($request->hasFile('lampiran')) {
            $path = $request->file('lampiran')->store('lampiran_pembelian', 'public');
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
            $pembelianInduk = Pembelian::create([
                'user_id' => Auth::id(),
                'status' => 'Pending',
                'gudang_id' => $request->gudang_id,
                'staf_penyetuju' => $request->staf_penyetuju,
                'email_penyetuju' => $request->email_penyetuju,
                'tgl_transaksi' => $request->tgl_transaksi,
                'tgl_jatuh_tempo' => $request->tgl_jatuh_tempo,
                'urgensi' => $request->urgensi,
                'tahun_anggaran' => $request->tahun_anggaran,
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

                PembelianItem::create([
                    'pembelian_id' => $pembelianInduk->id,
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

        return redirect()->route('pembelian.index')->with('success', 'Permintaan pembelian berhasil diajukan.');
    }

    /**
     * Menampilkan halaman detail.
     */
    public function show(Pembelian $pembelian)
    {
        if (auth()->user()->role != 'admin' && $pembelian->user_id != auth()->id()) {
            return redirect()->route('pembelian.index')->with('error', 'Akses ditolak.');
        }
        $pembelian->load('items', 'user', 'gudang', 'items.produk');
        return view('pembelian.show', compact('pembelian'));
    }

    /**
     * ======================================================
     * FUNGSI EDIT YANG DIPERBARUI
     * ======================================================
     * Menampilkan form edit dengan data induk DAN rincian.
     */
    public function edit(Pembelian $pembelian)
    {
        if (Auth::user()->role != 'admin' && $pembelian->user_id != Auth::id()) {
             return redirect()->route('pembelian.index')->with('error', 'Akses ditolak.');
        }
        if ($pembelian->status != 'Pending' && Auth::user()->role != 'admin') {
            return redirect()->route('pembelian.index')->with('error', 'Data yang sudah diproses tidak bisa diedit.');
         }

        $produks = Produk::all();
        $gudangs = Gudang::all();
        $kontaks = Kontak::all();
        $pembelian->load('items'); // Muat rincian item
        
        return view('pembelian.edit', compact('pembelian', 'produks', 'gudangs', 'kontaks'));
    }

    /**
     * ======================================================
     * FUNGSI UPDATE YANG DIPERBARUI
     * ======================================================
     * Mengupdate data induk DAN rincian.
     */
    public function update(Request $request, Pembelian $pembelian)
    {
        if (auth()->user()->role != 'admin' && $pembelian->user_id != auth()->id()) {
             return redirect()->route('pembelian.index')->with('error', 'Akses ditolak.');
        }
        
        // 1. Validasi (Sama persis seperti store)
        $request->validate([
            'staf_penyetuju' => 'required|string|max:255',
            'tgl_transaksi' => 'required|date',
            'urgensi' => 'required|string',
            'gudang_id' => 'required|exists:gudangs,id',
            'tax_percentage' => 'required|numeric|min:0',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048',
            'produk_id' => 'required|array|min:1',
            'produk_id.*' => 'required|exists:produks,id',
            'kuantitas.*' => 'required|numeric|min:1',
            'harga_satuan.*' => 'required|numeric|min:0',
        ]);
        
        // 2. Proses upload file (jika ada file baru)
        $path = $pembelian->lampiran_path;
        if ($request->hasFile('lampiran')) {
            // TODO: Hapus file lama jika perlu
            $path = $request->file('lampiran')->store('lampiran_pembelian', 'public');
        }

        // 3. Hitung Grand Total baru
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
            // 4. Update Data Induk (Pembelian)
            $pembelian->update([
                'status' => 'Pending', // Set status kembali ke Pending
                'gudang_id' => $request->gudang_id,
                'staf_penyetuju' => $request->staf_penyetuju,
                'email_penyetuju' => $request->email_penyetuju,
                'tgl_transaksi' => $request->tgl_transaksi,
                'tgl_jatuh_tempo' => $request->tgl_jatuh_tempo,
                'urgensi' => $request->urgensi,
                'tahun_anggaran' => $request->tahun_anggaran,
                'tag' => $request->tag,
                'memo' => $request->memo,
                'lampiran_path' => $path,
                'tax_percentage' => $pajakPersen,
                'grand_total' => $grandTotal,
            ]);

            // 5. Hapus semua rincian item LAMA
            $pembelian->items()->delete();

            // 6. Buat ulang rincian item BARU
            foreach ($request->produk_id as $index => $produkId) {
                $quantity = $request->kuantitas[$index] ?? 0;
                $price = $request->harga_satuan[$index] ?? 0;
                $discount = $request->diskon[$index] ?? 0;
                $jumlah_baris = ($quantity * $price) * (1 - ($discount / 100));

                PembelianItem::create([
                    'pembelian_id' => $pembelian->id,
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

        return redirect()->route('pembelian.index')->with('success', 'Data pembelian berhasil diperbarui.');
    }

    /**
     * Menghapus data dari database.
     */
    public function destroy(Pembelian $pembelian)
    {
        if (auth()->user()->role != 'admin' && $pembelian->user_id != auth()->id()) {
             return redirect()->route('pembelian.index')->with('error', 'Akses ditolak.');
        }
        
        $pembelian->delete();
        return redirect()->route('pembelian.index')->with('success', 'Data pembelian berhasil dihapus.');
    }

    /**
     * Menyetujui data pembelian DAN MENAMBAH STOK.
     */
    public function approve(Pembelian $pembelian)
    {
        if (auth()->user()->role != 'admin') {
             return redirect()->route('pembelian.index')->with('error', 'Akses ditolak.');
        }

        $gudangId = $pembelian->gudang_id; 
        if (!$gudangId) {
            return redirect()->route('pembelian.index')->with('error', 'Gagal! Transaksi ini tidak terhubung ke gudang.');
        }

        DB::beginTransaction();
        try {
            foreach ($pembelian->items as $item) {
                $stok = GudangProduk::firstOrCreate(
                    [
                        'gudang_id' => $gudangId,
                        'produk_id' => $item->produk_id
                    ],
                    ['stok' => 0]
                );
                $stok->increment('stok', $item->kuantitas);
            }

            $pembelian->status = 'Approved';
            $pembelian->save();
            DB::commit();

            return redirect()->route('pembelian.index')->with('success', 'Pembelian disetujui. Stok telah ditambahkan ke gudang.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('pembelian.index')->with('error', 'Error! Gagal memperbarui stok. ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan halaman print struk untuk pembelian.
     */
    public function print(Pembelian $pembelian)
    {
        if (auth()->user()->role != 'admin' && $pembelian->user_id != auth()->id()) {
            return redirect()->route('pembelian.index')->with('error', 'Akses ditolak.');
        }

        $pembelian->load('items', 'user', 'gudang', 'items.produk');
        return view('pembelian.print', compact('pembelian'));
    }
}