<?php

namespace App\Http\Controllers;

use App\Penjualan;
use Illuminate\Http\Request;
use Carbon\Carbon; // <-- Tambahkan ini

class PenjualanController extends Controller
{
    /**
     * Menampilkan daftar penjualan dengan kalkulasi ringkasan.
     */
    public function index()
    {
        $allPenjualan = Penjualan::latest()->get();

        // Kalkulasi untuk Kartu Ringkasan
        // (Kita asumsikan status 'Lunas' ada, untuk saat ini kita hardcode status)
        $totalBelumDibayar = Penjualan::where('status', '!=', 'Lunas')->sum('total');

        $totalTelatDibayar = Penjualan::where('tgl_jatuh_tempo', '<', Carbon::now())
            ->where('status', '!=', 'Lunas')
            ->sum('total');

        // Untuk contoh, kita anggap semua yang dibuat dalam 30 hari terakhir sudah lunas
        $pelunasan30Hari = Penjualan::where('created_at', '>=', Carbon::now()->subDays(30))
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
        // Validasi data utama
        $request->validate([
            'pelanggan' => 'required|string|max:255',
            'tgl_transaksi' => 'required|date',
            // Validasi data produk sebagai array
            'produk' => 'required|array',
            'kuantitas' => 'required|array',
            'harga_satuan' => 'required|array',
        ]);

        // Looping untuk setiap produk yang di-submit
        foreach ($request->produk as $index => $produk) {
            // Untuk setiap baris produk, kita buat satu entri penjualan baru
            // (Ini adalah pendekatan sederhana, setup ideal memerlukan tabel relasi)
            Penjualan::create([
                // Data utama (akan sama untuk setiap baris)
                'pelanggan' => $request->pelanggan,
                'email' => $request->email,
                'alamat_penagihan' => $request->alamat_penagihan,
                'tgl_transaksi' => $request->tgl_transaksi,
                'tgl_jatuh_tempo' => $request->tgl_jatuh_tempo,
                'syarat_pembayaran' => $request->syarat_pembayaran,
                'memo' => $request->memo,

                // Data per baris (berbeda-beda)
                // 'nama_produk' => $produk, // Anda perlu menambahkan kolom ini di migrasi jika perlu
                'total' => $request->harga_satuan[$index] * $request->kuantitas[$index],
            ]);
        }

        return redirect()->route('penjualan.index')->with('success', 'Data penjualan berhasil disimpan.');
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
}