<?php

namespace App; // atau App\Models

use Illuminate\Database\Eloquent\Model;

class PembelianItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'pembelian_id',
        'produk',
        'deskripsi',
        'kuantitas',
        'unit',
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }
}