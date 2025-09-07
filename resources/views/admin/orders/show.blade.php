@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">🧾 Detail Transaksi #{{ $order->id }}</h4>
            
            <div class="d-flex gap-2">
                <a href="{{ route('orders.index', $order->midtrans_order_id ?? $order->id) }}" 
                   class="btn btn-secondary btn-sm">
                    ⬅️ Kembali
                </a>
                <button onclick="window.print()" class="btn btn-primary btn-sm">🖨️ Cetak Invoice</button>
            </div>
        </div>
        <div class="card-body">
            <p><strong>Tanggal:</strong> {{ $order->tanggal_order }}</p>
            <p><strong>Status:</strong> 
                @if ($order->status_order === 'selesai' || $order->status_order === 'lunas')
                    <span class="badge bg-success">{{ ucfirst($order->status_order) }}</span>
                @elseif ($order->status_order === 'pending')
                    <span class="badge bg-warning text-dark">{{ ucfirst($order->status_order) }}</span>
                @else
                    <span class="badge bg-secondary">{{ ucfirst($order->status_order) }}</span>
                @endif
            </p>
            <p><strong>Total:</strong> 
                <span class="fw-bold text-danger">
                    Rp{{ number_format($order->total_order, 0, ',', '.') }}
                </span>
            </p>
            <p><strong>Pelanggan:</strong> {{ $order->user->name ?? '-' }}</p>

            <hr>

            <h5>🛒 Daftar Produk</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Produk</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Harga Jual</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>{{ $item->product->nama_produk ?? 'Produk terhapus' }}</td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td class="text-end">Rp{{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                                <td class="text-end">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-3">
                <h5>Total Akhir: 
                    <span class="fw-bold text-danger">
                        Rp{{ number_format($order->total_order, 0, ',', '.') }}
                    </span>
                </h5>
            </div>
        </div>
    </div>
</div>

{{-- CSS khusus untuk print --}}
<style>
    @media print {
        .btn, .card-header { 
            display: none !important; 
        }
        body {
            background: #fff;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endsection
