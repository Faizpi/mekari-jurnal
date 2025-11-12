<?php

namespace App\Http\Controllers;

use App\Gudang;
use App\Produk;
use App\GudangProduk;
use Illuminate\Http\Request;

class StokController extends Controller
{
    // Pastikan hanya admin yang bisa akses
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    /**
     * Menampilkan halaman master stok.
     */
    public function index()
    {
        // Data untuk Form Tambah Stok
        $gudangs = Gudang::all();
        $produks = Produk::all();

        // Data untuk Daftar Stok (Accordion)
        // Kita ambil semua gudang, dan 'eager load' (ambil sekalian)
        // relasi 'produkStok' (data pivot) DAN
        // relasi 'produk' yang ada di dalam 'produkStok'.
        $gudangsWithStok = Gudang::with('produkStok.produk')->get();

        return view('stok.index', compact('gudangs', 'produks', 'gudangsWithStok'));
    }

    /**
     * Menambah atau memperbarui stok secara manual.
     */
    public function store(Request $request)
    {
        $request->validate([
            'gudang_id' => 'required|exists:gudangs,id',
            'produk_id' => 'required|exists:produks,id',
            'stok' => 'required|integer|min:0',
        ]);

        // Cari data stok. Jika sudah ada, update. Jika belum, buat baru.
        $stok = GudangProduk::updateOrCreate(
            [
                'gudang_id' => $request->gudang_id,
                'produk_id' => $request->produk_id,
            ],
            [
                'stok' => $request->stok,
            ]
        );

        return redirect()->route('stok.index')->with('success', 'Stok berhasil diperbarui.');
    }
}