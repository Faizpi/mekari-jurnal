<?php

namespace App; // atau App\Models

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    // Sesuaikan $fillable dengan migrasi 'induk' yang baru
    protected $fillable = [
        'user_id',
        'pelanggan',
        'email',
        'alamat_penagihan',
        'tgl_transaksi',
        'tgl_jatuh_tempo',
        'syarat_pembayaran',
        'no_referensi',
        'tag',
        'gudang',
        'memo',
        'lampiran_path',
        'status',
        'grand_total',
    ];

    protected $casts = [
        'tgl_transaksi' => 'date',
        'tgl_jatuh_tempo' => 'date',
    ];

    /**
     * Relasi ke User (pembuat)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Rincian (Items)
     */
    public function items()
    {
        return $this->hasMany(PenjualanItem::class);
    }
}