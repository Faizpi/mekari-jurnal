<?php

namespace App\Http\Controllers;

use App\Gudang;
use App\Produk;
use App\GudangProduk; // <-- Model tabel pivot stok
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
        // Ambil semua data master untuk form
        $gudangs = Gudang::all();
        $produks = Produk::all();

        // Ambil semua data stok yang sudah ada, muat relasinya
        $stokItems = GudangProduk::with('gudang', 'produk')->get();

        return view('stok.index', compact('gudangs', 'produks', 'stokItems'));
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
        // Ini mencegah duplikasi data stok (1 produk di 1 gudang).
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