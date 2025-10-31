<?php

namespace App; // atau App\Models

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    // 1. Perbarui $fillable
    protected $fillable = [
        'user_id', 'status', // <-- Tambahkan ini
        'staf_penyetuju', 'email_penyetuju', 'tgl_transaksi',
        'tgl_jatuh_tempo', 'urgensi', 'tahun_anggaran', 'tag',
        'memo', 'total_barang',
    ];

    // 2. Tambahkan relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}