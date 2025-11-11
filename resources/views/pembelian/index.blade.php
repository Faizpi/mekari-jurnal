@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Pembelian</h1>
    <a href="{{ route('pembelian.create') }}" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Buat Permintaan Baru
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
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Faktur Belum Dibayar</div>
                        {{-- (Nilai ini dihitung dari grand_total di controller) --}}
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($fakturBelumDibayar, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Faktur Telat Dibayar</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($fakturTelatBayar, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Permintaan Pembelian</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nomor</th>
                        <th>Pembuat</th>
                        <th>Staf Penyetuju</th>
                        <th>Urgensi</th>
                        <th class="text-right">Grand Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pembelians as $item)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item->tgl_transaksi)->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('pembelian.show', $item->id) }}">
                                <strong>PR-{{ $item->id }}</strong>
                            </a>
                        </td>
                        <td>{{ $item->user->name }}</td>
                        <td>{{ $item->staf_penyetuju }}</td>
                        <td>{{ $item->urgensi }}</td>
                        <td class="text-right font-weight-bold">Rp {{ number_format($item->grand_total, 0, ',', '.') }}</td>
                        <td class="text-center">
                             @if($item->status == 'Approved')
                                <span class="badge badge-success">{{ $item->status }}</span>
                            @elseif($item->status == 'Pending')
                                <span class="badge badge-warning">{{ $item->status }}</span>
                            @else
                                <span class="badge badge-info">{{ $item->status }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(auth()->user()->role == 'admin' || $item->status == 'Pending')
                                <a href="{{ route('pembelian.edit', $item->id) }}" class="btn btn-warning btn-circle btn-sm">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-circle btn-sm" 
                                        data-toggle="modal" 
                                        data-target="#deleteModal" 
                                        data-action="{{ route('pembelian.destroy', $item->id) }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @if(auth()->user()->role == 'admin' && $item->status == 'Pending')
                                    <form action="{{ route('pembelian.approve', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-circle btn-sm" title="Setujui">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                            @else
                                <span class="text-muted small">Terkunci</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">Belum ada data permintaan pembelian.</td>
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
        var button = $(event->relatedTarget); 
        var action = button.data('action'); 
        var modal = $(this);
        modal.find('#deleteForm').attr('action', action);
    });
</script>
@endpush