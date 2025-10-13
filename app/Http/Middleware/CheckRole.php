<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role  // Kita tambahkan parameter $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        // Cek apakah user sudah login DAN rolenya sesuai dengan yang diizinkan
        if (!Auth::check() || Auth::user()->role !== $role) {
            // Jika tidak, tendang user kembali ke dashboard dengan pesan error
            return redirect('/dashboard')->with('error', 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        // Jika rolenya cocok, izinkan user untuk melanjutkan
        return $next($request);
    }
}