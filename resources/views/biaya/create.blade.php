@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Buat Biaya</h1>
        <h3 class="font-weight-bold text-right" id="grand-total-display">Total Rp0,00</h3>
    </div>

    <form action="{{ route('biaya.store') }}" method="POST">
        @csrf
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="bayar_dari">Bayar Dari *</label>
                            <select class="form-control" name="bayar_dari" required>
                                <option value="Kas (1-10001)">Kas (1-10001)</option>
                                <option value="Bank (1-10002)">Bank (1-10002)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8 pt-4">
                         <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="bayar_nanti">
                            <label class="custom-control-label" for="bayar_nanti">Bayar Nanti</label>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="penerima">Penerima</label>
                            <input type="text" class="form-control" id="penerima" name="penerima">
                        </div>
                         <div class="form-group">
                            <label for="alamat_penagihan">Alamat Penagihan</label>
                            <textarea class="form-control" name="alamat_penagihan" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tgl_transaksi">Tgl Transaksi *</label>
                                    <input type="date" class="form-control" id="tgl_transaksi" name="tgl_transaksi" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cara_pembayaran">Cara Pembayaran</label>
                                    <select class="form-control" name="cara_pembayaran">
                                        <option value="Tunai">Tunai</option>
                                        <option value="Transfer Bank">Transfer Bank</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_biaya">No Biaya</label>
                                    <input type="text" class="form-control" id="no_biaya" name="no_biaya" placeholder="[Auto]" disabled>
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tag">Tag</label>
                                    <input type="text" class="form-control" id="tag" name="tag">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 30%;">Akun Biaya</th>
                                <th>Deskripsi</th>
                                <th style="width: 20%;">Pajak</th>
                                <th class="text-right" style="width: 25%;">Jumlah</th>
                                <th style="width: 5%"></th>
                            </tr>
                        </thead>
                        <tbody id="expense-table-body">
                            <tr>
                                <td><input type="text" class="form-control" name="kategori[]" placeholder="Contoh: Biaya Kantor"></td>
                                <td><input type="text" class="form-control" name="deskripsi_akun[]"></td>
                                <td>
                                    <select class="form-control expense-tax" name="pajak[]">
                                        <option value="0">Tidak Ada Pajak</option>
                                        <option value="11">PPN (11%)</option>
                                    </select>
                                </td>
                                <td><input type="number" class="form-control text-right expense-amount" name="total[]" placeholder="0" required></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-dark btn-sm" id="add-row-btn">+ Tambah Data</button>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="memo">Memo</label>
                            <textarea class="form-control" id="memo" name="memo" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                         <table class="table table-borderless text-right">
                            <tbody>
                                <tr>
                                    <td>Subtotal</td>
                                    <td id="subtotal-display">Rp0,00</td>
                                </tr>
                                 <tr>
                                    <td>Pajak (PPN 11%)</td>
                                    <td id="tax-display">Rp0,00</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="h5"><strong>Total</strong></td>
                                    <td class="h5" id="total-display"><strong>Rp0,00</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 text-right">
            <a href="{{ route('biaya.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-success">Buat Biaya Baru</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.getElementById('expense-table-body');
    const addRowBtn = document.getElementById('add-row-btn');

    const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);

    const calculateTotalExpense = () => {
        let subtotal = 0;
        let totalTax = 0;
        tableBody.querySelectorAll('tr').forEach(row => {
            const amount = parseFloat(row.querySelector('.expense-amount').value) || 0;
            const taxRate = parseFloat(row.querySelector('.expense-tax').value) || 0;
            subtotal += amount;
            if (taxRate > 0) {
                totalTax += amount * (taxRate / 100);
            }
        });
        const total = subtotal + totalTax;
        document.getElementById('subtotal-display').innerText = formatRupiah(subtotal);
        document.getElementById('tax-display').innerText = formatRupiah(totalTax);
        document.getElementById('total-display').innerHTML = `<strong>${formatRupiah(total)}</strong>`;
        document.getElementById('grand-total-display').innerText = `Total ${formatRupiah(total)}`;
    };

    tableBody.addEventListener('input', function(event) {
        if (event.target.classList.contains('expense-amount') || event.target.classList.contains('expense-tax')) {
            calculateTotalExpense();
        }
    });

    addRowBtn.addEventListener('click', function() {
        const newRow = tableBody.insertRow();
        newRow.innerHTML = `
            <td><input type="text" class="form-control" name="kategori[]" placeholder="Contoh: Biaya Internet"></td>
            <td><input type="text" class="form-control" name="deskripsi_akun[]"></td>
            <td>
                <select class="form-control expense-tax" name="pajak[]">
                    <option value="0">Tidak Ada Pajak</option>
                    <option value="11">PPN (11%)</option>
                </select>
            </td>
            <td><input type="number" class="form-control text-right expense-amount" name="total[]" placeholder="0" required></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row-btn">X</button></td>
        `;
        calculateTotalExpense();
    });

    tableBody.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-row-btn')) {
            event.target.closest('tr').remove();
            calculateTotalExpense();
        }
    });
    
    calculateTotalExpense();
});
</script>
@endpush