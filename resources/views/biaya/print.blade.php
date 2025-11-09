<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-R">
    <title>Struk Biaya #{{ $biaya->id }}</title>
    <style>
        /* CSS Sederhana untuk Struk Thermal 80mm */
        body {
            width: 76mm; /* Sedikit lebih kecil dari 80mm untuk margin */
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 2mm;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h3 {
            margin: 0;
            padding: 0;
            font-size: 14pt;
        }
        hr {
            border: 0;
            border-top: 1px dashed #000;
        }
        table {
            width: 100%;
            font-size: 10pt;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .info-table td:first-child {
            width: 40%;
        }
        .item-table th {
            text-align: left;
            border-bottom: 1px dashed #000;
        }
        .item-table td {
            border-bottom: 1px solid #eee;
        }
        .text-right {
            text-align: right;
        }
        .total {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 9pt;
        }

        /* Sembunyikan tombol saat print */
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

{{-- Tambahkan onLoad="window.print()" untuk langsung print saat dibuka --}}
<body onload="window.print()"> 
    <div class="container">
        <div class="header">
            <h3>BUKTI BIAYA</h3>
            <p>PT JAYA ABADI (Contoh)</p>
        </div>

        <hr>

        <table class="info-table">
            <tr>
                <td>Nomor</td>
                <td>: EXP-{{ $biaya->id }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: {{ $biaya->tgl_transaksi->format('d/m/Y H:i') }}</td>
            </tr>
             <tr>
                <td>Penerima</td>
                <td>: {{ $biaya->penerima }}</td>
            </tr>
            <tr>
                <td>Dibuat oleh</td>
                <td>: {{ $biaya->user->name }}</td>
            </tr>
             <tr>
                <td>Status</td>
                <td>: {{ $biaya->status }}</td>
            </tr>
        </table>

        <hr>

        <table class="item-table">
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($biaya->items as $item)
                <tr>
                    <td>
                        {{ $item->kategori }}
                        @if($item->deskripsi)
                            <br><small>({{ $item->deskripsi }})</small>
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <hr>

        <table class="total-table">
            <tr>
                <td class="total">GRAND TOTAL</td>
                <td class="total text-right">Rp {{ number_format($biaya->grand_total, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="footer">
            <p>-- Terima kasih --</p>
            <button type="button" class="no-print" onclick="window.print()">Print Ulang</button>
        </div>
    </div>

    {{-- Script untuk auto-close setelah print (opsional) --}}
    <script>
        window.onafterprint = function() {
            // window.close(); // Aktifkan jika ingin tab-nya auto-close
        }
    </script>
</body>
</html>