<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Penjualan;  // Pastikan namespace Model Anda benar (App\ atau App\Models\)
use App\Pembelian;
use App\Biaya;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard aplikasi beserta data ringkasan.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data = [];
        $now = Carbon::now();

        if (Auth::user()->role == 'admin') {
            // ===================================
            // LOGIKA UNTUK ADMIN (MELIHAT SEMUA DATA)
            // ===================================
            
            // 1. Ambil query dasar untuk semua data
            $penjualanQuery = Penjualan::query();
            $pembelianQuery = Pembelian::query();
            $biayaQuery = Biaya::query();
            
            // 2. Kartu "Jumlah User Terdaftar"
            $data['card_4_title'] = 'Jumlah User Terdaftar';
            $data['card_4_value'] = User::count();
            $data['card_4_icon'] = 'fa-users';

        } else {
            // ===================================
            // LOGIKA UNTUK USER BIASA (MELIHAT DATA SENDIRI)
            // ===================================
            $userId = Auth::id();

            // 1. Ambil query dasar yang sudah difilter
            $penjualanQuery = Penjualan::where('user_id', $userId);
            $pembelianQuery = Pembelian::where('user_id', $userId);
            $biayaQuery = Biaya::where('user_id', $userId);
            
            // 2. Kartu "Data Menunggu Persetujuan"
            $pendingCount = $penjualanQuery->where('status', 'Pending')->count()
                           + $pembelianQuery->where('status', 'Pending')->count()
                           + $biayaQuery->where('status', 'Pending')->count();

            $data['card_4_title'] = 'Data Menunggu Persetujuan';
            $data['card_4_value'] = $pendingCount;
            $data['card_4_icon'] = 'fa-clock';
        }

        // ===================================
        // KALKULASI BERSAMA (berdasarkan query di atas)
        // ===================================

        // 3. Kartu Penjualan
        $data['penjualanBulanIni'] = $penjualanQuery
            ->whereYear('tgl_transaksi', $now->year)
            ->whereMonth('tgl_transaksi', $now->month)
            ->sum('total');

        // 4. Kartu Pembelian
        $data['pembelianBulanIni'] = $pembelianQuery
            ->whereYear('tgl_transaksi', $now->year)
            ->whereMonth('tgl_transaksi', $now->month)
            ->count();

        // 5. Kartu Biaya
        $data['biayaBulanIni'] = $biayaQuery
            ->whereYear('tgl_transaksi', $now->year)
            ->whereMonth('tgl_transaksi', $now->month)
            ->sum('total');

        // Kirim semua data ke view
        return view('dashboard', $data);
    }
}