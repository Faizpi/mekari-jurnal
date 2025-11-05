<?php

namespace App\Http\Controllers;

use App\Pembelian;
use App\PembelianItem; // <-- Tambahkan ini
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class PembelianController extends Controller
{
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

        // Ambil data untuk tabel
        $allPembelian = $query->latest()->get();

        // Kalkulasi Kartu Ringkasan
        // Kita akan clone query SEBELUM mengambil data items
        $fakturBelumDibayar = (clone $query)->where('status', '!=', 'Lunas')->count();
        $fakturTelatBayar = (clone $query)->where('tgl_jatuh_tempo', '<', Carbon::now())
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
     * Menyimpan data baru ke database (Struktur Induk & Rincian).
     */
    public function store(Request $request)
    {
        $request->validate([
            'staf_penyetuju' => 'required|string|max:255',
            'tgl_transaksi' => 'required|date',
            'urgensi' => 'required|string',
            'produk' => 'required|array',
            'kuantitas' => 'required|array',
            'produk.*' => 'required|string|max:255',
            'kuantitas.*' => 'required|numeric|min:1',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048',
        ]);

        // 1. Proses upload file
        $path = null;
        if ($request->hasFile('lampiran')) {
            $path = $request->file('lampiran')->store('lampiran_pembelian', 'public');
        }

        // 2. Buat Data Induk (Pembelian)
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
        ]);

        // 3. Looping untuk menyimpan Data Rincian (PembelianItem)
        foreach ($request->produk as $index => $produk) {
            PembelianItem::create([
                'pembelian_id' => $pembelianInduk->id, // <-- Hubungkan ke ID Induk
                'produk' => $produk,
                'deskripsi' => $request->deskripsi[$index] ?? null,
                'kuantitas' => $request->kuantitas[$index] ?? 0,
                'unit' => $request->unit[$index] ?? null,
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
        $pembelian->load('items', 'user'); // Muat relasi items dan user
        return view('pembelian.show', compact('pembelian'));
    }

    /**
     * Menampilkan form edit.
     * (Saat ini hanya edit data induk, belum termasuk rincian)
     */
    public function edit(Pembelian $pembelian)
    {
        if (auth()->user()->role != 'admin' && $pembelian->user_id != auth()->id()) {
             return redirect()->route('pembelian.index')->with('error', 'Akses ditolak.');
        }
        return view('pembelian.edit', compact('pembelian'));
    }

    /**
     * Mengupdate data induk.
     */
    public function update(Request $request, Pembelian $pembelian)
    {
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
     * Menghapus data induk (dan rinciannya).
     */
    public function destroy(Pembelian $pembelian)
    {
        if (auth()->user()->role != 'admin' && $pembelian->user_id != auth()->id()) {
             return redirect()->route('pembelian.index')->with('error', 'Akses ditolak.');
        }
        
        $pembelian->delete(); // Rincian akan ikut terhapus (onDelete cascade)
        return redirect()->route('pembelian.index')->with('success', 'Data pembelian berhasil dihapus.');
    }

    /**
     * Menyetujui data pembelian.
     */
    public function approve(Pembelian $pembelian)
    {
        if (auth()->user()->role != 'admin') {
             return redirect()->route('pembelian.index')->with('error', 'Akses ditolak.');
        }
        $pembelian->status = 'Approved';
        $pembelian->save();
        return redirect()->route('pembelian.index')->with('success', 'Data pembelian berhasil disetujui.');
    }
}