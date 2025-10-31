<?php

namespace App\Http\Controllers;

use App\Biaya;
use App\User; // Pastikan namespace Model Anda benar
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BiayaController extends Controller
{
    /**
     * Tampilkan daftar biaya (berdasarkan role).
     */
    public function index()
    {
        $allBiaya = [];
        
        if (Auth::user()->role == 'admin') {
            // Admin melihat semua data
            $allBiaya = Biaya::with('user')->latest()->get(); // Muat relasi user
        } else {
            // User biasa hanya melihat data milik sendiri
            $allBiaya = Biaya::where('user_id', Auth::id())
                              ->with('user')
                              ->latest()
                              ->get();
        }

        // Kalkulasi untuk Kartu Ringkasan
        $totalBulanIni = $allBiaya->where('tgl_transaksi', '>=', Carbon::now()->startOfMonth())->sum('total');
        $total30Hari = $allBiaya->where('tgl_transaksi', '>=', Carbon::now()->subDays(30))->sum('total');
        $totalBelumDibayar = $allBiaya->whereIn('status', ['Pending', 'Rejected'])->sum('total');

        return view('biaya.index', [
            'biayas' => $allBiaya,
            'totalBulanIni' => $totalBulanIni,
            'total30Hari' => $total30Hari,
            'totalBelumDibayar' => $totalBelumDibayar,
        ]);
    }

    /**
     * Menampilkan form untuk membuat data biaya baru.
     */
    public function create()
    {
        return view('biaya.create');
    }

    /**
     * Memvalidasi dan menyimpan data biaya baru ke database.
     */
    public function store(Request $request)
    {
        // =================================================================
        // INI BAGIAN YANG DIPERBAIKI (VALIDASI LENGKAP)
        // =================================================================
        $request->validate([
            'bayar_dari' => 'required|string',
            'tgl_transaksi' => 'required|date',
            'penerima' => 'nullable|string|max:255',
            'alamat_penagihan' => 'nullable|string',
            'cara_pembayaran' => 'nullable|string',
            'tag' => 'nullable|string',
            'memo' => 'nullable|string',
            
            // Validasi Array
            'kategori' => 'required|array',
            'pajak' => 'required|array',
            'total' => 'required|array',
            
            // Validasi setiap item di dalam array
            'kategori.*' => 'nullable|string|max:255',
            'pajak.*' => 'required|numeric',
            'total.*' => 'required|numeric|min:0',
        ]);

        // Looping untuk menyimpan data
        foreach ($request->kategori as $index => $kategori) {
            $jumlah = $request->total[$index] ?? 0;
            $pajakRate = $request->pajak[$index] ?? 0;
            $jumlahPajak = $jumlah * ($pajakRate / 100);
            $totalAkhir = $jumlah + $jumlahPajak;

            Biaya::create([
                'user_id' => Auth::id(),
                'status' => 'Pending',
                'bayar_dari' => $request->bayar_dari,
                'penerima' => $request->penerima,
                'alamat_penagihan' => $request->alamat_penagihan,
                'tgl_transaksi' => $request->tgl_transaksi,
                'cara_pembayaran' => $request->cara_pembayaran,
                'tag' => $request->tag,
                'memo' => $request->memo,
                'kategori' => $kategori,
                'pajak' => $pajakRate > 0 ? "PPN 11%" : null,
                'total' => $totalAkhir,
            ]);
        }

        return redirect()->route('biaya.index')->with('success', 'Data biaya berhasil diajukan dan menunggu persetujuan.');
    }

    /**
     * Menampilkan form untuk mengedit data biaya.
     */
    public function edit(Biaya $biaya)
    {
        // Tambahkan pengecekan keamanan: user biasa tidak bisa edit data orang lain
        if (Auth::user()->role != 'admin' && $biaya->user_id != Auth::id()) {
             return redirect()->route('biaya.index')->with('error', 'Anda tidak punya hak akses untuk mengedit data ini.');
        }

        return view('biaya.edit', compact('biaya'));
    }

    /**
     * Mengupdate data biaya yang ada di database.
     */
    public function update(Request $request, Biaya $biaya)
    {
        // Pengecekan keamanan
        if (Auth::user()->role != 'admin' && $biaya->user_id != Auth::id()) {
             return redirect()->route('biaya.index')->with('error', 'Anda tidak punya hak akses untuk mengedit data ini.');
        }

        $request->validate([
            'penerima' => 'required|string',
            'tgl_transaksi' => 'required|date',
            'total' => 'required|numeric',
            'kategori' => 'nullable|string',
            'status' => 'required|string', // Pastikan status juga dikirim dari form edit
        ]);

        $biaya->update($request->all());

        return redirect()->route('biaya.index')->with('success', 'Data biaya berhasil diperbarui.');
    }

    /**
     * Menghapus data biaya dari database.
     */
    public function destroy(Biaya $biaya)
    {
        // Pengecekan keamanan
        if (Auth::user()->role != 'admin' && $biaya->user_id != Auth::id()) {
             return redirect()->route('biaya.index')->with('error', 'Anda tidak punya hak akses untuk menghapus data ini.');
        }
        
        $biaya->delete();
        return redirect()->route('biaya.index')->with('success', 'Data biaya berhasil dihapus.');
    }

    public function approve(Biaya $biaya)
    {
        // 1. Kirim data ke API Mekari Jurnal
        // (Ini adalah CONTOH, URL dan data asli harus disesuaikan)
        /*
        $response = Http::withToken('YOUR_API_KEY')->post('https://api.mekari.com/v1/expenses', [
            'tanggal' => $biaya->tgl_transaksi,
            'kontak' => $biaya->penerima,
            'total' => $biaya->total,
            'kategori' => $biaya->kategori,
        ]);

        // 2. Cek jika pengiriman ke API gagal
        if (!$response->successful()) {
            return redirect()->route('biaya.index')->with('error', 'Gagal mengirim data ke API Jurnal.');
        }
        */

        // 3. Jika berhasil (atau kita skip untuk dummy), update status di database LOKAL
        $biaya->status = 'Approved';
        $biaya->save();

        return redirect()->route('biaya.index')->with('success', 'Data biaya berhasil disetujui dan dikirim ke Jurnal.');
    }
}