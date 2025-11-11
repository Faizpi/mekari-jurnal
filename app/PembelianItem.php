<?php

namespace App; // atau App\Models

use Illuminate\Database\Eloquent\Model;

class PembelianItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'pembelian_id',
        'produk_id',
        'deskripsi',
        'kuantitas',
        'unit',
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}