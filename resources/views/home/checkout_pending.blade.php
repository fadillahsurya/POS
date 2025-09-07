@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">
        <div class="card shadow border-0 my-5">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <span class="avatar avatar-lg bg-warning bg-opacity-25 mb-3 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:70px;height:70px;">
                        <i class="bx bx-time-five text-warning" style="font-size:2.6rem;"></i>
                    </span>
                </div>
                <h3 class="fw-bold text-warning mb-2">Pembayaran Belum Selesai</h3>
                <p class="fs-5">Transaksi Anda masih <b>pending</b>.<br>Silakan selesaikan pembayaran atau cek status pesanan di <a href="">Pesanan Saya</a>.</p>
                <a href="{{ route('home.katalog') }}" class="btn btn-primary px-4 mt-3">
                    <i class="bx bx-home"></i> Kembali ke Katalog
                </a>
                {{-- Tambahkan Button Bayar Ulang di sini --}}
                @if(isset($order) && $order->status_order === 'pending')
                <a href="{{ route('home.bayarUlang', $order->midtrans_order_id) }}" class="btn btn-warning px-4 mt-3 ms-2">
                    <i class="bx bx-refresh"></i> Bayar Ulang
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
