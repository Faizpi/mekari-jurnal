<?php

namespace App\Http\Controllers;

use App\Pembelian;
use App\PembelianItem;
use App\Produk;
use App\GudangProduk; // <-- TAMBAHKAN INI
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB; // <-- TAMBAHKAN INI

class PembelianController extends Controller
{
    // ... (method index, create, store, show, edit, update, destroy tidak berubah) ...
    
    /**
     * Menampilkan daftar pembelian (berdasarkan role).
     */
    public function index()
    {
        $query = null;
        if (Auth::user()->role == 'admin') {
            $query = Pembelian::with('user');
        } else {
            $query = Pembelian::where('user_id', Auth::id())->with('user');
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
        return view('pembelian.create', compact('produks'));
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
            $path = $request->file('lampiran')->store('lampiran_pembelian', 'public');
        }

        $grandTotal = 0;
        foreach ($request->produk_id as $index => $produkId) {
            $quantity = $request->kuantitas[$index] ?? 0;
            $price = $request->harga_satuan[$index] ?? 0;
            $discount = $request->diskon[$index] ?? 0;
            $jumlah_baris = ($quantity * $price) * (1 - ($discount / 100));
            $grandTotal += $jumlah_baris;
        }

        $pembelianInduk = Pembelian::create([
            'user_id' => Auth::id(),
            'status' => 'Pending',
            'staf_penyetuju' => $request->staf_penyetuju,
            'email_penyetuju' => $request->email_penyetuju,
            'tgl_transaksi' => $request->tgl_transaksi,
            'tgl_jatuh_tempo' => $request->tgl_jatuh_tempo,
            'urgensi' => $request->urgensi,
            'tahun_anggaran' => $request->tahun_anggaran,
            'tag' => $request->tag,
            'memo' => $request->memo,
            'lampiran_path' => $path,
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
        $pembelian->load('items', 'user', 'items.produk');
        return view('pembelian.show', compact('pembelian'));
    }

    /**
     * Menampilkan form edit.
     */
    public function edit(Pembelian $pembelian)
    {
        if (auth()->user()->role != 'admin' && $pembelian->user_id != auth()->id()) {
             return redirect()->route('pembelian.index')->with('error', 'Akses ditolak.');
        }
        $produks = Produk::all();
        $pembelian->load('items');
        return view('pembelian.edit', compact('pembelian', 'produks'));
    }

    /**
     * Mengupdate data di database.
     */
    public function update(Request $request, Pembelian $pembelian)
    {
        // (Logika update yang lengkap untuk induk-rincian lebih kompleks,
        // kita fokus ke approve dulu)
        if (auth()->user()->role != 'admin' && $pembelian->user_id != auth()->id()) {
             return redirect()->route('pembelian.index')->with('error', 'Akses ditolak.');
        }
        
        $request->validate([
            'staf_penyetuju' => 'required|string|max:255',
            'tgl_transaksi' => 'required|date',
            'urgensi' => 'required|string',
            'status' => 'required|string',
        ]);

        $pembelian->update($request->all());

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
     * Menampilkan halaman print struk untuk pembelian.
     */
    public function print(Pembelian $pembelian)
    {
        // Keamanan
        if (auth()->user()->role != 'admin' && $pembelian->user_id != auth()->id()) {
            return redirect()->route('pembelian.index')->with('error', 'Akses ditolak.');
        }

        $pembelian->load('items', 'user', 'items.produk');

        // PASTIKAN BARIS INI MENGEMBALIKAN 'view'
        return view('pembelian.print', compact('pembelian'));
    }
    
    // ======================================================
    // LOGIKA INVENTARIS BARU
    // ======================================================
    /**
     * Menyetujui data pembelian DAN MENAMBAH STOK.
     */
    public function approve(Pembelian $pembelian)
    {
        if (auth()->user()->role != 'admin') {
             return redirect()->route('pembelian.index')->with('error', 'Akses ditolak.');
        }

        // 1. Dapatkan gudang dari user yang MEMBUAT permintaan
        $userGudang = $pembelian->user->gudang_id;
        if (!$userGudang) {
            return redirect()->route('pembelian.index')->with('error', 'Gagal! User pembuat tidak terhubung ke gudang manapun.');
        }

        // 2. Gunakan DB Transaction agar aman (jika satu gagal, semua batal)
        DB::beginTransaction();
        try {
            // 3. Loop setiap item dalam pembelian ini
            foreach ($pembelian->items as $item) {
                // 4. Cari atau buat data stok untuk produk ini di gudang ini
                $stok = GudangProduk::firstOrCreate(
                    [
                        'gudang_id' => $userGudang,
                        'produk_id' => $item->produk_id
                    ],
                    ['stok' => 0] // Buat stok 0 jika belum ada
                );

                // 5. TAMBAH stok
                $stok->increment('stok', $item->kuantitas);
            }

            // 6. Update status pembelian menjadi 'Approved'
            $pembelian->status = 'Approved';
            $pembelian->save();

            // 7. Jika semua berhasil, simpan perubahan
            DB::commit();

            return redirect()->route('pembelian.index')->with('success', 'Pembelian disetujui. Stok telah ditambahkan ke gudang.');

        } catch (\Exception $e) {
            // 8. Jika ada error, batalkan semua
            DB::rollBack();
            return redirect()->route('pembelian.index')->with('error', 'Error! Gagal memperbarui stok. ' . $e->getMessage());
        }
    }
}