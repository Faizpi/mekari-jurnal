@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Buat Penagihan Penjualan</h1>
        <h3 class="font-weight-bold text-right" id="grand-total-display">Total Rp0,00</h3>
    </div>

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
                                    {{-- DIUBAH MENJADI INPUT TEXT --}}
                                    <input type="text" class="form-control" id="pelanggan" name="pelanggan" required placeholder="Ketik nama pelanggan...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="alamat_penagihan">Alamat Penagihan</label>
                            <textarea class="form-control" name="alamat_penagihan" rows="2"></textarea>
                        </div>
                        <div class="row">
                             <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tgl_transaksi">Tgl. Transaksi</label>
                                    <input type="date" class="form-control" id="tgl_transaksi" name="tgl_transaksi" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tgl_jatuh_tempo">Tgl. Jatuh Tempo</label>
                                    <input type="date" class="form-control" id="tgl_jatuh_tempo" name="tgl_jatuh_tempo">
                                </div>
                            </div>
                             <div class="col-md-4">
                                <div class="form-group">
                                    <label for="syarat_pembayaran">Syarat Pembayaran</label>
                                    <input type="text" class="form-control" id="syarat_pembayaran" name="syarat_pembayaran">
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
                            <input type="text" class="form-control" id="no_referensi" name="no_referensi">
                        </div>
                        <div class="form-group">
                            <label for="tag">Tag</label>
                            <input type="text" class="form-control" id="tag" name="tag">
                        </div>
                        <div class="form-group">
                            <label for="gudang">Gudang</label>
                            <input type="text" class="form-control" id="gudang" name="gudang">
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
                                <th style="width: 12%;">Harga Satuan</th>
                                <th style="width: 10%;">Diskon (%)</th>
                                <th class="text-right" style="width: 15%;">Jumlah</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="product-table-body">
                            <tr>
                                {{-- DIUBAH MENJADI INPUT TEXT --}}
                                <td><input type="text" class="form-control" name="produk[]" placeholder="Ketik nama produk..." required></td>
                                <td><input type="text" class="form-control" name="deskripsi[]"></td>
                                <td><input type="number" class="form-control product-quantity" name="kuantitas[]" value="1" min="1"></td>
                                <td><input type="number" class="form-control text-right product-price" name="harga_satuan[]" placeholder="0" required></td>
                                <td><input type="number" class="form-control text-right product-discount" name="diskon[]" placeholder="0" min="0" max="100"></td>
                                <td><input type="text" class="form-control text-right product-line-total" name="jumlah[]" placeholder="0" readonly></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-link pl-0" id="add-product-row">+ Tambah Data</button>

                {{-- BAGIAN BAWAH (MEMO & TOTAL) --}}
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="memo">Memo</label>
                            <textarea class="form-control" id="memo" name="memo" rows="4"></textarea>
                        </div>
                         <div class="form-group">
                            <label>Lampiran</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="lampiran">
                                <label class="custom-file-label" for="lampiran">Choose file...</label>
                            </div>
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

    calculateGrandTotal();
});
</script>
@endpush