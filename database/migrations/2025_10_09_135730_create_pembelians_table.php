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
            $table->string('staf_penyetuju');
            $table->string('email_penyetuju')->nullable();
            $table->date('tgl_transaksi');
            $table->date('tgl_jatuh_tempo')->nullable();
            $table->string('urgensi');
            $table->string('tahun_anggaran')->nullable(); // Ditambahkan
            $table->string('tag')->nullable(); // Ditambahkan
            $table->text('memo')->nullable();
            $table->integer('total_barang')->default(0);
            $table->string('status')->default('Belum Ditagih');
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
