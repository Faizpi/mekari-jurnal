@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Buat Penagihan Penjualan</h1>
        <h3 class="font-weight-bold text-right" id="grand-total-display">Total Rp0,00</h3>
    </div>

    {{-- ====================================================== --}}
    {{-- PERBAIKAN: Tambahkan enctype="multipart/form-data" --}}
    {{-- ====================================================== --}}
    <form action="{{ route('penjualan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card shadow mb-4">
            <div class="card-body">
                {{-- BAGIAN ATAS FORM --}}
                <div class="row">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pelanggan">Pelanggan *</label>
                                    <input type="text" class="form-control @error('pelanggan') is-invalid @enderror" id="pelanggan" name="pelanggan" value="{{ old('pelanggan') }}" required>
                                    @error('pelanggan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="alamat_penagihan">Alamat Penagihan</label>
                            <textarea class="form-control @error('alamat_penagihan') is-invalid @enderror" name="alamat_penagihan" rows="2">{{ old('alamat_penagihan') }}</textarea>
                            @error('alamat_penagihan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row">
                             <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tgl_transaksi">Tgl. Transaksi *</label>
                                    <input type="date" class="form-control @error('tgl_transaksi') is-invalid @enderror" id="tgl_transaksi" name="tgl_transaksi" value="{{ old('tgl_transaksi', date('Y-m-d')) }}" required>
                                    @error('tgl_transaksi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tgl_jatuh_tempo">Tgl. Jatuh Tempo</label>
                                    <input type="date" class="form-control @error('tgl_jatuh_tempo') is-invalid @enderror" id="tgl_jatuh_tempo" name="tgl_jatuh_tempo" value="{{ old('tgl_jatuh_tempo') }}">
                                    @error('tgl_jatuh_tempo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                             <div class="col-md-4">
                                <div class="form-group">
                                    <label for="syarat_pembayaran">Syarat Pembayaran</label>
                                    <input type="text" class="form-control @error('syarat_pembayaran') is-invalid @enderror" id="syarat_pembayaran" name="syarat_pembayaran" value="{{ old('syarat_pembayaran') }}">
                                    @error('syarat_pembayaran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                         <div class="form-group">
                            <label for="no_transaksi">No Transaksi</label>
                            <input type="text" class="form-control" id="no_transaksi" name="no_transaksi" placeholder="[Auto]" disabled>
                        </div>
                        <div class="form-group">
                            <label for="no_referensi">No. Referensi Pelanggan</label>
                            <input type="text" class="form-control @error('no_referensi') is-invalid @enderror" id="no_referensi" name="no_referensi" value="{{ old('no_referensi') }}">
                            @error('no_referensi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="tag">Tag</label>
                            <input type="text" class="form-control @error('tag') is-invalid @enderror" id="tag" name="tag" value="{{ old('tag') }}">
                            @error('tag') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="gudang">Gudang</label>
                            <input type="text" class="form-control @error('gudang') is-invalid @enderror" id="gudang" name="gudang" value="{{ old('gudang') }}">
                            @error('gudang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- TABEL PRODUK/JASA --}}
                <div class="table-responsive mt-3">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 20%;">Produk</th>
                                <th>Deskripsi</th>
                                <th style="width: 8%;">Kuantitas</th>
                                <th style="width: 10%;">Unit</th> {{-- Kolom ini ada di JS tapi tidak ada di @foreach old() --}}
                                <th style="width: 12%;">Harga Satuan</th>
                                <th style="width: 10%;">Diskon (%)</th>
                                <th class="text-right" style="width: 15%;">Jumlah</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="product-table-body">
                            {{-- Tampilkan baris lama jika ada error validasi --}}
                            @if(old('produk'))
                                @foreach(old('produk') as $index => $oldProduk)
                                    <tr>
                                        <td><input type="text" class="form-control" name="produk[]" value="{{ $oldProduk }}" required></td>
                                        <td><input type="text" class="form-control" name="deskripsi[]" value="{{ old('deskripsi.'.$index) }}"></td>
                                        <td><input type="number" class="form-control product-quantity" name="kuantitas[]" value="{{ old('kuantitas.'.$index) }}" min="1" required></td>
                                        {{-- ====================================================== --}}
                                        {{-- PERBAIKAN: Tambahkan input 'unit[]' yang hilang --}}
                                        {{-- ====================================================== --}}
                                        <td><input type="text" class="form-control" name="unit[]" value="{{ old('unit.'.$index) }}"></td>
                                        <td><input type="number" class="form-control text-right product-price" name="harga_satuan[]" value="{{ old('harga_satuan.'.$index) }}" placeholder="0" required></td>
                                        <td><input type="number" class="form-control text-right product-discount" name="diskon[]" value="{{ old('diskon.'.$index) }}" placeholder="0" min="0" max="100"></td>
                                        <td><input type="text" class="form-control text-right product-line-total" name="jumlah[]" placeholder="0" readonly></td>
                                        <td>@if($index > 0)<button type="button" class="btn btn-danger btn-sm remove-row-btn">X</button>@endif</td>
                                    </tr>
                                @endforeach
                            @else
                                {{-- Baris default --}}
                                <tr>
                                    <td><input type="text" class="form-control" name="produk[]" placeholder="Ketik nama produk..." required></td>
                                    <td><input type="text" class="form-control" name="deskripsi[]"></td>
                                    <td><input type="number" class="form-control product-quantity" name="kuantitas[]" value="1" min="1"></td>
                                    <td><input type="text" class="form-control" name="unit[]"></td>
                                    <td><input type="number" class="form-control text-right product-price" name="harga_satuan[]" placeholder="0" required></td>
                                    <td><input type="number" class="form-control text-right product-discount" name="diskon[]" placeholder="0" min="0" max="100"></td>
                                    <td><input type="text" class="form-control text-right product-line-total" name="jumlah[]" placeholder="0" readonly></td>
                                    <td></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-link pl-0" id="add-product-row">+ Tambah Data</button>

                {{-- Tampilkan pesan error untuk validasi array --}}
                @error('produk.*') <div class="text-danger small mt-2">Error di baris Produk: {{ $message }}</div> @enderror
                @error('kuantitas.*') <div class="text-danger small mt-2">Error di baris Kuantitas: {{ $message }}</div> @enderror
                @error('harga_satuan.*') <div class="text-danger small mt-2">Error di baris Harga: {{ $message }}</div> @enderror

                {{-- BAGIAN BAWAH (MEMO & TOTAL) --}}
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="memo">Memo</label>
                            <textarea class="form-control @error('memo') is-invalid @enderror" id="memo" name="memo" rows="4">{{ old('memo') }}</textarea>
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
                    <div class="col-md-6">
                        <table class="table table-borderless text-right">
                            <tbody>
                                <tr>
                                    <td><strong>Subtotal</strong></td>
                                    <td id="subtotal-display">Rp0,00</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="h5"><strong>Total</strong></td>
                                    <td class="h5" id="total-display"><strong>Rp0,00</strong></td>
                                </tr>
                                <tr class="border-top">
                                    <td class="h5"><strong>Sisa Tagihan</strong></td>
                                    <td class="h5" id="sisa-tagihan-display"><strong>Rp0,00</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
        <div class="mt-3 text-right">
            <a href="{{ route('penjualan.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.getElementById('product-table-body');
    const addRowBtn = document.getElementById('add-product-row');
    
    const formatRupiah = (angka) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    };

    const calculateRow = (row) => {
        const quantity = parseFloat(row.querySelector('.product-quantity').value) || 0;
        const price = parseFloat(row.querySelector('.product-price').value) || 0;
        const discount = parseFloat(row.querySelector('.product-discount').value) || 0;
        const total = quantity * price * (1 - (discount / 100));
        row.querySelector('.product-line-total').value = total.toFixed(0);
        calculateGrandTotal();
    };

    const calculateGrandTotal = () => {
        let subtotal = 0;
        tableBody.querySelectorAll('tr').forEach(row => {
            const lineTotal = parseFloat(row.querySelector('.product-line-total').value) || 0;
            subtotal += lineTotal;
        });
        document.getElementById('subtotal-display').innerText = formatRupiah(subtotal);
        document.getElementById('total-display').innerHTML = `<strong>${formatRupiah(subtotal)}</strong>`;
        document.getElementById('sisa-tagihan-display').innerHTML = `<strong>${formatRupiah(subtotal)}</strong>`;
        document.getElementById('grand-total-display').innerText = `Total ${formatRupiah(subtotal)}`;
    };

    tableBody.addEventListener('input', function(event) {
        if (event.target.classList.contains('product-quantity') || event.target.classList.contains('product-price') || event.target.classList.contains('product-discount')) {
            calculateRow(event.target.closest('tr'));
        }
    });

    addRowBtn.addEventListener('click', function () {
        const newRow = tableBody.insertRow();
        newRow.innerHTML = `
            <td><input type="text" class="form-control" name="produk[]" placeholder="Ketik nama produk..." required></td>
            <td><input type="text" class="form-control" name="deskripsi[]"></td>
            <td><input type="number" class="form-control product-quantity" name="kuantitas[]" value="1" min="1"></td>
            <td><input type="text" class="form-control" name="unit[]"></td>
            <td><input type="number" class="form-control text-right product-price" name="harga_satuan[]" placeholder="0" required></td>
            <td><input type="number" class="form-control text-right product-discount" name="diskon[]" placeholder="0" min="0" max="100"></td>
            <td><input type="text" class="form-control text-right product-line-total" name="jumlah[]" placeholder="0" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row-btn">X</button></td>
        `;
    });

    tableBody.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-row-btn')) {
            event.target.closest('tr').remove();
            calculateGrandTotal();
        }
    });

    // Hitung total saat halaman dimuat (untuk data 'old' jika ada)
    tableBody.querySelectorAll('tr').forEach(row => calculateRow(row));

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