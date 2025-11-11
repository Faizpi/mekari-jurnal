@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Penjualan #INV-{{ $penjualan->id }}</h1>
        <div>
            @if(auth()->user()->role == 'admin')
                @if($penjualan->status == 'Pending')
                    <form action="{{ route('penjualan.approve', $penjualan->id) }}" method="POST" class="d-inline" title="Setujui data ini">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm shadow-sm"><i class="fas fa-check fa-sm"></i> Setujui</button>
                    </form>
                @endif
                @if($penjualan->status == 'Approved')
                    <form action="{{ route('penjualan.markAsPaid', $penjualan->id) }}" method="POST" class="d-inline" title="Tandai Lunas">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm shadow-sm"><i class="fas fa-dollar-sign fa-sm"></i> Tandai Lunas</button>
                    </form>
                @endif
            @endif
            
            <a href="{{ route('penjualan.print', $penjualan->id) }}" target="_blank" class="btn btn-info btn-sm shadow-sm">
                <i class="fas fa-print fa-sm"></i> Cetak Struk
            </a>
            <a href="{{ route('penjualan.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left fa-sm"></i> Kembali
            </a>
        </div>
    </div>
    
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Info Utama</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr><td style="width: 30%;"><strong>Pembuat</strong></td><td>: {{ $penjualan->user->name }}</td></tr>
                        <tr><td><strong>Pelanggan</strong></td><td>: {{ $penjualan->pelanggan }}</td></tr>
                        <tr><td><strong>Email</strong></td><td>: {{ $penjualan->email ?? '-' }}</td></tr>
                        <tr><td><strong>Tgl. Transaksi</strong></td><td>: {{ $penjualan->tgl_transaksi->format('d F Y H:i') }}</td></tr>
                        <tr><td><strong>Tgl. Jatuh Tempo</strong></td><td>: {{ $penjualan->tgl_jatuh_tempo ? $penjualan->tgl_jatuh_tempo->format('d F Y') : '-' }}</td></tr>
                        <tr><td><strong>Gudang</strong></td><td>: {{ $penjualan->gudang ?? '-' }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td style="width: 30%;"><strong>Status</strong></td>
                            <td>: 
                                @php
                                    $statusBadge = 'badge-secondary';
                                    $statusText = $penjualan->status;
                                    if ($penjualan->status == 'Pending') {
                                        $statusBadge = 'badge-warning'; $statusText = 'Pending Approval';
                                    } elseif ($penjualan->status == 'Approved') {
                                        $statusBadge = 'badge-info'; $statusText = 'Belum Dibayar';
                                        if ($penjualan->tgl_jatuh_tempo && $penjualan->tgl_jatuh_tempo->isPast()) {
                                            $statusBadge = 'badge-danger'; $statusText = 'Telat Dibayar';
                                        }
                                    } elseif ($penjualan->status == 'Lunas') {
                                        $statusBadge = 'badge-success'; $statusText = 'Lunas';
                                    }
                                @endphp
                                <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
                            </td>
                        </tr>
                         <tr>
                            <td><strong>Grand Total</strong></td>
                            <td>: <h4 class="font-weight-bold text-primary">Rp {{ number_format($penjualan->grand_total, 0, ',', '.') }}</h4></td>
                        </tr>
                         <tr><td><strong>No. Referensi</strong></td><td>: {{ $penjualan->no_referensi ?? '-' }}</td></tr>
                         <tr><td><strong>Tag</strong></td><td>: {{ $penjualan->tag ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Rincian Produk</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Item Code</th> {{-- <-- KOLOM BARU --}}
                            <th>Produk</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Kuantitas</th>
                            <th class="text-right">Harga Satuan</th>
                            <th class="text-center">Diskon (%)</th>
                            <th class="text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($penjualan->items as $item)
                        <tr>
                            <td>{{ $item->produk->item_code ?? 'N/A' }}</td> {{-- <-- DATA BARU --}}
                            <td>{{ $item->produk->nama_produk }}</td>
                            <td>{{ $item->deskripsi ?? '-' }}</td>
                            <td class="text-center">{{ $item->kuantitas }}</td>
                            <td class="text-right">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $item->diskon }}%</td>
                            <td class="text-right">Rp {{ number_format($item->jumlah_baris, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Memo</h6></div>
                <div class="card-body">{{ $penjualan->memo ?? 'Tidak ada memo.' }}</div>
            </div>
        </div>
        <div class="col-md-6">
             <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Lampiran</h6></div>
                <div class="card-body">
                    @if($penjualan->lampiran_path)
                        @php
                            $path = $penjualan->lampiran_path;
                            $isImage = in_array(pathinfo($path, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        @endphp
                        @if($isImage)
                            <a href="{{ asset('storage/' . $path) }}" target="_blank">
                                <img src="{{ asset('storage/' . $path) }}" alt="Lampiran" class="img-fluid rounded" style="max-height: 250px;">
                            </a>
                        @else
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="fas fa-file-alt fa-2x mr-3"></i>
                                <div><strong>File terlampir:</strong><br>
                                    <a href="{{ asset('storage/' . $path) }}" target="_blank">{{ basename($path) }}</a>
                                </div>
                            </div>
                        @endif
                    @else
                        <p class="text-muted">Tidak ada lampiran.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection