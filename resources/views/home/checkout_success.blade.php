@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">
        <div class="card shadow border-0 my-5">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <span class="avatar avatar-lg bg-success bg-opacity-25 mb-3 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:70px;height:70px;">
                        <i class="bx bx-check-circle text-success" style="font-size:2.6rem;"></i>
                    </span>
                </div>
                <h3 class="fw-bold text-success mb-2">Checkout Berhasil!</h3>
                <p class="fs-5">Terima kasih telah melakukan pemesanan.<br>Order Anda sedang diproses.</p>
                <p class="mb-4 text-muted">Anda akan segera menerima email konfirmasi dan dapat melihat status pesanan di halaman riwayat transaksi.</p>
                <a href="{{ route('home.katalog') }}" class="btn btn-primary px-4">
                    <i class="bx bx-home"></i> Kembali ke Katalog
                </a>
            </div>
        </div>
    </div>
</div>
@endsection