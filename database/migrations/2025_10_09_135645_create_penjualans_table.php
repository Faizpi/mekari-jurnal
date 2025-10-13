<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenjualansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penjualans', function (Blueprint $table) {
            $table->id();
            $table->string('pelanggan');
            $table->string('email')->nullable();
            $table->text('alamat_penagihan')->nullable();
            $table->date('tgl_transaksi');
            $table->date('tgl_jatuh_tempo')->nullable();
            $table->string('syarat_pembayaran')->nullable();
            $table->string('no_referensi')->nullable();
            $table->string('tag')->nullable();
            $table->string('gudang')->nullable();
            $table->text('memo')->nullable();
            $table->decimal('total', 15, 2);
            $table->string('status')->default('Menunggu Pembayaran');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penjualans');
    }
}
