@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Pembelian #PR-{{ $pembelian->id }}</h1>
        <div>
            @if(auth()->user()->role == 'admin' && $pembelian->status == 'Pending')
                <form action="{{ route('pembelian.approve', $pembelian->id) }}" method="POST" class="d-inline" title="Setujui data ini">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm shadow-sm"><i class="fas fa-check fa-sm"></i> Setujui</button>
                </form>
            @endif
            <a href="{{ route('pembelian.print', $pembelian->id) }}" target="_blank" class="btn btn-info btn-sm shadow-sm">
                <i class="fas fa-print fa-sm"></i> Cetak Struk
            </a>
            <a href="{{ route('pembelian.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left fa-sm"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Info Utama</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr><td style="width: 30%;"><strong>Pembuat</strong></td><td>: {{ $pembelian->user->name }}</td></tr>
                        <tr><td><strong>Staf Penyetuju</strong></td><td>: {{ $pembelian->staf_penyetuju }}</td></tr>
                        <tr><td><strong>Email Penyetuju</strong></td><td>: {{ $pembelian->email_penyetuju ?? '-' }}</td></tr>
                        <tr><td><strong>Tgl. Transaksi</strong></td><td>: {{ $pembelian->tgl_transaksi->format('d F Y H:i') }}</td></tr>
                        <tr><td><strong>Gudang</strong></td><td>: {{ $pembelian->user->gudang->nama_gudang ?? '-' }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    @php
                        $subtotal = $pembelian->items->sum('jumlah_baris');
                        $taxAmount = $subtotal * ($pembelian->tax_percentage / 100);
                    @endphp
                    <table class="table table-borderless">
                        <tr>
                            <td style="width: 30%;"><strong>Status</strong></td>
                            <td>: 
                                @if($pembelian->status == 'Approved')
                                    <span class="badge badge-success">{{ $pembelian->status }}</span>
                                @elseif($pembelian->status == 'Pending')
                                    <span class="badge badge-warning">{{ $pembelian->status }}</span>
                                @else
                                    <span class="badge badge-danger">{{ $pembelian->status }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Subtotal</strong></td>
                            <td>: Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Pajak ({{ $pembelian->tax_percentage }}%)</strong></td>
                            <td>: Rp {{ number_format($taxAmount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Grand Total</strong></td>
                            <td>: <h4 class="font-weight-bold text-primary">Rp {{ number_format($pembelian->grand_total, 0, ',', '.') }}</h4></td>
                        </tr>
                         <tr><td><strong>Urgensi</strong></td><td>: {{ $pembelian->urgensi }}</td></tr>
                         <tr><td><strong>Tahun Anggaran</strong></td><td>: {{ $pembelian->tahun_anggaran ?? '-' }}</td></tr>
                         <tr><td><strong>Tag</strong></td><td>: {{ $pembelian->tag ?? '-' }}</td></tr>
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
                            <th>Item Code</th>
                            <th>Produk</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Kuantitas</th>
                            <th class="text-right">Harga Satuan</th>
                            <th class="text-center">Diskon (%)</th>
                            <th class="text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pembelian->items as $item)
                        <tr>
                            <td>{{ $item->produk->item_code ?? 'N/A' }}</td>
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
                <div class="card-body">{{ $pembelian->memo ?? 'Tidak ada memo.' }}</div>
            </div>
        </div>
        <div class="col-md-6">
             <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Lampiran</h6></div>
                <div class="card-body">
                    @if($pembelian->lampiran_path)
                        @php
                            $path = $pembelian->lampiran_path;
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