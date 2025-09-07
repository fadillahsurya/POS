<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Produk Terlaris</title>
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
        .rank-1 { background-color: #fff3cd; }
        .rank-2 { background-color: #f8f9fa; }
        .rank-3 { background-color: #f8f9fa; }
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
        .progress-bar {
            background-color: #e9ecef;
            height: 20px;
            border-radius: 3px;
            overflow: hidden;
            margin: 5px 0;
        }
        .progress-fill {
            background-color: #28a745;
            height: 100%;
            text-align: center;
            line-height: 20px;
            color: white;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PRODUK TERLARIS</h1>
        <p>{{ config('app.name', 'Aplikasi POS') }}</p>
        <p>Periode: {{ \Carbon\Carbon::parse($tglAwal)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($tglAkhir)->format('d/m/Y') }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>Tanggal Cetak:</strong></td>
            <td>{{ now()->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Total Produk:</strong></td>
            <td>{{ $produkTerlaris->count() }} produk</td>
        </tr>
        <tr>
            <td><strong>Total Terjual:</strong></td>
            <td><strong>{{ number_format($totalKeseluruhan, 0, ',', '.') }} pcs</strong></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;">Rank</th>
                <th style="width: 12%;">Kategori</th>
                <th style="width: 35%;">Nama Produk</th>
                <th style="width: 15%;">Stok</th>
                <th style="width: 15%;">Terjual</th>
                <th style="width: 15%;">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @forelse($produkTerlaris as $index => $item)
                @php
                    $rank = $index + 1;
                    $percentage = $totalKeseluruhan > 0 ? ($item->total_terjual / $totalKeseluruhan) * 100 : 0;
                    $rowClass = '';
                    if($rank == 1) $rowClass = 'rank-1';
                    elseif($rank <= 3) $rowClass = 'rank-2';
                @endphp
                <tr class="{{ $rowClass }}">
                    <td class="text-center">
                        <strong>{{ $rank }}</strong>
                        @if($rank == 1) 🏆
                        @elseif($rank == 2) 🥈
                        @elseif($rank == 3) 🥉
                        @endif
                    </td>
                    <td class="text-center">
                        {{ $item->product->category->nama_kategori ?? 'Tanpa Kategori' }}
                    </td>
                    <td>
                        <strong>{{ $item->product->nama_produk ?? 'Produk tidak ditemukan' }}</strong><br>
                        <small style="color: #666;">Rp {{ number_format($item->product->harga_jual ?? 0, 0, ',', '.') }}</small>
                    </td>
                    <td class="text-center">
                        {{ $item->product->stok ?? 0 }} pcs
                    </td>
                    <td class="text-center">
                        <strong>{{ number_format($item->total_terjual, 0, ',', '.') }} pcs</strong>
                    </td>
                    <td class="text-center">
                        <strong>{{ number_format($percentage, 1) }}%</strong>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $percentage }}%;">
                                {{ number_format($percentage, 1) }}%
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data penjualan pada periode ini</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($produkTerlaris->count() > 0)
        <div class="summary">
            <h3>Ringkasan Laporan</h3>
            <table style="width: 100%;">
                <tr>
                    <td><strong>Produk Terlaris #1:</strong></td>
                    <td style="text-align: right;">
                        <strong>{{ $produkTerlaris->first()->product->nama_produk ?? '-' }}</strong>
                        ({{ number_format($produkTerlaris->first()->total_terjual ?? 0, 0, ',', '.') }} pcs)
                    </td>
                </tr>
                <tr>
                    <td><strong>Total Jenis Produk:</strong></td>
                    <td style="text-align: right;"><strong>{{ $produkTerlaris->count() }} produk</strong></td>
                </tr>
                <tr>
                    <td><strong>Rata-rata Penjualan:</strong></td>
                    <td style="text-align: right;"><strong>{{ $produkTerlaris->count() > 0 ? number_format($totalKeseluruhan / $produkTerlaris->count(), 1, ',', '.') : 0 }} pcs/produk</strong></td>
                </tr>
                <tr style="border-top: 1px solid #333;">
                    <td><strong>TOTAL KESELURUHAN:</strong></td>
                    <td style="text-align: right;"><strong>{{ number_format($totalKeseluruhan, 0, ',', '.') }} pcs</strong></td>
                </tr>
            </table>
        </div>
    @endif

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis pada {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
