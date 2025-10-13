@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted mb-0">Pembelian</p>
            <h2 class="font-weight-bold">Buat Permintaan Pembelian</h2>
        </div>
    </div>

    <form action="{{ route('pembelian.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="staf_penyetuju">Staf Penyetuju *</label>
                            <input type="text" class="form-control" name="staf_penyetuju" required>
                        </div>
                        <div class="form-group">
                            <label for="tgl_transaksi">Tanggal Transaksi *</label>
                            <input type="date" class="form-control" name="tgl_transaksi" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="no_transaksi">No Transaksi</label>
                            <input type="text" class="form-control" name="no_transaksi" placeholder="[Auto]" disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email_penyetuju">Email Penyetuju</label>
                            <input type="email" class="form-control" name="email_penyetuju">
                        </div>
                         <div class="form-group">
                            <label for="tgl_jatuh_tempo">Tanggal Jatuh Tempo</label>
                            <input type="date" class="form-control" name="tgl_jatuh_tempo">
                        </div>
                        <div class="form-group">
                            <label for="urgensi">Urgensi *</label>
                            <select class="form-control" name="urgensi" required>
                                <option value="Rendah">Rendah</option>
                                <option value="Sedang" selected>Sedang</option>
                                <option value="Tinggi">Tinggi</option>
                            </select>
                        </div>
                    </div>
                </div>

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
                            <tr>
                                <td><select class="form-control" name="produk[]"><option>Pilih produk</option></select></td>
                                <td><input type="text" class="form-control" name="deskripsi[]"></td>
                                <td><input type="number" class="form-control product-quantity" name="kuantitas[]" value="1" min="1"></td>
                                <td><input type="text" class="form-control" name="unit[]"></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-link pl-0" id="add-product-row">+ Tambah Data</button>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="memo">Memo</label>
                            <textarea class="form-control" name="memo" rows="4"></textarea>
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
            <td><select class="form-control" name="produk[]"><option>Pilih produk</option></select></td>
            <td><input type="text" class="form-control" name="deskripsi[]"></td>
            <td><input type="number" class="form-control product-quantity" name="kuantitas[]" value="1" min="1"></td>
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
});
</script>
@endpush