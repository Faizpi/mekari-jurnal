<?php

namespace App\Http\Controllers;

use App\Biaya;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BiayaController extends Controller
{
    /**
     * Menampilkan daftar semua biaya beserta kalkulasi ringkasan.
     */
    public function index()
    {
        $allBiaya = Biaya::latest()->get();

        $totalBulanIni = Biaya::whereYear('tgl_transaksi', Carbon::now()->year)
            ->whereMonth('tgl_transaksi', Carbon::now()->month)
            ->sum('total');

        $total30Hari = Biaya::where('tgl_transaksi', '>=', Carbon::now()->subDays(30))
            ->sum('total');

        $totalBelumDibayar = Biaya::where('status', '!=', 'Paid')->sum('total');

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
            'bayar_dari' => 'required|string|max:255',
            'tgl_transaksi' => 'required|date',

            // Validasi bahwa input adalah array
            'kategori' => 'required|array',
            'total' => 'required|array',

            // Validasi setiap item di dalam array
            'kategori.*' => 'nullable|string|max:255',
            'total.*' => 'required|numeric|min:0',
        ]);

        // Looping sebanyak baris yang di-submit
        foreach ($request->kategori as $index => $kategori) {
            // Buat entri database baru untuk setiap baris
            Biaya::create([
                'bayar_dari' => $request->bayar_dari,
                'penerima' => $request->penerima,
                'tgl_transaksi' => $request->tgl_transaksi,
                'cara_pembayaran' => $request->cara_pembayaran,
                'tag' => $request->tag,
                'memo' => $request->memo,

                // Ambil data dari array sesuai index-nya
                'kategori' => $kategori,
                'total' => $request->total[$index],
            ]);
        }

        return redirect()->route('biaya.index')->with('success', 'Data biaya berhasil disimpan.');
    }

    public function show(Biaya $biaya)
    {
        return view('biaya.show', compact('biaya'));
    }

    public function edit(Biaya $biaya)
    {
        // Cukup kirim data biaya yang mau diedit ke view 'edit.blade.php'
        return view('biaya.edit', compact('biaya'));
    }

    /**
     * Mengupdate data biaya yang ada di database.
     */
    public function update(Request $request, Biaya $biaya)
    {
        // Lakukan validasi sama seperti di method store()
        $request->validate([
            'penerima' => 'required|string',
            'tgl_transaksi' => 'required|date',
            'total' => 'required|numeric',
            'kategori' => 'nullable|string',
        ]);

        // Update data di database dengan data baru dari form
        $biaya->update($request->all());

        // Arahkan kembali ke halaman daftar dengan pesan sukses
        return redirect()->route('biaya.index')->with('success', 'Data biaya berhasil diperbarui.');
    }


    public function destroy(Biaya $biaya)
    {
        // Hapus data dari database
        $biaya->delete();

        // Arahkan kembali ke halaman daftar dengan pesan sukses
        return redirect()->route('biaya.index')->with('success', 'Data biaya berhasil dihapus.');
    }
}