<?php

namespace App; // atau App\Models

use Illuminate\Database\Eloquent\Model;

class Biaya extends Model
{
    // 1. Perbarui $fillable
    protected $fillable = [
        'user_id', 'status', // <-- Tambahkan ini
        'bayar_dari', 'penerima', 'alamat_penagihan', 'tgl_transaksi',
        'cara_pembayaran', 'tag', 'kategori', 'pajak', 'memo', 'total',
    ];

    // 2. Tambahkan relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}