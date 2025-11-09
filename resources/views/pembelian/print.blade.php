<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Pembelian #{{ $pembelian->id }}</title>
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
            margin: 1.5rem auto !important;;
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
        
        /* Tabel Item */
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .item-table th {
            font-size: 9pt;
            text-align: left;
            border-bottom: 1px dashed #000;
            padding-bottom: 2px;
        }
        .item-table th.text-right {
            text-align: right;
            padding-right: 1mm;
        }
        .item-table td {
            vertical-align: top;
            padding: 3px 0;
            border-bottom: 1px solid #eee;
        }
        .item-name {
            font-weight: bold;
            word-wrap: break-word;
            line-height: 1.2em;
        }
        .item-details {
            font-size: 8pt;
        }
        .item-qty {
            text-align: right;
            white-space: nowrap;
            padding-right: 1mm;
        }

        /* Total */
        .total-table {
            width: 100%;
            margin-top: 6px;
            font-size: 9.5pt;
            border-collapse: collapse;
        }
        .total-table td {
            padding: 1.5px 0;
            font-weight: bold;
        }
        .total-table .text-right {
            text-align: right;
            white-space: nowrap;
            padding-right: 1mm;
        }

        /* Footer */
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
            <p class="title">PERMINTAAN PEMBELIAN</p>
        </div>

        <table class="info-table">
            <tr><td>Nomor</td><td>: PR-{{ $pembelian->id }}</td></tr>
            <tr><td>Tanggal</td><td>: {{ $pembelian->created_at->format('d/m/Y H:i') }}</td></tr>
            <tr><td>Staf</td><td>: {{ $pembelian->staf_penyetuju }}</td></tr>
            <tr><td>Dibuat oleh</td><td>: {{ $pembelian->user->name }}</td></tr>
            <tr><td>Urgensi</td><td>: {{ $pembelian->urgensi }}</td></tr>
            <tr><td>Status</td><td>: {{ $pembelian->status }}</td></tr>
        </table>

        <hr class="divider">

        <table class="item-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="text-right">Qty</th>
                </tr>
            </thead>
            <tbody>
                @php $totalBarang = 0; @endphp
                @foreach($pembelian->items as $item)
                @php $totalBarang += $item->kuantitas; @endphp
                <tr>
                    <td>
                        <div class="item-name">{{ $item->produk }}</div>
                        @if($item->deskripsi)
                            <div class="item-details">({{ $item->deskripsi }})</div>
                        @endif
                    </td>
                    <td class="item-qty">
                        {{ $item->kuantitas }} {{ $item->unit ?? 'pcs' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <hr class="divider">

        <table class="total-table">
            <tr>
                <td>Total Barang</td>
                <td class="text-right">{{ $totalBarang }}</td>
            </tr>
        </table>

        <div class="footer">
            <p>-- Dokumen Internal --</p>
            <button type="button" class="no-print" onclick="window.print()">Print Ulang</button>
        </div>
    </div>
</body>
</html>