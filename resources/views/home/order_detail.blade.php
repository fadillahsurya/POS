@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow mx-auto" style="max-width: 650px;">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <div>
                <span class="fw-semibold">Order #{{ $order->id }}</span>
                <span class="badge 
                    @if($order->status_order == 'pending') bg-warning text-dark 
                    @elseif($order->status_order == 'gagal') bg-danger 
                    @elseif($order->status_order == 'success' || $order->status_order == 'settlement') bg-success 
                    @else bg-secondary 
                    @endif">
                    {{ strtoupper($order->status_order) }}
                </span>
            </div>
            <a href="{{ route('home.myorders.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-arrow-back"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <h5 class="mb-3">Detail Pesanan</h5>
            <ul class="list-unstyled mb-3">
                <li>
                    <strong>Tanggal Order:</strong>
                    {{ \Carbon\Carbon::parse($order->tanggal_order)->format('d M Y H:i') }}
                </li>
                <li>
                    <strong>Alamat Kirim:</strong>
                    {{ $order->alamat_kirim ?: '-' }}
                </li>
                <li>
                    <strong>Total:</strong>
                    <span class="text-primary fw-bold">
                        Rp{{ number_format($order->total_order, 0, ',', '.') }}
                    </span>
                </li>
            </ul>

            <h6 class="mb-2">Produk:</h6>
            <div class="table-responsive mb-3">
                <table class="table table-sm table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $i => $item)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>
                                @if(isset($item->product))
                                    {{ $item->product->nama_produk }}
                                @else
                                    <em class="text-muted">Produk sudah dihapus</em>
                                @endif
                            </td>
                            <td>Rp{{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                            <td>{{ $item->qty }}</td>
                            <td>Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TOMBOL AKSI --}}
            @if($order->status_order == 'pending')
                <div class="mb-2 d-flex flex-wrap gap-2">
                    <form action="{{ route('home.myorders.lanjutkan_pembayaran', $order->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bx bx-credit-card"></i> Bayar Sekarang
                        </button>
                    </form>
                    <form action="{{ route('home.myorders.cancel', $order->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Batalkan pesanan ini?')">
                            <i class="bx bx-x"></i> Batalkan
                        </button>
                    </form>
                </div>
                <small class="text-muted">* Jika jendela pembayaran tidak muncul, klik "Bayar Sekarang".</small>
            @elseif($order->status_order == 'gagal')
                <div class="alert alert-danger mt-3 mb-0">
                    <i class="bx bx-x-circle"></i> Pesanan ini sudah dibatalkan atau kadaluarsa.
                </div>
            @else
                <div class="alert alert-success mt-3 mb-0">
                    <i class="bx bx-check-circle"></i> Pesanan berhasil diproses!
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
