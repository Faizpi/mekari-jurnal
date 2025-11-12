<?php

namespace App\Http\Controllers;

use App\Biaya;
use App\BiayaItem;
use App\User;
use App\Kontak; // <-- TAMBAHKAN INI
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class BiayaController extends Controller
{
    /**
     * Tampilkan daftar biaya (berdasarkan role).
     */
    public function index()
    {
        $query = null;

        if (Auth::user()->role == 'admin') {
            $query = Biaya::with('user');
        } else {
            $query = Biaya::where('user_id', Auth::id())->with('user');
        }

        $totalBulanIni = (clone $query)->whereYear('tgl_transaksi', Carbon::now()->year)
            ->whereMonth('tgl_transaksi', Carbon::now()->month)
            ->sum('grand_total');

        $total30Hari = (clone $query)->where('tgl_transaksi', '>=', Carbon::now()->subDays(30))
            ->sum('grand_total');

        $totalBelumDibayar = (clone $query)->whereIn('status', ['Pending', 'Rejected'])
            ->sum('grand_total');
        
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
        $kontaks = Kontak::all(); // <-- TAMBAHKAN INI
        return view('biaya.create', compact('kontaks')); // <-- Kirim 'kontaks'
    }

    /**
     * Memvalidasi dan menyimpan data biaya baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bayar_dari' => 'required|string',
            'tgl_transaksi' => 'required|date',
            'tax_percentage' => 'required|numeric|min:0',
            'lampiran' => 'nullable|file|mimes:jpg,png,pdf,zip,doc,docx|max:2048',
            'kategori' => 'required|array|min:1',
            'total' => 'required|array|min:1',
            'kategori.*' => 'required|string|max:255',
            'total.*' => 'required|numeric|min:0',
            'penerima' => 'nullable|string|max:255', // Validasi 'penerima'
        ]);

        $path = null;
        if ($request->hasFile('lampiran')) {
            $path = $request->file('lampiran')->store('lampiran_biaya', 'public');
        }

        $subTotal = 0;
        foreach ($request->total as $index => $jumlah) {
            $subTotal += $jumlah ?? 0;
        }
        
        $pajakPersen = $request->tax_percentage ?? 0;
        $jumlahPajak = $subTotal * ($pajakPersen / 100);
        $grandTotal = $subTotal + $jumlahPajak;

        DB::beginTransaction();
        try {
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
                'tax_percentage' => $pajakPersen,
                'grand_total' => $grandTotal,
            ]);

            foreach ($request->kategori as $index => $kategori) {
                BiayaItem::create([
                    'biaya_id' => $biayaInduk->id,
                    'kategori' => $kategori,
                    'deskripsi' => $request->deskripsi_akun[$index] ?? null,
                    'jumlah' => $request->total[$index] ?? 0,
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
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
        if (Auth::user()->role != 'admin' && $biaya->user_id != Auth::id()) {
             return redirect()->route('biaya.index')->with('error', 'Anda tidak punya hak akses.');
        }
        if ($biaya->status != 'Pending' && Auth::user()->role != 'admin') {
            return redirect()->route('biaya.index')->with('error', 'Data yang sudah diproses tidak bisa diedit.');
        }

        $biaya->load('items');
        $kontaks = Kontak::all(); // <-- TAMBAHKAN INI
        return view('biaya.edit', compact('biaya', 'kontaks')); // <-- Kirim 'kontaks'
    }

    /**
     * Mengupdate data biaya yang ada di database.
     */
    public function update(Request $request, Biaya $biaya)
    {
        if (Auth::user()->role != 'admin' && $biaya->user_id != Auth::id()) {
             return redirect()->route('biaya.index')->with('error', 'Anda tidak punya hak akses.');
        }

        $request->validate([
            'bayar_dari' => 'required|string',
            'tgl_transaksi' => 'required|date',
            'tax_percentage' => 'required|numeric|min:0',
            'lampiran' => 'nullable|file|mimes:jpg,png,pdf,zip,doc,docx|max:2048',
            'kategori' => 'required|array|min:1',
            'total' => 'required|array|min:1',
            'kategori.*' => 'required|string|max:255',
            'total.*' => 'required|numeric|min:0',
            'penerima' => 'nullable|string|max:255', // Validasi 'penerima'
        ]);

        $path = $biaya->lampiran_path;
        if ($request->hasFile('lampiran')) {
            // TODO: Hapus file lama
            $path = $request->file('lampiran')->store('lampiran_biaya', 'public');
        }

        $subTotal = 0;
        foreach ($request->total as $index => $jumlah) {
            $subTotal += $jumlah ?? 0;
        }
        $pajakPersen = $request->tax_percentage ?? 0;
        $jumlahPajak = $subTotal * ($pajakPersen / 100);
        $grandTotal = $subTotal + $jumlahPajak;

        DB::beginTransaction();
        try {
            $biaya->update([
                'status' => 'Pending',
                'bayar_dari' => $request->bayar_dari,
                'penerima' => $request->penerima,
                'alamat_penagihan' => $request->alamat_penagihan,
                'tgl_transaksi' => $request->tgl_transaksi,
                'cara_pembayaran' => $request->cara_pembayaran,
                'tag' => $request->tag,
                'memo' => $request->memo,
                'lampiran_path' => $path,
                'tax_percentage' => $pajakPersen,
                'grand_total' => $grandTotal,
            ]);

            $biaya->items()->delete();

            foreach ($request->kategori as $index => $kategori) {
                BiayaItem::create([
                    'biaya_id' => $biaya->id,
                    'kategori' => $kategori,
                    'deskripsi' => $request->deskripsi_akun[$index] ?? null,
                    'jumlah' => $request->total[$index] ?? 0,
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('biaya.index')->with('success', 'Data biaya berhasil diperbarui.');
    }

    /**
     * Menghapus data biaya dari database.
     */
    public function destroy(Biaya $biaya)
    {
        if (auth()->user()->role != 'admin' && $biaya->user_id != auth()->id()) {
             return redirect()->route('biaya.index')->with('error', 'Anda tidak punya hak akses.');
        }
        
        $biaya->delete();
        return redirect()->route('biaya.index')->with('success', 'Data biaya berhasil dihapus.');
    }

    /**
     * Menyetujui data biaya.
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

    /**
     * Menampilkan halaman print struk.
     */
    public function print(Biaya $biaya)
    {
        if (auth()->user()->role != 'admin' && $biaya->user_id != auth()->id()) {
            return redirect()->route('biaya.index')->with('error', 'Akses ditolak.');
        }
        $biaya->load('items', 'user');
        return view('biaya.print', compact('biaya'));
    }
}