<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenjualanItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penjualan_items', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel 'penjualans' (induk)
            $table->unsignedBigInteger('penjualan_id');
            $table->foreign('penjualan_id')->references('id')->on('penjualans')->onDelete('cascade');

            // Kolom rincian dari tabel di form
            $table->string('produk'); // Dari input teks 'produk[]'
            $table->text('deskripsi')->nullable();
            $table->integer('kuantitas');
            $table->string('unit')->nullable();
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('diskon', 5, 2)->default(0); // Misal, 10.00 untuk 10%
            $table->decimal('jumlah_baris', 15, 2); // Kuantitas * Harga * (1 - Diskon)
            
            // Kita tidak perlu timestamps untuk items
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penjualan_items');
    }
}
