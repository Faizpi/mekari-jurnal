<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Penjualan; // Pastikan namespace Model Anda benar
use App\Pembelian;
use App\Biaya;
use App\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard aplikasi beserta data ringkasan.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Kalkulasi untuk kartu ringkasan
        $penjualanBulanIni = Penjualan::whereYear('tgl_transaksi', Carbon::now()->year)
                                    ->whereMonth('tgl_transaksi', Carbon::now()->month)
                                    ->sum('total');

        $pembelianBulanIni = Pembelian::whereYear('tgl_transaksi', Carbon::now()->year)
                                    ->whereMonth('tgl_transaksi', Carbon::now()->month)
                                    ->count(); // Menghitung jumlah transaksi pembelian

        $biayaBulanIni = Biaya::whereYear('tgl_transaksi', Carbon::now()->year)
                                    ->whereMonth('tgl_transaksi', Carbon::now()->month)
                                    ->sum('total');
        
        $jumlahUser = User::count();

        // Kirim semua data hasil kalkulasi ke view
        return view('dashboard', compact(
            'penjualanBulanIni', 
            'pembelianBulanIni', 
            'biayaBulanIni', 
            'jumlahUser'
        ));
    }
}