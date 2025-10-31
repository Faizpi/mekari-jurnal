<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembeliansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pembelians', function (Blueprint $table) {
            $table->id();

            // 1. Kolom baru untuk Foreign Key
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->string('staf_penyetuju');
            $table->string('email_penyetuju')->nullable();
            $table->date('tgl_transaksi');
            $table->date('tgl_jatuh_tempo')->nullable();
            $table->string('urgensi');
            $table->string('tahun_anggaran')->nullable();
            $table->string('tag')->nullable();
            $table->text('memo')->nullable();
            $table->integer('total_barang')->default(0);

            // 2. Kolom baru untuk Status Persetujuan
            $table->string('status')->default('Pending');

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
        Schema::dropIfExists('pembelians');
    }
}
