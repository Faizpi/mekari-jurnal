<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $fillable = [
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
        'total',
        'status',
    ];
}