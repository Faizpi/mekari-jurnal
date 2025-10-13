<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $fillable = [
        'staf_penyetuju',
        'email_penyetuju',
        'tgl_transaksi',
        'tgl_jatuh_tempo',
        'urgensi',
        'memo',
        'total_barang',
        'status',
    ];
}