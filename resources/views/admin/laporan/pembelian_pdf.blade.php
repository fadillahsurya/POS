<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pembelian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 5px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        .data-table .text-center {
            text-align: center;
        }
        .data-table .text-right {
            text-align: right;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .summary h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PEMBELIAN</h1>
        <p>{{ config('app.name', 'Aplikasi POS') }}</p>
        <p>Periode: {{ \Carbon\Carbon::parse($tglAwal)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($tglAkhir)->format('d/m/Y') }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>Tanggal Cetak:</strong></td>
            <td>{{ now()->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Total Transaksi:</strong></td>
            <td>{{ $purchases->count() }} transaksi</td>
        </tr>
        <tr>
            <td><strong>Total Pembelian:</strong></td>
            <td><strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 20%;">Supplier</th>
                <th style="width: 35%;">Detail Produk</th>
                <th style="width: 15%;">Qty</th>
                <th style="width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchases as $index => $purchase)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($purchase->tanggal_beli)->format('d/m/Y') }}</td>
                    <td>{{ $purchase->supplier->name ?? '-' }}</td>
                    <td>
                        @foreach($purchase->purchaseItems as $item)
                            <div style="margin-bottom: 5px;">
                                <strong>{{ $item->product->nama ?? 'Produk tidak ditemukan' }}</strong><br>
                                <small>@ Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</small>
                            </div>
                        @endforeach
                    </td>
                    <td class="text-center">
                        @foreach($purchase->purchaseItems as $item)
                            <div style="margin-bottom: 5px;">
                                {{ $item->quantity }} pcs
                            </div>
                        @endforeach
                    </td>
                    <td class="text-right">
                        <strong>Rp {{ number_format($purchase->total_beli, 0, ',', '.') }}</strong>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data pembelian pada periode ini</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($purchases->count() > 0)
        <div class="summary">
            <h3>Ringkasan Laporan</h3>
            <table style="width: 100%;">
                <tr>
                    <td><strong>Total Transaksi Pembelian:</strong></td>
                    <td style="text-align: right;"><strong>{{ $purchases->count() }} transaksi</strong></td>
                </tr>
                <tr>
                    <td><strong>Total Item Dibeli:</strong></td>
                    <td style="text-align: right;"><strong>{{ $purchases->sum(function($p) { return $p->purchaseItems->sum('quantity'); }) }} pcs</strong></td>
                </tr>
                <tr style="border-top: 1px solid #333;">
                    <td><strong>TOTAL PEMBELIAN:</strong></td>
                    <td style="text-align: right;"><strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></td>
                </tr>
            </table>
        </div>
    @endif

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis pada {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
