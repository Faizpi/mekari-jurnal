<?php

namespace App\Http\Controllers;

use App\Pembelian;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PembelianController extends Controller
{
    /**
     * Menampilkan daftar pembelian dengan kalkulasi ringkasan.
     */
    public function index()
    {
        $allPembelian = [];

        // Cek role user
        if (Auth::user()->role == 'admin') {
            // Admin: ambil semua data
            $allPembelian = Pembelian::with('user')->latest()->get();
        } else {
            // User: ambil hanya data milik sendiri
            $allPembelian = Pembelian::where('user_id', Auth::id())
                                      ->with('user')
                                      ->latest()
                                      ->get();
        }

        // Kalkulasi untuk Kartu Ringkasan
        $fakturBelumDibayar = $allPembelian->where('status', '!=', 'Lunas')->count();
        $fakturTelatBayar = $allPembelian->where('tgl_jatuh_tempo', '<', Carbon::now())
                                          ->where('status', '!=', 'Lunas')
                                          ->count();

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
        return view('pembelian.create');
    }

    /**
     * Menyimpan data baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'staf_penyetuju' => 'required|string|max:255',
            'tgl_transaksi' => 'required|date',
            'urgensi' => 'required|string',
            'produk' => 'required|array',
            'kuantitas' => 'required|array',
        ]);

        foreach ($request->produk as $index => $produk) {
            Pembelian::create([
                // 1. Tambahkan user_id dari user yang sedang login
                'user_id' => Auth::id(),
                // 2. Set status default
                'status' => 'Pending',

                // 3. Data lain dari form
                'staf_penyetuju' => $request->staf_penyetuju,
                'email_penyetuju' => $request->email_penyetuju,
                'tgl_transaksi' => $request->tgl_transaksi,
                'tgl_jatuh_tempo' => $request->tgl_jatuh_tempo,
                'urgensi' => $request->urgensi,
                'tahun_anggaran' => $request->tahun_anggaran,
                'tag' => $request->tag,
                'memo' => $request->memo,
                
                // 4. Data per baris
                'total_barang' => $request->kuantitas[$index],
            ]);
        }

        return redirect()->route('pembelian.index')->with('success', 'Permintaan pembelian berhasil diajukan dan menunggu persetujuan.');
    }

    /**
     * Menampilkan form untuk mengedit data.
     */
    public function edit(Pembelian $pembelian)
    {
        return view('pembelian.edit', compact('pembelian'));
    }

    /**
     * Mengupdate data di database.
     */
    public function update(Request $request, Pembelian $pembelian)
    {
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
        $pembelian->delete();

        return redirect()->route('pembelian.index')->with('success', 'Data pembelian berhasil dihapus.');
    }

    public function approve(Pembelian $pembelian)
    {
        // 1. (CONTOH) Kirim data ke API Mekari Jurnal
        /*
        $response = Http::withToken('YOUR_API_KEY')->post('https://api.mekari.com/v1/purchase-orders', [
            'tanggal' => $pembelian->tgl_transaksi,
            'total_barang' => $pembelian->total_barang,
        ]);

        if (!$response->successful()) {
            return redirect()->route('pembelian.index')->with('error', 'Gagal mengirim data ke API Jurnal.');
        }
        */

        // 2. Update status di database LOKAL
        $pembelian->status = 'Approved';
        $pembelian->save();

        return redirect()->route('pembelian.index')->with('success', 'Data pembelian berhasil disetujui.');
    }
}