<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Penjualan #{{ $penjualan->id }}</title>
    <style>
        @page { size: 58mm; margin: 0; }
        @media screen {
            html { background-color: #E0E0E0; }
            body { 
                margin: 1.5rem auto !important; 
                box-shadow: 0 0 6px rgba(0,0,0,0.3); 
                background: #fff; 
            }
        }
        body {
            width: 56mm;
            margin: 0;
            padding: 0;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 9pt;
            color: #000;
        }
        .container {
            padding: 2mm 2mm 3mm;
        }
        .header {
            text-align: center;
            margin-bottom: 4px;
        }
        .company-name {
            font-weight: bold;
            font-size: 11pt;
            margin: 0;
        }
        .title {
            font-size: 9pt;
            margin: 2px 0 0;
        }
        .divider {
            border: none;
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .info-table {
            width: 100%;
            font-size: 8.5pt;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 1px 0;
            vertical-align: top;
        }
        .info-table td:first-child {
            width: 36%;
        }
        
        /* ====================================================== */
        /* TATA LETAK TABEL ITEM (GAYA KIRI-KANAN) */
        /* ====================================================== */
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            font-size: 9pt;
        }
        .item-table thead {
            display: none; /* Sembunyikan header */
        }
        .item-table td {
            padding: 1.5px 0;
            vertical-align: top;
        }
        .item-table .label {
            width: 40%;
            text-align: left;
            padding-left: 2mm; /* Menjorok sedikit */
        }
        .item-table .value {
            width: 60%;
            text-align: right;
            padding-right: 1mm;
            white-space: nowrap;
        }
        .item-table .item-name {
            font-weight: bold;
            padding-top: 5px; /* Jarak antar item */
            padding-left: 0;
        }
        .item-table tr.item-last-row td {
            padding-bottom: 5px;
            border-bottom: 1px dashed #eee;
        }
        /* ====================================================== */
        
        .total-table {
            width: 100%;
            margin-top: 6px;
            font-size: 9.5pt;
            border-collapse: collapse;
        }
        .total-table td {
            padding: 1.5px 0;
        }
        .text-right {
            text-align: right;
            white-space: nowrap;
            padding-right: 1mm;
        }
        .grand-total {
            font-size: 11pt;
            font-weight: bold;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding-top: 3px;
            padding-bottom: 3px;
        }
        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 8pt;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="header">
            <p class="company-name">PT Giyats Automobile</p>
            <p class="title">INVOICE PENJUALAN</p>
        </div>

        <table class="info-table">
            <tr><td>Nomor</td><td>: INV-{{ $penjualan->id }}</td></tr>
            <tr><td>Tanggal</td><td>: {{ $penjualan->created_at->format('d/m/Y H:i') }}</td></tr>
            <tr><td>Pelanggan</td><td>: {{ $penjualan->pelanggan }}</td></tr>
            <tr><td>Dibuat oleh</td><td>: {{ $penjualan->user->name }}</td></tr>
            <tr><td>Status</td>
                <td>: 
                    @php
                        $statusText = $penjualan->status;
                        if ($penjualan->status == 'Pending') { $statusText = 'Pending Approval'; }
                        elseif ($penjualan->status == 'Approved') {
                            $statusText = 'Belum Dibayar';
                            if ($penjualan->tgl_jatuh_tempo && $penjualan->tgl_jatuh_tempo->isPast()) {
                                $statusText = 'Telat Dibayar';
                            }
                        }
                    @endphp
                    {{ $statusText }}
                </td>
            </tr>
            <tr><td>Tag</td><td>: {{ $penjualan->tag ?? '-' }}</td></tr>
            <tr><td>Ref</td><td>: {{ $penjualan->no_referensi ?? '-' }}</td></tr>
            <tr><td>Gudang</td><td>: {{ $penjualan->gudang ?? '-' }}</td></tr>
        </table>

        <hr class="divider">

        {{-- ====================================================== --}}
        {{-- STRUKTUR TABEL ITEM YANG BARU (GAYA KIRI-KANAN) --}}
        {{-- ====================================================== --}}
        <table class="item-table">
            <thead>
                <tr>
                    <th>Keterangan</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penjualan->items as $index => $item)
                {{-- Baris Nama Produk --}}
                <tr class="item-name">
                    <td colspan="2">{{ $item->produk->nama_produk }} ({{ $item->produk->item_code }})</td>
                </tr>
                {{-- Baris Qty --}}
                <tr>
                    <td class="label">Qty</td>
                    <td class="value">{{ $item->kuantitas }}</td>
                </tr>
                {{-- Baris Unit --}}
                <tr>
                    <td class="label">Unit</td>
                    <td class="value">{{ $item->unit ?? 'pcs' }}</td>
                </tr>
                {{-- Baris Harga --}}
                <tr>
                    <td class="label">Harga</td>
                    <td class="value">Rp{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                </tr>
                {{-- Baris Diskon (jika ada) --}}
                @if($item->diskon > 0)
                <tr>
                    <td class="label">Diskon</td>
                    <td class="value">{{ $item->diskon }}%</td>
                </tr>
                @endif
                {{-- Baris Jumlah --}}
                <tr class="item-last-row">
                    <td class="label">Jumlah</td>
                    <td class="value">Rp{{ number_format($item->jumlah_baris, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{-- ====================================================== --}}

        <hr class="divider">

        <table class="total-table">
            <tr>
                <td>Subtotal</td>
                <td class="text-right">Rp{{ number_format($penjualan->items->sum('jumlah_baris'), 0, ',', '.') }}</td>
            </tr>
            <tr class="grand-total">
                <td>GRAND TOTAL</td>
                <td class="text-right">Rp{{ number_format($penjualan->grand_total, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="footer">
            <p>-- Terima kasih --</p>
            <button type="button" class="no-print" onclick="window.print()">Print Ulang</button>
        </div>
    </div>
</body>
</html>