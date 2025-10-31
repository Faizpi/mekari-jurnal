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
    // database/migrations/..._create_penjualans_table.php

public function up()
{
    Schema::create('penjualans', function (Blueprint $table) {
        $table->id();

        // 1. Kolom baru untuk Foreign Key ke tabel users
        $table->unsignedBigInteger('user_id'); 
        $table->foreign('user_id')->references('id')->on('users');

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
        Schema::dropIfExists('penjualans');
    }
}
