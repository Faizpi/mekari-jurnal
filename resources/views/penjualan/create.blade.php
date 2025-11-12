@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Buat Penagihan Penjualan</h1>
        <h3 class="font-weight-bold text-right" id="grand-total-display">Total Rp0,00</h3>
    </div>
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi Kesalahan Validasi:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
                                    <select class="form-control @error('pelanggan') is-invalid @enderror" id="pelanggan" name="pelanggan" required>
                                        <option value="">Pilih kontak...</option>
                                        @foreach($kontaks as $kontak)
                                            <option value="{{ $kontak->nama }}"
                                                    data-email="{{ $kontak->email }}"
                                                    data-alamat="{{ $kontak->alamat }}"
                                                    data-diskon="{{ $kontak->diskon_persen }}"
                                                    {{ old('pelanggan') == $kontak->nama ? 'selected' : '' }}>
                                                {{ $kontak->nama }}
                                            </option>
                                        @endforeach
                                    </select>
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
                            <textarea class="form-control @error('alamat_penagihan') is-invalid @enderror" id="alamat_penagihan" name="alamat_penagihan" rows="2">{{ old('alamat_penagihan') }}</textarea>
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
                                    <select class="form-control @error('syarat_pembayaran') is-invalid @enderror" id="syarat_pembayaran" name="syarat_pembayaran">
                                        <option value="Cash" {{ old('syarat_pembayaran') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="Net 7" {{ old('syarat_pembayaran') == 'Net 7' ? 'selected' : '' }}>Net 7</option>
                                        <option value="Net 14" {{ old('syarat_pembayaran') == 'Net 14' ? 'selected' : '' }}>Net 14</option>
                                        <option value="Net 20" {{ old('syarat_pembayaran') == 'Net 20' ? 'selected' : '' }}>Net 20</option>
                                        <option value="Net 30" {{ old('syarat_pembayaran') == 'Net 30' ? 'selected' : '' }}>Net 30</option>
                                        <option value="Net 60" {{ old('syarat_pembayaran') == 'Net 60' ? 'selected' : '' }}>Net 60</option>
                                    </select>
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
                            <label for="tag">Tag (Dibuat oleh)</label>
                            <input type="text" class="form-control @error('tag') is-invalid @enderror" id="tag" name="tag" value="{{ old('tag', auth()->user()->name) }}" readonly>
                            @error('tag') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="gudang_id">Gudang *</label>
                            @if(auth()->user()->role == 'admin')
                                <select class="form-control @error('gudang_id') is-invalid @enderror" id="gudang_id" name="gudang_id" required>
                                    <option value="">Pilih gudang...</option>
                                    @foreach($gudangs as $gudang)
                                        <option value="{{ $gudang->id }}" {{ old('gudang_id') == $gudang->id ? 'selected' : '' }}>
                                            {{ $gudang->nama_gudang }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="form-control" value="{{ auth()->user()->gudang->nama_gudang ?? 'User tidak terhubung ke gudang' }}" readonly>
                                <input type="hidden" name="gudang_id" value="{{ auth()->user()->gudang_id }}">
                            @endif
                            @error('gudang_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                <th style="width: 10%;">Unit</th>
                                <th style="width: 12%;">Harga Satuan</th>
                                <th style="width: 10%;">Diskon (%)</th>
                                <th class="text-right" style="width: 15%;">Jumlah</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="product-table-body">
                            @if(old('produk_id'))
                                @foreach(old('produk_id') as $index => $oldProdukId)
                                    <tr>
                                        <td>
                                            <select class="form-control product-select @error('produk_id.'.$index) is-invalid @enderror" name="produk_id[]" required>
                                                <option value="">Pilih produk...</option>
                                                @foreach($produks as $produk)
                                                    <option value="{{ $produk->id }}" 
                                                            data-harga="{{ $produk->harga }}" 
                                                            data-deskripsi="{{ $produk->deskripsi }}"
                                                            {{ $oldProdukId == $produk->id ? 'selected' : '' }}>
                                                        {{ $produk->nama_produk }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control product-description" name="deskripsi[]" value="{{ old('deskripsi.'.$index) }}"></td>
                                        <td><input type="number" class="form-control product-quantity" name="kuantitas[]" value="{{ old('kuantitas.'.$index) }}" min="1" required></td>
                                        <td>
                                            <select class="form-control" name="unit[]">
                                                <option value="Pcs" {{ old('unit.'.$index) == 'Pcs' ? 'selected' : '' }}>Pcs</option>
                                                <option value="Karton" {{ old('unit.'.$index) == 'Karton' ? 'selected' : '' }}>Karton</option>
                                            </select>
                                        </td>
                                        <td><input type="number" class="form-control text-right product-price" name="harga_satuan[]" value="{{ old('harga_satuan.'.$index) }}" placeholder="0" required></td>
                                        <td><input type="number" class="form-control text-right product-discount" name="diskon[]" value="{{ old('diskon.'.$index) }}" placeholder="0" min="0" max="100"></td>
                                        <td><input type="text" class="form-control text-right product-line-total" name="jumlah[]" placeholder="0" readonly></td>
                                        <td>@if($index > 0)<button type="button" class="btn btn-danger btn-sm remove-row-btn">X</button>@endif</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td>
                                        <select class="form-control product-select @error('produk_id.0') is-invalid @enderror" name="produk_id[]" required>
                                            <option value="">Pilih produk...</option>
                                            @foreach($produks as $produk)
                                                <option value="{{ $produk->id }}" data-harga="{{ $produk->harga }}" data-deskripsi="{{ $produk->deskripsi }}">
                                                    {{ $produk->nama_produk }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control product-description" name="deskripsi[]"></td>
                                    <td><input type="number" class="form-control product-quantity" name="kuantitas[]" value="1" min="1"></td>
                                    <td>
                                        <select class="form-control" name="unit[]">
                                            <option value="Pcs">Pcs</option>
                                            <option value="Karton">Karton</option>
                                        </select>
                                    </td>
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
                @error('produk_id.*') <div class="text-danger small mt-2">Error di baris Produk: {{ $message }}</div> @enderror
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
                            <label for="lampiran">Lampiran</label>
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
                                <tr>
                                    <td><label for="tax_percentage_input" class="mb-0"><strong>Pajak (%)</strong></label></td>
                                    <td style="width: 50%;">
                                        <input type="number" class="form-control text-right @error('tax_percentage') is-invalid @enderror" 
                                               id="tax_percentage_input" name="tax_percentage" value="{{ old('tax_percentage', 0) }}" min="0" step="0.01">
                                        @error('tax_percentage') 
                                            <div class="invalid-feedback d-block text-right">{{ $message }}</div> 
                                        @enderror
                                    </td>
                                </tr>
                                <tr>
                                    <td>Jumlah Pajak</td>
                                    <td id="tax-amount-display">Rp0,00</td>
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
    const taxInput = document.getElementById('tax_percentage_input');
    
    // Perbarui ID JavaScript
    const kontakSelect = document.getElementById('pelanggan');
    const emailInput = document.getElementById('email');
    const alamatInput = document.getElementById('alamat_penagihan');

    // --- FUNGSI AUTOFILL KONTAK ---
    kontakSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        emailInput.value = selectedOption.dataset.email || '';
        alamatInput.value = selectedOption.dataset.alamat || '';

        tableBody.querySelectorAll('tr').forEach(row => {
            const diskonInput = row.querySelector('.product-discount');
            if (diskonInput) {
                diskonInput.value = selectedOption.dataset.diskon || 0;
                calculateRow(row);
            }
        });
    });

    // Simpan HTML dropdown produk untuk baris baru
    const productDropdownHtml = `
        <select class="form-control product-select" name="produk_id[]" required>
            <option value="">Pilih produk...</option>
            @foreach($produks as $produk)
                <option value="{{ $produk->id }}" data-harga="{{ $produk->harga }}" data-deskripsi="{{ $produk->deskripsi }}">
                    {{ $produk->nama_produk }}
                </option>
            @endforeach
        </select>
    `;

    const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);

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
        let taxPercentage = parseFloat(taxInput.value) || 0;
        let taxAmount = subtotal * (taxPercentage / 100);
        const total = subtotal + taxAmount;
        document.getElementById('subtotal-display').innerText = formatRupiah(subtotal);
        document.getElementById('tax-amount-display').innerText = formatRupiah(taxAmount);
        document.getElementById('total-display').innerHTML = `<strong>${formatRupiah(total)}</strong>`;
        document.getElementById('sisa-tagihan-display').innerHTML = `<strong>${formatRupiah(total)}</strong>`;
        document.getElementById('grand-total-display').innerText = `Total ${formatRupiah(total)}`;
    };

    const handleProductChange = (event) => {
        if (!event.target.classList.contains('product-select')) return;
        const selectedOption = event.target.options[event.target.selectedIndex];
        const row = event.target.closest('tr');
        const harga = selectedOption.dataset.harga || 0;
        const deskripsi = selectedOption.dataset.deskripsi || '';
        row.querySelector('.product-price').value = harga;
        row.querySelector('.product-description').value = deskripsi;
        const kontakOption = kontakSelect.options[kontakSelect.selectedIndex];
        if (kontakOption) {
            row.querySelector('.product-discount').value = kontakOption.dataset.diskon || 0;
        }
        calculateRow(row);
    };

    tableBody.addEventListener('input', function(event) {
        if (event.target.classList.contains('product-quantity') || event.target.classList.contains('product-price') || event.target.classList.contains('product-discount')) {
            calculateRow(event.target.closest('tr'));
        }
    });

    taxInput.addEventListener('input', calculateGrandTotal);
    tableBody.addEventListener('change', handleProductChange);

    addRowBtn.addEventListener('click', function () {
        const newRow = tableBody.insertRow();
        newRow.innerHTML = `
            <td>${productDropdownHtml}</td>
            <td><input type="text" class="form-control product-description" name="deskripsi[]"></td>
            <td><input type="number" class="form-control product-quantity" name="kuantitas[]" value="1" min="1"></td>
            <td>
                <select class="form-control" name="unit[]">
                    <option value="Pcs">Pcs</option>
                    <option value="Karton">Karton</option>
                </select>
            </td>
            <td><input type="number" class="form-control text-right product-price" name="harga_satuan[]" placeholder="0" required></td>
            <td><input type="number" class="form-control text-right product-discount" name="diskon[]" placeholder="0" min="0" max="100"></td>
            <td><input type="text" class="form-control text-right product-line-total" name="jumlah[]" placeholder="0" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row-btn">X</button></td>
        `;
        const kontakOption = kontakSelect.options[kontakSelect.selectedIndex];
        const diskonInput = newRow.querySelector('.product-discount');
        if (diskonInput && kontakOption) {
            diskonInput.value = kontakOption.dataset.diskon || 0;
        }
    });

    tableBody.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-row-btn')) {
            event.target.closest('tr').remove();
            calculateGrandTotal();
        }
    });

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