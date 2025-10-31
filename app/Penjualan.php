<?php

namespace App; // atau App\Models

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    // 1. Perbarui $fillable
    protected $fillable = [
        'user_id', 'status', // <-- Tambahkan ini
        'pelanggan', 'email', 'alamat_penagihan', 'tgl_transaksi',
        'tgl_jatuh_tempo', 'syarat_pembayaran', 'no_referensi',
        'tag', 'gudang', 'memo', 'total',
    ];

    // 2. Tambahkan relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}