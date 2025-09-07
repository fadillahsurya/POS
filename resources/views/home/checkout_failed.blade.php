@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">
        <div class="card shadow border-0 my-5">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <span class="avatar avatar-lg bg-danger bg-opacity-25 mb-3 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:70px;height:70px;">
                        <i class="bx bx-x-circle text-danger" style="font-size:2.6rem;"></i>
                    </span>
                </div>
                <h3 class="fw-bold text-danger mb-2">Pembayaran Gagal</h3>
                <p class="fs-5">Transaksi Anda gagal diproses atau pembayaran dibatalkan/expired.<br>Silakan ulangi transaksi atau hubungi admin jika ada kendala.</p>
                <a href="{{ route('home.katalog') }}" class="btn btn-primary px-4 mt-3">
                    <i class="bx bx-home"></i> Kembali ke Katalog
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
