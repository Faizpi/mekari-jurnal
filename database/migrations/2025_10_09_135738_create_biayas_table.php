<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBiayasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('biayas', function (Blueprint $table) {
            $table->id();
            $table->string('bayar_dari');
            $table->string('penerima')->nullable();
            $table->text('alamat_penagihan')->nullable();
            $table->date('tgl_transaksi');
            $table->string('cara_pembayaran')->nullable();
            $table->string('tag')->nullable();
            $table->string('pajak')->nullable(); // Ditambahkan
            $table->string('kategori')->nullable();
            $table->text('memo')->nullable();
            $table->decimal('total', 15, 2);
            $table->string('status')->default('Paid');
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
        Schema::dropIfExists('biayas');
    }
}