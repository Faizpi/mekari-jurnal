<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Penjualan;
use App\Pembelian;
use App\Biaya;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard aplikasi beserta data ringkasan.
     */
    public function index()
    {
        $data = [];
        $now = Carbon::now();

        if (Auth::user()->role == 'admin') {
            // ===================================
            // LOGIKA UNTUK ADMIN
            // ===================================
            
            // 1. Query dasar untuk SEMUA data
            $penjualanQuery = Penjualan::query();
            $pembelianQuery = Pembelian::query();
            $biayaQuery = Biaya::query();
            
            // 2. Kartu "Jumlah User"
            $data['card_4_title'] = 'Jumlah User Terdaftar';
            $data['card_4_value'] = User::count();
            $data['card_4_icon'] = 'fa-users';

            // 3. Mengambil SEMUA data untuk Master Table
            $penjualans = Penjualan::with('user')->get();
            $pembelians = Pembelian::with('user')->get();
            $biayas = Biaya::with('user')->get();

            // 4. Menyiapkan data untuk digabung (tambahkan tipe agar bisa dibedakan)
            $penjualans->each(function($item) { 
                $item->type = 'Penjualan'; 
                $item->route = route('penjualan.show', $item->id);
                $item->number = 'INV-' . $item->id;
            });
            $pembelians->each(function($item) { 
                $item->type = 'Pembelian'; 
                $item->route = route('pembelian.show', $item->id);
                $item->number = 'PR-' . $item->id;
            });
            $biayas->each(function($item) { 
                $item->type = 'Biaya'; 
                $item->route = route('biaya.show', $item->id);
                $item->number = 'EXP-' . $item->id;
            });

            // 5. Gabungkan dan urutkan berdasarkan tanggal dibuat
            $allTransactions = $penjualans->concat($pembelians)->concat($biayas);
            $data['allTransactions'] = $allTransactions->sortByDesc('created_at');


        } else {
            // ===================================
            // LOGIKA UNTUK USER BIASA
            // ===================================
            $userId = Auth::id();

            // 1. Query dasar HANYA data milik user
            $penjualanQuery = Penjualan::where('user_id', $userId);
            $pembelianQuery = Pembelian::where('user_id', $userId);
            $biayaQuery = Biaya::where('user_id', $userId);
            
            // 2. Kartu "Data Pending"
            $pendingCount = (clone $penjualanQuery)->where('status', 'Pending')->count()
                           + (clone $pembelianQuery)->where('status', 'Pending')->count()
                           + (clone $biayaQuery)->where('status', 'Pending')->count();

            $data['card_4_title'] = 'Data Menunggu Persetujuan';
            $data['card_4_value'] = $pendingCount;
            $data['card_4_icon'] = 'fa-clock';
        }

        // ===================================
        // KALKULASI KARTU RINGKASAN BERSAMA
        // ===================================
        $data['penjualanBulanIni'] = (clone $penjualanQuery)
            ->whereYear('tgl_transaksi', $now->year)
            ->whereMonth('tgl_transaksi', $now->month)
            ->sum('grand_total');

        $data['pembelianBulanIni'] = (clone $pembelianQuery)
            ->whereYear('tgl_transaksi', $now->year)
            ->whereMonth('tgl_transaksi', $now->month)
            ->count();

        $data['biayaBulanIni'] = (clone $biayaQuery)
            ->whereYear('tgl_transaksi', $now->year)
            ->whereMonth('tgl_transaksi', $now->month)
            ->sum('grand_total');

        // Kirim semua data ke view
        return view('dashboard', $data);
    }

    public function export(Request $request)
    {
        // 1. Validasi input tanggal
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        // 2. Ambil data dari database (logika yang sama seperti di index)
        $penjualans = Penjualan::with('user', 'gudang')
                        ->whereBetween('tgl_transaksi', [$dateFrom, $dateTo])
                        ->get();
        $pembelians = Pembelian::with('user', 'gudang')
                        ->whereBetween('tgl_transaksi', [$dateFrom, $dateTo])
                        ->get();
        $biayas = Biaya::with('user')
                        ->whereBetween('tgl_transaksi', [$dateFrom, $dateTo])
                        ->get();

        // 3. Siapkan data untuk digabung
        $penjualans->each(function($item) { 
            $item->type = 'Penjualan'; $item->route = route('penjualan.show', $item->id);
            $item->number = 'INV-' . $item->id;
        });
        $pembelians->each(function($item) { 
            $item->type = 'Pembelian'; $item->route = route('pembelian.show', $item->id);
            $item->number = 'PR-' . $item->id;
        });
        $biayas->each(function($item) { 
            $item->type = 'Biaya'; $item->route = route('biaya.show', $item->id);
            $item->number = 'EXP-' . $item->id;
        });

        // 4. Gabungkan dan urutkan
        $allTransactions = $penjualans->concat($pembelians)->concat($biayas)->sortBy('tgl_transaksi');

        // 5. Buat nama file
        $fileName = 'Laporan_Transaksi_' . $dateFrom . '_sampai_' . $dateTo . '.xlsx';

        // 6. Download file Excel
        return Excel::download(new TransactionsExport($allTransactions), $fileName);
    }
}