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
        $table->unsignedBigInteger('user_id');
        $table->foreign('user_id')->references('id')->on('users');

        // Info Utama (dari atas form)
        $table->string('staf_penyetuju');
        $table->string('email_penyetuju')->nullable();
        $table->date('tgl_transaksi');
        $table->date('tgl_jatuh_tempo')->nullable();
        $table->string('urgensi');
        $table->string('tahun_anggaran')->nullable();
        $table->string('tag')->nullable();
        $table->text('memo')->nullable();
        $table->string('lampiran_path')->nullable();

        // Info Status & Total
        $table->string('status')->default('Pending');
        // Kita hapus 'total_barang' dan akan ganti dengan 'grand_total'
        // (Kita akan tambahkan grand_total nanti jika pembelian memiliki nilai uang)
        // Untuk saat ini, kita biarkan tanpa total

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