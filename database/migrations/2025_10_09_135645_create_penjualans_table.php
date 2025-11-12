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
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            // Info Utama (dari atas form)
            $table->string('pelanggan');
            $table->string('email')->nullable();
            $table->text('alamat_penagihan')->nullable();
            $table->date('tgl_transaksi');
            $table->date('tgl_jatuh_tempo')->nullable();
            $table->string('syarat_pembayaran')->nullable();
            $table->string('no_referensi')->nullable();
            $table->string('tag')->nullable();
            $table->unsignedBigInteger('gudang_id');
            $table->foreign('gudang_id')->references('id')->on('gudangs');
            $table->text('memo')->nullable();
            $table->string('lampiran_path')->nullable();
            
            // Info Status & Total
            $table->string('status')->default('Pending');
            $table->decimal('grand_total', 15, 2);
            $table->decimal('tax_percentage', 5, 2)->default(0); // Total akhir semua item

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
