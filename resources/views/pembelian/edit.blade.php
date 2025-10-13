@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Edit Permintaan Pembelian #{{ $pembelian->id }}</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('pembelian.update', $pembelian->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="staf_penyetuju">Staf Penyetuju *</label>
                            <input type="text" class="form-control" name="staf_penyetuju" value="{{ old('staf_penyetuju', $pembelian->staf_penyetuju) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tgl_transaksi">Tanggal Transaksi *</label>
                            <input type="date" class="form-control" name="tgl_transaksi" value="{{ old('tgl_transaksi', $pembelian->tgl_transaksi) }}" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="urgensi">Urgensi *</label>
                            <select name="urgensi" class="form-control" required>
                                <option value="Rendah" {{ $pembelian->urgensi == 'Rendah' ? 'selected' : '' }}>Rendah</option>
                                <option value="Sedang" {{ $pembelian->urgensi == 'Sedang' ? 'selected' : '' }}>Sedang</option>
                                <option value="Tinggi" {{ $pembelian->urgensi == 'Tinggi' ? 'selected' : '' }}>Tinggi</option>
                            </select>
                        </div>
                    </div>
                     <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" class="form-control">
                                <option value="Belum Ditagih" {{ $pembelian->status == 'Belum Ditagih' ? 'selected' : '' }}>Belum Ditagih</option>
                                <option value="Lunas" {{ $pembelian->status == 'Lunas' ? 'selected' : '' }}>Lunas</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <a href="{{ route('pembelian.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection