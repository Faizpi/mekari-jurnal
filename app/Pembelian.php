<?php

namespace App; // atau App\Models

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    // Sesuaikan $fillable dengan migrasi 'induk' yang baru
    protected $fillable = [
        'user_id',
        'staf_penyetuju',
        'email_penyetuju',
        'tgl_transaksi',
        'tgl_jatuh_tempo',
        'urgensi',
        'tahun_anggaran',
        'tag',
        'memo',
        'lampiran_path',
        'status',
        // 'total_barang' sudah dihapus dari $fillable karena pindah ke 'items'
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
        return $this->hasMany(PembelianItem::class);
    }
}