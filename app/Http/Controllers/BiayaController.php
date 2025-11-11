<?php

namespace App\Http\Controllers;

use App\Biaya;
use App\BiayaItem; // Pastikan ini ada
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class BiayaController extends Controller
{
    /**
     * Tampilkan daftar biaya (berdasarkan role).
     */
    public function index()
    {
        $query = null;

        // Cek role user
        if (Auth::user()->role == 'admin') {
            // Admin: ambil query dasar untuk semua data
            $query = Biaya::with('user');
        } else {
            // User: ambil query dasar HANYA untuk data milik sendiri
            $query = Biaya::where('user_id', Auth::id())->with('user');
        }

        // =================================================================
        // INI BAGIAN YANG DIPERBAIKI
        // =================================================================
        
        // Kalkulasi untuk Kartu Ringkasan
        $totalBulanIni = (clone $query) // Clone query agar tidak bentrok
            ->whereYear('tgl_transaksi', Carbon::now()->year)
            ->whereMonth('tgl_transaksi', Carbon::now()->month)
            ->sum('grand_total'); // <-- DIUBAH DARI 'total'

        $total30Hari = (clone $query)
            ->where('tgl_transaksi', '>=', Carbon::now()->subDays(30))
            ->sum('grand_total'); // <-- DIUBAH DARI 'total'

        $totalBelumDibayar = (clone $query)
            ->whereIn('status', ['Pending', 'Rejected'])
            ->sum('grand_total'); // <-- DIUBAH DARI 'total'

        // =================================================================

        // Ambil data untuk tabel, setelah semua kalkulasi selesai
        $allBiaya = $query->latest()->get();

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
        $request->validate([
            'bayar_dari' => 'required|string',
            'tgl_transaksi' => 'required|date',
            'tax_percentage' => 'required|numeric|min:0', // Validasi persentase
            'lampiran' => 'nullable|file|mimes:jpg,png,pdf,zip,doc,docx|max:2048',
            'kategori' => 'required|array|min:1',
            'total' => 'required|array|min:1',
            'kategori.*' => 'required|string|max:255',
            'total.*' => 'required|numeric|min:0',
        ]);

        $path = null;
        if ($request->hasFile('lampiran')) {
            $path = $request->file('lampiran')->store('lampiran_biaya', 'public');
        }

        // 2. Hitung Subtotal (dari item)
        $subTotal = 0;
        foreach ($request->total as $index => $jumlah) {
            $subTotal += $jumlah ?? 0;
        }
        
        // 3. Ambil Persentase Pajak
        $pajakPersen = $request->tax_percentage ?? 0;

        // 4. Hitung Jumlah Pajak & Grand Total
        $jumlahPajak = $subTotal * ($pajakPersen / 100);
        $grandTotal = $subTotal + $jumlahPajak;

        // 5. Buat Data Induk (Biaya)
        $biayaInduk = Biaya::create([
            'user_id' => Auth::id(),
            'status' => 'Pending',
            'bayar_dari' => $request->bayar_dari,
            'penerima' => $request->penerima,
            'alamat_penagihan' => $request->alamat_penagihan,
            'tgl_transaksi' => $request->tgl_transaksi,
            'cara_pembayaran' => $request->cara_pembayaran,
            'tag' => $request->tag,
            'memo' => $request->memo,
            'lampiran_path' => $path,
            'tax_percentage' => $pajakPersen, // Simpan persentasenya
            'grand_total' => $grandTotal,
        ]);

        // 6. Looping untuk menyimpan Data Rincian (BiayaItem)
        foreach ($request->kategori as $index => $kategori) {
            BiayaItem::create([
                'biaya_id' => $biayaInduk->id,
                'kategori' => $kategori,
                'deskripsi' => $request->deskripsi_akun[$index] ?? null,
                'jumlah' => $request->total[$index] ?? 0,
            ]);
        }

        return redirect()->route('biaya.index')->with('success', 'Data biaya berhasil diajukan.');
    }

    /**
     * Menampilkan halaman detail untuk satu data biaya.
     */
    public function show(Biaya $biaya)
    {
        if (auth()->user()->role != 'admin' && $biaya->user_id != auth()->id()) {
            return redirect()->route('biaya.index')->with('error', 'Akses ditolak.');
        }
        $biaya->load('items', 'user');
        return view('biaya.show', compact('biaya'));
    }

    /**
     * Menampilkan form untuk mengedit data biaya.
     */
    public function edit(Biaya $biaya)
    {
        // (Logika edit perlu disesuaikan untuk memuat items, tapi kita fokus ke index dulu)
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
        // (Logika update juga perlu disesuaikan)
        // ...
    }

    /**
     * Menghapus data biaya dari database.
     */
    public function destroy(Biaya $biaya)
    {
        if (Auth::user()->role != 'admin' && $biaya->user_id != Auth::id()) {
             return redirect()->route('biaya.index')->with('error', 'Anda tidak punya hak akses untuk menghapus data ini.');
        }
        
        // Karena kita set 'onDelete('cascade')' di migrasi,
        // Menghapus induk (biaya) akan otomatis menghapus semua rincian (biaya_items).
        $biaya->delete();
        return redirect()->route('biaya.index')->with('success', 'Data biaya berhasil dihapus.');
    }

    /**
     * Menyetujui data biaya (HANYA update status di DB lokal).
     */
    public function approve(Biaya $biaya)
    {
        if (auth()->user()->role != 'admin') {
             return redirect()->route('biaya.index')->with('error', 'Akses ditolak.');
        }
        $biaya->status = 'Approved';
        $biaya->save();
        return redirect()->route('biaya.index')->with('success', 'Data biaya berhasil disetujui.');
    }

    public function print(Biaya $biaya)
    {
        // Keamanan: User hanya bisa print data sendiri, Admin bisa semua
        if (auth()->user()->role != 'admin' && $biaya->user_id != auth()->id()) {
            return redirect()->route('biaya.index')->with('error', 'Akses ditolak.');
        }

        // Muat relasi items dan user (sama seperti 'show')
        $biaya->load('items', 'user');

        // Kembalikan view 'print.blade.php' yang baru
        return view('biaya.print', compact('biaya'));
    }
}