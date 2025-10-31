@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    {{-- Kita hanya tampilkan tombol report untuk Admin --}}
    @if(auth()->user()->role == 'admin')
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                class="fas fa-download fa-sm text-white-50"></i> Generate Report</a>
    @endif
</div>

<div class="row">

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Penjualan (Bulan Ini)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($penjualanBulanIni, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Pembelian (Bulan Ini)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pembelianBulanIni }} Transaksi</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box-open fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Biaya (Bulan Ini)
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($biayaBulanIni, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-receipt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            {{ $card_4_title }}</div> {{-- Judul dinamis --}}
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $card_4_value }}</div> {{-- Nilai dinamis --}}
                    </div>
                    <div class="col-auto">
                        <i class="fas {{ $card_4_icon }} fa-2x text-gray-300"></i> {{-- Ikon dinamis --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Selamat Datang, {{ Auth::user()->name }}!</h6>
            </div>
            <div class="card-body">
                @if(Auth::user()->role == 'admin')
                    <p>Anda login sebagai Admin. Anda dapat melihat dan menyetujui semua data yang masuk dari user lain.</p>
                @else
                    <p>Anda login sebagai User. Semua data yang Anda buat (Biaya, Penjualan, Pembelian) akan memerlukan persetujuan dari Admin sebelum diproses.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection