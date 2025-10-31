<?php

namespace App\Http\Controllers;

use App\Penjualan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PenjualanController extends Controller
{
    /**
     * Menampilkan daftar penjualan dengan kalkulasi ringkasan.
     */
    public function index()
    {
        $allPenjualan = [];

        // Cek role user
        if (Auth::user()->role == 'admin') {
            // Admin: ambil semua data
            $allPenjualan = Penjualan::with('user')->latest()->get();
        } else {
            // User: ambil hanya data milik sendiri
            $allPenjualan = Penjualan::where('user_id', Auth::id())
                                      ->with('user')
                                      ->latest()
                                      ->get();
        }

        // Kalkulasi untuk Kartu Ringkasan (berdasarkan data yang sudah difilter)
        $totalBelumDibayar = $allPenjualan->where('status', '!=', 'Lunas')->sum('total');
        $totalTelatDibayar = $allPenjualan->where('tgl_jatuh_tempo', '<', Carbon::now())
                                          ->where('status', '!=', 'Lunas')
                                          ->sum('total');
        $pelunasan30Hari = $allPenjualan->where('status', 'Lunas')
                                        ->where('updated_at', '>=', Carbon::now()->subDays(30))
                                        ->sum('total');

        return view('penjualan.index', [
            'penjualans' => $allPenjualan,
            'totalBelumDibayar' => $totalBelumDibayar,
            'totalTelatDibayar' => $totalTelatDibayar,
            'pelunasan30Hari' => $pelunasan30Hari,
        ]);
    }

    public function create()
    {
        return view('penjualan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'pelanggan' => 'required|string|max:255',
            'tgl_transaksi' => 'required|date',
            'produk' => 'required|array',
            'kuantitas' => 'required|array',
            'harga_satuan' => 'required|array',
        ]);

        foreach ($request->produk as $index => $produk) {
            // Hitung total per baris
            $quantity = $request->kuantitas[$index] ?? 0;
            $price = $request->harga_satuan[$index] ?? 0;
            $discount = $request->diskon[$index] ?? 0;
            $totalAkhir = ($quantity * $price) * (1 - ($discount / 100));

            Penjualan::create([
                // 1. Tambahkan user_id dari user yang sedang login
                'user_id' => Auth::id(),
                // 2. Set status default
                'status' => 'Pending',

                // 3. Data lain dari form (data utama)
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
                
                // 4. Data per baris
                // 'nama_produk' => $produk, // (Kita masih pakai pendekatan 1 baris = 1 data)
                'total' => $totalAkhir,
            ]);
        }

        return redirect()->route('penjualan.index')->with('success', 'Data penjualan berhasil diajukan dan menunggu persetujuan.');
    }

    public function show(Penjualan $penjualan)
    {
        // Logika untuk menampilkan detail satu penjualan
        return view('penjualan.show', compact('penjualan'));
    }

    public function edit(Penjualan $penjualan)
    {
        return view('penjualan.edit', compact('penjualan'));
    }

    public function update(Request $request, Penjualan $penjualan)
    {
        $request->validate([
            'pelanggan' => 'required|string|max:255',
            'tgl_transaksi' => 'required|date',
            'total' => 'required|numeric',
            'status' => 'required|string',
        ]);

        $penjualan->update($request->all());

        return redirect()->route('penjualan.index')->with('success', 'Data penjualan berhasil diperbarui.');
    }

    public function destroy(Penjualan $penjualan)
    {
        $penjualan->delete();

        return redirect()->route('penjualan.index')->with('success', 'Data penjualan berhasil dihapus.');
    }

    public function approve(Penjualan $penjualan)
    {
        // 1. (CONTOH) Kirim data ke API Mekari Jurnal
        /*
        $response = Http::withToken('YOUR_API_KEY')->post('https://api.mekari.com/v1/sales-invoices', [
            'tanggal' => $penjualan->tgl_transaksi,
            'kontak' => $penjualan->pelanggan,
            'total' => $penjualan->total,
        ]);

        if (!$response->successful()) {
            return redirect()->route('penjualan.index')->with('error', 'Gagal mengirim data ke API Jurnal.');
        }
        */

        // 2. Update status di database LOKAL
        $penjualan->status = 'Approved';
        $penjualan->save();

        return redirect()->route('penjualan.index')->with('success', 'Data penjualan berhasil disetujui.');
    }
}