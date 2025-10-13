@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Edit Penjualan #{{ $penjualan->id }}</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('penjualan.update', $penjualan->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pelanggan">Pelanggan *</label>
                            <input type="text" class="form-control" name="pelanggan" value="{{ old('pelanggan', $penjualan->pelanggan) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tgl_transaksi">Tanggal Transaksi *</label>
                            <input type="date" class="form-control" name="tgl_transaksi" value="{{ old('tgl_transaksi', $penjualan->tgl_transaksi) }}" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                         <div class="form-group">
                            <label for="total">Total Tagihan *</label>
                            <input type="number" class="form-control" name="total" value="{{ old('total', $penjualan->total) }}" required>
                        </div>
                    </div>
                     <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" class="form-control">
                                <option value="Menunggu Pembayaran" {{ $penjualan->status == 'Menunggu Pembayaran' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                                <option value="Lunas" {{ $penjualan->status == 'Lunas' ? 'selected' : '' }}>Lunas</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <a href="{{ route('penjualan.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection