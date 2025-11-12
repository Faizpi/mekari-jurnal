@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Penjualan</h1>
    <a href="{{ route('penjualan.create') }}" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Buat Penagihan Baru
    </a>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

<div class="row">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Faktur Belum Dibayar</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalBelumDibayar, 0, ',', '.') }}</div>
                </div>
                <div class="col-auto"><i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i></div>
            </div></div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Penagihan Telat Dibayar</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalTelatDibayar, 0, ',', '.') }}</div>
                </div>
                <div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i></div>
            </div></div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Pelunasan (30 Hari Terakhir)</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($pelunasan30Hari, 0, ',', '.') }}</div>
                </div>
                <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
            </div></div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Penjualan</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nomor</th>
                        <th>Pembuat</th>
                        <th>Pelanggan</th>
                        <th>Gudang</th>
                        <th class="text-right">Grand Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($penjualans as $item)
                    <tr>
                        <td>{{ $item->tgl_transaksi->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('penjualan.show', $item->id) }}">
                                <strong>INV-{{ $item->id }}</strong>
                            </a>
                        </td>
                        <td>{{ $item->user->name }}</td>
                        <td>{{ $item->pelanggan }}</td>
                        <td>{{ $item->gudang->nama_gudang ?? 'N/A' }}</td>
                        <td class="text-right font-weight-bold">Rp {{ number_format($item->grand_total, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @php
                                $statusBadge = 'badge-secondary'; $statusText = $item->status;
                                if ($item->status == 'Pending') {
                                    $statusBadge = 'badge-warning'; $statusText = 'Pending Approval';
                                } elseif ($item->status == 'Approved') {
                                    $statusBadge = 'badge-info'; $statusText = 'Belum Dibayar';
                                    if ($item->tgl_jatuh_tempo && $item->tgl_jatuh_tempo->isPast()) {
                                        $statusBadge = 'badge-danger'; $statusText = 'Telat Dibayar';
                                    }
                                } elseif ($item->status == 'Lunas') {
                                    $statusBadge = 'badge-success'; $statusText = 'Lunas';
                                }
                            @endphp
                            <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
                        </td>
                        <td class="text-center">
                            @if(auth()->user()->role == 'admin')
                                <a href="{{ route('penjualan.edit', $item->id) }}" class="btn btn-warning btn-circle btn-sm" title="Edit">
                                    <i class="fas fa-pen"></i>
                                </a>
                                @if($item->status == 'Pending')
                                    <form action="{{ route('penjualan.approve', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-circle btn-sm" title="Setujui">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                                @if($item->status == 'Approved')
                                    <form action="{{ route('penjualan.markAsPaid', $item->id) }}" method="POST" class="d-inline" title="Tandai Lunas">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-circle btn-sm"><i class="fas fa-dollar-sign"></i></button>
                                    </form>
                                @endif
                            @endif

                            @if(auth()->user()->role == 'admin' || $item->status == 'Pending')
                                <button type="button" class="btn btn-danger btn-circle btn-sm" 
                                        data-toggle="modal" data-target="#deleteModal" data-action="{{ route('penjualan.destroy', $item->id) }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                            
                            @if(auth()->user()->role != 'admin' && $item->status != 'Pending')
                                <span class="text-muted small">Terkunci</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">Belum ada data penjualan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Anda Yakin?</h5><button class="close" type="button" data-dismiss="modal"><span aria-hidden="true">×</span></button></div>
            <div class="modal-body">Pilih "Hapus" di bawah ini jika Anda yakin untuk menghapus data ini.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('#deleteModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); 
        var action = button.data('action'); 
        var modal = $(this);
        modal.find('#deleteForm').attr('action', action);
    });
</script>
@endpush