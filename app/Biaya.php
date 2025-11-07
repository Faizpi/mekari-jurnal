<?php

namespace App; // atau App\Models

use Illuminate\Database\Eloquent\Model;

class Biaya extends Model
{
    // Perbarui $fillable agar sesuai dengan migrasi baru
    protected $fillable = [
        'user_id',
        'penerima',
        'tgl_transaksi',
        'bayar_dari',
        'cara_pembayaran',
        'alamat_penagihan',
        'tag',
        'memo',
        'lampiran_path',
        'status',
        'grand_total',
    ];

    protected $casts = [
        'tgl_transaksi' => 'date',
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
        return $this->hasMany(BiayaItem::class);
    }
}