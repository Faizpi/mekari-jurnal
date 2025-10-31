<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Membuat kolom id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->string('name'); // Membuat kolom name VARCHAR(255)
            $table->string('email')->unique(); // Membuat kolom email VARCHAR(255) yang unik
            $table->timestamp('email_verified_at')->nullable(); // Kolom untuk verifikasi email
            $table->string('password'); // Membuat kolom password VARCHAR(255)
            $table->string('role')->default('user'); // Kolom role dengan nilai default 'user'
            $table->rememberToken(); // Kolom remember_token VARCHAR(100) untuk "remember me"
            $table->timestamps(); // Membuat kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}