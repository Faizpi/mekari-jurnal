<?php

namespace App\Http\Controllers;

use App\Pembelian;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PembelianController extends Controller
{
    /**
     * Menampilkan daftar pembelian dengan kalkulasi ringkasan.
     */
    public function index()
    {
        $allPembelian = Pembelian::latest()->get();

        // Kalkulasi untuk Kartu Ringkasan
        $fakturBelumDibayar = Pembelian::where('status', '!=', 'Lunas')->count();
        $fakturTelatBayar = Pembelian::where('tgl_jatuh_tempo', '<', Carbon::now())
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
            'tahun_anggaran' => 'nullable|string', // Ditambahkan
            'tag' => 'nullable|string', // Ditambahkan
        ]);

        foreach ($request->produk as $index => $produk) {
            Pembelian::create([
                'staf_penyetuju' => $request->staf_penyetuju,
                'email_penyetuju' => $request->email_penyetuju,
                'tgl_transaksi' => $request->tgl_transaksi,
                'tgl_jatuh_tempo' => $request->tgl_jatuh_tempo,
                'urgensi' => $request->urgensi,
                'tahun_anggaran' => $request->tahun_anggaran,
                'tag' => $request->tag,
                'memo' => $request->memo,
                'status' => 'Belum Ditagih',
                'total_barang' => $request->kuantitas[$index],
            ]);
        }

        return redirect()->route('pembelian.index')->with('success', 'Permintaan pembelian berhasil disimpan.');
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
}