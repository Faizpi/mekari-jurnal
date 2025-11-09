<?php

namespace App\Http\Controllers;

use App\Penjualan;
use App\PenjualanItem;
use App\User;
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

        $totalBelumDibayar = (clone $query)->whereIn('status', ['Pending', 'Approved'])->sum('grand_total');
        $totalTelatDibayar = (clone $query)->where('tgl_jatuh_tempo', '<', Carbon::now())
                                          ->whereIn('status', ['Pending', 'Approved'])
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
        return view('penjualan.create');
    }

    /**
     * Menyimpan data baru ke database (Struktur Induk & Rincian).
     */
    public function store(Request $request)
    {
        // =================================================================
        // VALIDASI LENGKAP (INILAH PERBAIKANNYA)
        // =================================================================
        $request->validate([
            // Validasi field utama
            'pelanggan' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'alamat_penagihan' => 'nullable|string',
            'tgl_transaksi' => 'required|date',
            'tgl_jatuh_tempo' => 'nullable|date',
            'syarat_pembayaran' => 'nullable|string|max:255',
            'no_referensi' => 'nullable|string|max:255',
            'tag' => 'nullable|string|max:255',
            'gudang' => 'nullable|string|max:255',
            'memo' => 'nullable|string',
            
            // Validasi file
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048', // 2MB Max
            
            // Validasi Array
            'produk' => 'required|array',
            'deskripsi' => 'nullable|array',
            'kuantitas' => 'required|array',
            'unit' => 'nullable|array',
            'harga_satuan' => 'required|array',
            'diskon' => 'nullable|array',
            
            // Validasi setiap item di dalam array
            'produk.*' => 'required|string|max:255',
            'deskripsi.*' => 'nullable|string',
            'kuantitas.*' => 'required|numeric|min:1',
            'unit.*' => 'nullable|string',
            'harga_satuan.*' => 'required|numeric|min:0',
            'diskon.*' => 'nullable|numeric|min:0|max:100',
        ]);
        // =================================================================

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
     */
    public function edit(Penjualan $penjualan)
    {
        if (auth()->user()->role != 'admin' && $penjualan->user_id != auth()->id()) {
             return redirect()->route('penjualan.index')->with('error', 'Akses ditolak.');
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
            'grand_total' => 'required|numeric', // Sesuaikan jika form edit bisa mengubah total
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
     * Menyetujui data penjualan.
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