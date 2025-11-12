@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Biaya</h1>
    <a href="{{ route('biaya.create') }}" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Buat Biaya Baru
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
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Biaya (Bulan Ini)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalBulanIni, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-calendar-alt fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Biaya (30 Hari Terakhir)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($total30Hari, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-money-bill-wave fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Biaya Belum Dibayar</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalBelumDibayar, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-comments-dollar fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Biaya</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nomor</th>
                        <th>Pembuat</th>
                        <th>Penerima</th>
                        <th class="text-right">Grand Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($biayas as $item)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item->tgl_transaksi)->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('biaya.show', $item->id) }}">
                                <strong>EXP-{{ $item->id }}</strong>
                            </a>
                        </td>
                        <td>{{ $item->user->name }}</td>
                        <td>{{ $item->penerima ?? '-' }}</td>
                        <td class="text-right font-weight-bold">Rp {{ number_format($item->grand_total, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($item->status == 'Approved')
                                <span class="badge badge-success">{{ $item->status }}</span>
                            @elseif($item->status == 'Pending')
                                <span class="badge badge-warning">{{ $item->status }}</span>
                            @else
                                <span class="badge badge-danger">{{ $item->status }}</span>
                            @endif
                        </td>
                        
                        {{-- =================================== --}}
                        {{-- PERUBAHAN LOGIKA TOMBOL AKSI --}}
                        {{-- =================================== --}}
                        <td class="text-center">
                            @if(auth()->user()->role == 'admin')
                                {{-- Admin bisa Edit & Approve --}}
                                <a href="{{ route('biaya.edit', $item->id) }}" class="btn btn-warning btn-circle btn-sm" title="Edit">
                                    <i class="fas fa-pen"></i>
                                </a>
                                @if($item->status == 'Pending')
                                    <form action="{{ route('biaya.approve', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-circle btn-sm" title="Setujui">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                            @endif

                            {{-- Admin DAN User bisa Hapus jika masih Pending --}}
                            @if(auth()->user()->role == 'admin' || $item->status == 'Pending')
                                <button type="button" class="btn btn-danger btn-circle btn-sm" 
                                        data-toggle="modal" data-target="#deleteModal" data-action="{{ route('biaya.destroy', $item->id) }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif

                            {{-- Tampilkan Terkunci jika BUKAN admin DAN status BUKAN pending --}}
                            @if(auth()->user()->role != 'admin' && $item->status != 'Pending')
                                <span class="text-muted small">Terkunci</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">Belum ada data biaya.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Anda Yakin?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
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
    // Script ini menggunakan jQuery yang sudah ada dari template
    $('#deleteModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Tombol yang memicu modal
        var action = button.data('action'); // Ambil URL dari atribut data-action

        var modal = $(this);
        // Set action form di dalam modal
        modal.find('#deleteForm').attr('action', action);
    });
</script>
@endpush