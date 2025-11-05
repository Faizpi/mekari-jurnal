<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembelianItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::create('pembelian_items', function (Blueprint $table) {
        $table->id();

        // Relasi ke tabel 'pembelians' (induk)
        $table->unsignedBigInteger('pembelian_id');
        $table->foreign('pembelian_id')->references('id')->on('pembelians')->onDelete('cascade');

        // Kolom rincian dari tabel di form
        $table->string('produk');
        $table->text('deskripsi')->nullable();
        $table->integer('kuantitas');
        $table->string('unit')->nullable();
    });
}
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pembelian_items');
    }
}
