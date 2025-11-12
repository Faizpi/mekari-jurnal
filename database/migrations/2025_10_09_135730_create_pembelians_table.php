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

            // Info Utama
            $table->string('staf_penyetuju');
            $table->string('email_penyetuju')->nullable();
            $table->date('tgl_transaksi');
            $table->date('tgl_jatuh_tempo')->nullable();
            $table->string('urgensi');
            $table->string('tahun_anggaran')->nullable();
            $table->unsignedBigInteger('gudang_id');
            $table->foreign('gudang_id')->references('id')->on('gudangs');
            $table->text('memo')->nullable();
            $table->string('lampiran_path')->nullable();
            
            // Info Status & Total
            $table->string('status')->default('Pending');
            $table->decimal('grand_total', 15, 2);
            $table->decimal('tax_percentage', 5, 2)->default(0); // <-- TAMBAHKAN INI

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