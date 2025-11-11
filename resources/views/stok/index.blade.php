@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">Master Stok</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tambah / Update Stok Awal</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('stok.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="gudang_id">Pilih Gudang *</label>
                            <select name="gudang_id" id="gudang_id" class="form-control @error('gudang_id') is-invalid @enderror" required>
                                <option value="">Pilih...</option>
                                @foreach($gudangs as $gudang)
                                    <option value="{{ $gudang->id }}">{{ $gudang->nama_gudang }}</option>
                                @endforeach
                            </select>
                            @error('gudang_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="produk_id">Pilih Produk *</label>
                            <select name="produk_id" id="produk_id" class="form-control @error('produk_id') is-invalid @enderror" required>
                                <option value="">Pilih...</option>
                                @foreach($produks as $produk)
                                    <option value="{{ $produk->id }}">{{ $produk->nama_produk }}</option>
                                @endforeach
                            </select>
                            @error('produk_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="stok">Jumlah Stok *</label>
                            <input type="number" name="stok" id="stok" class="form-control @error('stok') is-invalid @enderror" value="0" min="0" required>
                            @error('stok') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan Stok</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Stok Saat Ini</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Gudang</th>
                                    <th>Produk</th>
                                    <th class="text-right">Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stokItems as $item)
                                <tr>
                                    <td>{{ $item->gudang->nama_gudang }}</td>
                                    <td>{{ $item->produk->nama_produk }}</td>
                                    <td class="text-right font-weight-bold">{{ $item->stok }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">Belum ada data stok.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection