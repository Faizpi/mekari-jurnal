<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Di sinilah Anda dapat mendaftarkan rute web untuk aplikasi Anda.
|
*/

// Rute untuk halaman depan
// Jika user sudah login, arahkan ke dashboard. Jika belum, tampilkan halaman welcome/login.
Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('auth.login');
});

// Rute untuk semua fitur autentikasi (Login, Register, Logout, Lupa Password)
// Disediakan oleh Laravel secara otomatis.
Auth::routes();

// --- GRUP UNTUK HALAMAN YANG MEMBUTUHKAN LOGIN ---
Route::middleware(['auth'])->group(function () {

    // Rute Dashboard
    Route::get('/dashboard', 'DashboardController@index')->name('dashboard');

    // Rute untuk Modul Penjualan, Pembelian, dan Biaya
    // Menggunakan Route::resource akan secara otomatis membuat 7 rute standar:
    // index, create, store, show, edit, update, destroy.
    Route::resource('penjualan', 'PenjualanController');
    Route::resource('pembelian', 'PembelianController');
    Route::resource('biaya', 'BiayaController');

    Route::get('/pengaturan-admin', function() {
        return '<h1>Ini Halaman Pengaturan Admin</h1>';
    })->middleware('role:admin');

    Route::middleware(['role:admin'])->group(function () {
        // Rute ini akan menangani semua fungsionalitas user (index, create, store, edit, update, destroy)
        Route::resource('users', 'UserController');
    });

});
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
