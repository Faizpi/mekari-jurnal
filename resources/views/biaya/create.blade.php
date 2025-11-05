@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Buat Biaya</h1>
        <h3 class="font-weight-bold text-right" id="grand-total-display">Total Rp0,00</h3>
    </div>

    <form action="{{ route('biaya.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card shadow mb-4">
            <div class="card-body">
                {{-- BAGIAN ATAS --}}
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="bayar_dari">Bayar Dari *</label>
                            <select class="form-control @error('bayar_dari') is-invalid @enderror" name="bayar_dari" required>
                                <option value="Kas (1-10001)" {{ old('bayar_dari') == 'Kas (1-10001)' ? 'selected' : '' }}>Kas (1-10001)</option>
                                <option value="Bank (1-10002)" {{ old('bayar_dari') == 'Bank (1-10002)' ? 'selected' : '' }}>Bank (1-10002)</option>
                            </select>
                            @error('bayar_dari')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                {{-- DETAIL BIAYA --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="penerima">Penerima</label>
                            <input type="text" class="form-control @error('penerima') is-invalid @enderror" id="penerima" name="penerima" value="{{ old('penerima') }}">
                            @error('penerima')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                         <div class="form-group">
                            <label for="alamat_penagihan">Alamat Penagihan</label>
                            <textarea class="form-control @error('alamat_penagihan') is-invalid @enderror" name="alamat_penagihan" rows="2">{{ old('alamat_penagihan') }}</textarea>
                            @error('alamat_penagihan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tgl_transaksi">Tgl Transaksi *</label>
                                    <input type="date" class="form-control @error('tgl_transaksi') is-invalid @enderror" id="tgl_transaksi" name="tgl_transaksi" value="{{ old('tgl_transaksi', date('Y-m-d')) }}" required>
                                    @error('tgl_transaksi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cara_pembayaran">Cara Pembayaran</label>
                                    <select class="form-control @error('cara_pembayaran') is-invalid @enderror" name="cara_pembayaran">
                                        <option value="Tunai" {{ old('cara_pembayaran') == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                                        <option value="Transfer Bank" {{ old('cara_pembayaran') == 'Transfer Bank' ? 'selected' : '' }}>Transfer Bank</option>
                                    </select>
                                    @error('cara_pembayaran')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                    <input type="text" class="form-control @error('tag') is-invalid @enderror" id="tag" name="tag" value="{{ old('tag') }}">
                                    @error('tag')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TABEL AKUN BIAYA --}}
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
                            {{-- Tampilkan baris lama jika ada error validasi --}}
                            @if(old('kategori'))
                                @foreach(old('kategori') as $index => $oldKategori)
                                    <tr>
                                        <td><input type="text" class="form-control" name="kategori[]" value="{{ $oldKategori }}" placeholder="Contoh: Biaya Kantor"></td>
                                        <td><input type="text" class="form-control" name="deskripsi_akun[]" value="{{ old('deskripsi_akun.'.$index) }}"></td>
                                        <td>
                                            <select class="form-control expense-tax" name="pajak[]">
                                                <option value="0" {{ old('pajak.'.$index) == 0 ? 'selected' : '' }}>Tidak Ada Pajak</option>
                                                <option value="11" {{ old('pajak.'.$index) == 11 ? 'selected' : '' }}>PPN (11%)</option>
                                            </select>
                                        </td>
                                        <td><input type="number" class="form-control text-right expense-amount" name="total[]" value="{{ old('total.'.$index) }}" placeholder="0" required></td>
                                        <td>
                                            @if($index > 0)
                                                <button type="button" class="btn btn-danger btn-sm remove-row-btn">X</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                {{-- Baris default jika tidak ada error --}}
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
                            @endif
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-dark btn-sm" id="add-row-btn">+ Tambah Data</button>
                
                {{-- Pesan Error untuk Validasi Array --}}
                @error('kategori') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                @error('kategori.*') <div class="text-danger small mt-2">Error di baris Kategori: {{ $message }}</div> @enderror
                @error('pajak.*') <div class="text-danger small mt-2">Error di baris Pajak: {{ $message }}</div> @enderror
                @error('total.*') <div class="text-danger small mt-2">Error di baris Jumlah: {{ $message }}</div> @enderror

                {{-- BAGIAN BAWAH --}}
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="memo">Memo</label>
                            <textarea class="form-control @error('memo') is-invalid @enderror" id="memo" name="memo" rows="2">{{ old('memo') }}</textarea>
                            @error('memo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="lampiran">Lampiran</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('lampiran') is-invalid @enderror" id="lampiran" name="lampiran">
                                <label class="custom-file-label" for="lampiran">Pilih file...</label>
                            </div>
                            @error('lampiran')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
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
        calculateTotalExpense(); // Hitung ulang saat baris baru ditambahkan
    });

    tableBody.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-row-btn')) {
            event.target.closest('tr').remove();
            calculateTotalExpense();
        }
    });
    
    // Hitung total saat halaman dimuat (untuk data 'old' jika ada)
    calculateTotalExpense();

    // Script untuk menampilkan nama file di input
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