<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Biaya extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bayar_dari',
        'penerima',
        'alamat_penagihan',
        'tgl_transaksi',
        'cara_pembayaran',
        'tag',
        'kategori',
        'memo',
        'total',
        'status',
    ];
}