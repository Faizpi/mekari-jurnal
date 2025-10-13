{{-- resources/views/biaya/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Biaya #{{ $biaya->id }}</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            {{-- Form ini akan mengirim data ke route 'biaya.update' --}}
            <form action="{{ route('biaya.update', $biaya->id) }}" method="POST">
                @csrf
                @method('PUT') {{-- <-- Ini wajib untuk proses update --}}

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="penerima">Penerima *</label>
                            {{-- Tampilkan data lama menggunakan 'value' --}}
                            <input type="text" class="form-control" id="penerima" name="penerima" value="{{ old('penerima', $biaya->penerima) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tgl_transaksi">Tanggal Transaksi *</label>
                            <input type="date" class="form-control" id="tgl_transaksi" name="tgl_transaksi" value="{{ old('tgl_transaksi', $biaya->tgl_transaksi) }}" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kategori">Kategori</label>
                            <input type="text" class="form-control" id="kategori" name="kategori" value="{{ old('kategori', $biaya->kategori) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="total">Total Biaya *</label>
                            <input type="number" class="form-control" id="total" name="total" value="{{ old('total', $biaya->total) }}" required>
                        </div>
                    </div>
                </div>
                {{-- Anda bisa menambahkan field lain yang ingin diedit di sini dengan pola yang sama --}}

                <hr>

                <div class="text-right">
                    <a href="{{ route('biaya.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection