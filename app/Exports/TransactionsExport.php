<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class TransactionsExport implements FromView, WithTitle
{
    protected $transactions;

    // Terima data yang sudah difilter dari controller
    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    /**
     * Menggunakan file Blade untuk merender Excel.
     * Ini memberi kita fleksibilitas penuh atas tampilan.
     */
    public function view(): View
    {
        return view('reports.transactions', [
            'transactions' => $this->transactions
        ]);
    }

    /**
     * Memberi nama pada sheet di Excel.
     */
    public function title(): string
    {
        return 'Laporan Transaksi';
    }
}