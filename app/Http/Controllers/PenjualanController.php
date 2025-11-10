<?php

namespace App\Http\Controllers;

use App\Penjualan;
use App\PenjualanItem; // Pastikan ini ada
use App\User; // Pastikan namespace Model Anda benar (App\ atau App\Models\)
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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

        // Kalkulasi untuk Kartu Ringkasan
        $totalBelumDibayar = (clone $query)
            ->whereIn('status', ['Pending', 'Approved']) // Status "Belum Dibayar" mencakup Pending DAN Approved
            ->sum('grand_total');

        $totalTelatDibayar = (clone $query)
            ->where('status', 'Approved') // Hanya yang sudah disetujui yang bisa telat
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
        return view('penjualan.create');
    }

    /**
     * Menyimpan data baru ke database (Struktur Induk & Rincian).
     */
    public function store(Request $request)
    {
        // Validasi lengkap
        $request->validate([
            // Validasi field utama
            'pelanggan' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'alamat_penagihan' => 'nullable|string',
            'tgl_transaksi' => 'required|date',
            'tgl_jatuh_tempo' => 'nullable|date|after_or_equal:tgl_transaksi',
            'syarat_pembayaran' => 'nullable|string|max:255',
            'no_referensi' => 'nullable|string|max:255',
            'tag' => 'nullable|string|max:255',
            'gudang' => 'nullable|string|max:255',
            'memo' => 'nullable|string',
            
            // Validasi file
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048', // 2MB Max
            
            // Validasi Array
            'produk' => 'required|array|min:1',
            'deskripsi' => 'nullable|array',
            'kuantitas' => 'required|array|min:1',
            'unit' => 'nullable|array',
            'harga_satuan' => 'required|array|min:1',
            'diskon' => 'nullable|array',
            
            // Validasi setiap item di dalam array
            'produk.*' => 'required|string|max:255',
            'deskripsi.*' => 'nullable|string',
            'kuantitas.*' => 'required|numeric|min:1',
            'unit.*' => 'nullable|string',
            'harga_satuan.*' => 'required|numeric|min:0',
            'diskon.*' => 'nullable|numeric|min:0|max:100',
        ]);

        // 1. Proses upload file
        $path = null;
        if ($request->hasFile('lampiran')) {
            $path = $request->file('lampiran')->store('lampiran_penjualan', 'public');
        }

        // 2. Hitung Grand Total
        $grandTotal = 0;
        foreach ($request->produk as $index => $produk) {
            $quantity = $request->kuantitas[$index] ?? 0;
            $price = $request->harga_satuan[$index] ?? 0;
            $discount = $request->diskon[$index] ?? 0;
            $jumlah_baris = ($quantity * $price) * (1 - ($discount / 100));
            $grandTotal += $jumlah_baris;
        }

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
            'grand_total' => $grandTotal,
        ]);

        // 4. Looping untuk menyimpan Data Rincian (PenjualanItem)
        foreach ($request->produk as $index => $produk) {
            $quantity = $request->kuantitas[$index] ?? 0;
            $price = $request->harga_satuan[$index] ?? 0;
            $discount = $request->diskon[$index] ?? 0;
            $jumlah_baris = ($quantity * $price) * (1 - ($discount / 100));

            PenjualanItem::create([
                'penjualan_id' => $penjualanInduk->id,
                'produk' => $produk,
                'deskripsi' => $request->deskripsi[$index] ?? null,
                'kuantitas' => $quantity,
                'unit' => $request->unit[$index] ?? null,
                'harga_satuan' => $price,
                'diskon' => $discount,
                'jumlah_baris' => $jumlah_baris,
            ]);
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
        $penjualan->load('items', 'user');
        return view('penjualan.show', compact('penjualan'));
    }

    /**
     * Menampilkan form edit.
     * (Saat ini hanya edit data induk)
     */
    public function edit(Penjualan $penjualan)
    {
        if (auth()->user()->role != 'admin' && $penjualan->user_id != auth()->id()) {
             return redirect()->route('penjualan.index')->with('error', 'Akses ditolak.');
        }
         // Hanya user yang statusnya masih Pending yang bisa edit
         if ($penjualan->status != 'Pending' && auth()->user()->role != 'admin') {
            return redirect()->route('penjualan.index')->with('error', 'Data yang sudah disetujui tidak bisa diedit.');
         }

        return view('penjualan.edit', compact('penjualan'));
    }

    /**
     * Mengupdate data induk.
     */
    public function update(Request $request, Penjualan $penjualan)
    {
         if (auth()->user()->role != 'admin' && $penjualan->user_id != auth()->id()) {
             return redirect()->route('penjualan.index')->with('error', 'Akses ditolak.');
        }
        
        $request->validate([
            'pelanggan' => 'required|string|max:255',
            'tgl_transaksi' => 'required|date',
            'status' => 'required|string',
            // validasi lainnya jika diperlukan
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

        // Hanya data pending yang boleh dihapus user
        if ($penjualan->status != 'Pending' && auth()->user()->role != 'admin') {
            return redirect()->route('penjualan.index')->with('error', 'Data yang sudah diproses tidak bisa dihapus.');
         }
        
        $penjualan->delete();
        return redirect()->route('penjualan.index')->with('success', 'Data penjualan berhasil dihapus.');
    }

    /**
     * Menyetujui data penjualan (Admin).
     */
    public function approve(Penjualan $penjualan)
    {
        if (auth()->user()->role != 'admin') {
             return redirect()->route('penjualan.index')->with('error', 'Akses ditolak.');
        }
        $penjualan->status = 'Approved';
        $penjualan->save();
        return redirect()->route('penjualan.index')->with('success', 'Data penjualan berhasil disetujui.');
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
        // Keamanan
        if (auth()->user()->role != 'admin' && $penjualan->user_id != auth()->id()) {
            return redirect()->route('penjualan.index')->with('error', 'Akses ditolak.');
        }

        $penjualan->load('items', 'user');
        return view('penjualan.print', compact('penjualan'));
    }
}