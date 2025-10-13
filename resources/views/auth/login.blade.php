@extends('layouts.app')

@section('content')
{{-- Kita bungkus semuanya dalam satu container agar rapi --}}
<div class="container">

    {{-- Gunakan align-items-center untuk membuat kedua kolom sejajar di tengah secara vertikal --}}
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">

        <div class="col-lg-6 text-center d-none d-lg-block">
            <img src="{{ asset('assets/img/mekarilogo.png') }}" alt="Mekari Jurnal Logo" class="img-fluid px-5">
            {{-- Anda bisa menambahkan teks deskripsi di bawah logo jika perlu --}}
            <h4 class="text-gray-700 mt-4">Platform Akuntansi Online No. 1 di Indonesia</h4>
        </div>

        <div class="col-lg-6">
            <div class="card o-hidden border-0 shadow-lg">
                <div class="card-body p-0">
                    {{-- Hapus struktur row di dalam card, karena kita hanya butuh satu kolom untuk form --}}
                    <div class="p-5">
                        <div class="text-center">
                            <h1 class="h4 text-gray-900 mb-4">Selamat Datang!</h1>
                        </div>
                        <form class="user" method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="form-group">
                                <input type="email" class="form-control form-control-user @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Masukkan Email..." required autocomplete="email" autofocus>
                                @error('email')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control form-control-user @error('password') is-invalid @enderror" name="password" placeholder="Kata Sandi" required autocomplete="current-password">
                                 @error('password')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox small">
                                    <input type="checkbox" class="custom-control-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="remember">Ingat Saya</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-user btn-block">
                                Login
                            </button>
                            <hr>
                        </form>
                        <div class="text-center">
                            {{-- <a class="small" href="{{ route('register') }}">Buat Akun Baru!</a> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection