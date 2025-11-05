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
        $table->unsignedBigInteger('user_id');
        $table->foreign('user_id')->references('id')->on('users');

        // Info Utama
        $table->string('penerima')->nullable();
        $table->date('tgl_transaksi');
        $table->string('bayar_dari');
        $table->string('cara_pembayaran')->nullable();
        $table->text('alamat_penagihan')->nullable();
        $table->string('tag')->nullable();
        $table->text('memo')->nullable();
        $table->string('lampiran_path')->nullable();
        $table->string('status')->default('Pending');
        $table->decimal('grand_total', 15, 2); // Total akhir semua item

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