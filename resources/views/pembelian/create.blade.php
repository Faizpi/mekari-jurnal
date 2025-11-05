@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Buat Permintaan Pembelian</h1>
    </div>

    <form action="{{ route('pembelian.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="staf_penyetuju">Staf Penyetuju *</label>
                            <input type="text" class="form-control @error('staf_penyetuju') is-invalid @enderror" name="staf_penyetuju" value="{{ old('staf_penyetuju') }}" required>
                            @error('staf_penyetuju') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="tgl_transaksi">Tanggal Transaksi *</label>
                            <input type="date" class="form-control @error('tgl_transaksi') is-invalid @enderror" name="tgl_transaksi" value="{{ old('tgl_transaksi', date('Y-m-d')) }}" required>
                            @error('tgl_transaksi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="tgl_jatuh_tempo">Tanggal Jatuh Tempo</label>
                            <input type="date" class="form-control @error('tgl_jatuh_tempo') is-invalid @enderror" name="tgl_jatuh_tempo" value="{{ old('tgl_jatuh_tempo') }}">
                            @error('tgl_jatuh_tempo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email_penyetuju">Email Penyetuju</label>
                            <input type="email" class="form-control @error('email_penyetuju') is-invalid @enderror" name="email_penyetuju" value="{{ old('email_penyetuju') }}">
                            @error('email_penyetuju') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="urgensi">Urgensi *</label>
                            <select class="form-control @error('urgensi') is-invalid @enderror" name="urgensi" required>
                                <option value="Rendah" {{ old('urgensi') == 'Rendah' ? 'selected' : '' }}>Rendah</option>
                                <option value="Sedang" {{ old('urgensi') == 'Sedang' ? 'selected' : '' }}>Sedang</option>
                                <option value="Tinggi" {{ old('urgensi') == 'Tinggi' ? 'selected' : '' }}>Tinggi</option>
                            </select>
                            @error('urgensi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="tahun_anggaran">Tahun Anggaran</label>
                            <input type="text" class="form-control @error('tahun_anggaran') is-invalid @enderror" name="tahun_anggaran" value="{{ old('tahun_anggaran') }}">
                            @error('tahun_anggaran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="tag">Tag</label>
                            <input type="text" class="form-control @error('tag') is-invalid @enderror" name="tag" value="{{ old('tag') }}">
                            @error('tag') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- Tabel Produk --}}
                <div class="table-responsive mt-3">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 30%;">Produk</th>
                                <th>Deskripsi</th>
                                <th style="width: 15%;">Kuantitas</th>
                                <th style="width: 15%;">Unit</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="product-table-body">
                            @if(old('produk'))
                                @foreach(old('produk') as $index => $oldProduk)
                                    <tr>
                                        <td><input type="text" class="form-control" name="produk[]" value="{{ $oldProduk }}" required></td>
                                        <td><input type="text" class="form-control" name="deskripsi[]" value="{{ old('deskripsi.'.$index) }}"></td>
                                        <td><input type="number" class="form-control product-quantity" name="kuantitas[]" value="{{ old('kuantitas.'.$index) }}" min="1" required></td>
                                        <td><input type="text" class="form-control" name="unit[]" value="{{ old('unit.'.$index) }}"></td>
                                        <td>@if($index > 0)<button type="button" class="btn btn-danger btn-sm remove-row-btn">X</button>@endif</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td><input type="text" class="form-control" name="produk[]" placeholder="Ketik nama produk..." required></td>
                                    <td><input type="text" class="form-control" name="deskripsi[]"></td>
                                    <td><input type="number" class="form-control product-quantity" name="kuantitas[]" value="1" min="1" required></td>
                                    <td><input type="text" class="form-control" name="unit[]"></td>
                                    <td></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-link pl-0" id="add-product-row">+ Tambah Data</button>
                @error('produk.*') <div class="text-danger small mt-2">Error di baris Produk: {{ $message }}</div> @enderror
                @error('kuantitas.*') <div class="text-danger small mt-2">Error di baris Kuantitas: {{ $message }}</div> @enderror

                {{-- Bagian Bawah --}}
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="memo">Memo</label>
                            <textarea class="form-control @error('memo') is-invalid @enderror" name="memo" rows="4">{{ old('memo') }}</textarea>
                            @error('memo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label>Lampiran</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('lampiran') is-invalid @enderror" id="lampiran" name="lampiran">
                                <label class="custom-file-label" for="lampiran">Pilih file...</label>
                            </div>
                            @error('lampiran') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                        <label>Total Barang</label>
                        <h4 class="font-weight-bold" id="total-items-display">1 Barang</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 text-right">
            <a href="{{ route('pembelian.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Buat Permintaan</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.getElementById('product-table-body');
    const addRowBtn = document.getElementById('add-product-row');
    const totalItemsDisplay = document.getElementById('total-items-display');

    const calculateTotalItems = () => {
        let totalQuantity = 0;
        tableBody.querySelectorAll('.product-quantity').forEach(input => {
            totalQuantity += parseInt(input.value) || 0;
        });
        totalItemsDisplay.innerText = `${totalQuantity} Barang`;
    };
    
    tableBody.addEventListener('input', function(event) {
        if (event.target.classList.contains('product-quantity')) {
            calculateTotalItems();
        }
    });

    addRowBtn.addEventListener('click', function () {
        const newRow = tableBody.insertRow();
        newRow.innerHTML = `
            <td><input type="text" class="form-control" name="produk[]" placeholder="Ketik nama produk..." required></td>
            <td><input type="text" class="form-control" name="deskripsi[]"></td>
            <td><input type="number" class="form-control product-quantity" name="kuantitas[]" value="1" min="1" required></td>
            <td><input type="text" class="form-control" name="unit[]"></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row-btn">X</button></td>
        `;
        calculateTotalItems();
    });

    tableBody.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-row-btn')) {
            event.target.closest('tr').remove();
            calculateTotalItems();
        }
    });

    calculateTotalItems();

    // Script untuk menampilkan nama file
    document.querySelectorAll('.custom-file-input').forEach(input => {
        input.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                var fileName = e.target.files[0].name;
                var nextSibling = e.target.nextElementSibling;
                nextSibling.innerText = fileName;
            }
        });
    });
});
</script>
@endpush