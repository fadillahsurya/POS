@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-md-3 col-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="avatar flex-shrink-0 me-3 bg-primary rounded">
                    <i class="bx bx-cube text-white fs-3"></i>
                </div>
                <div>
                    <h6 class="mb-1 text-muted">Total Produk</h6>
                    <h4 class="mb-0 fw-bold">{{ $produkCount ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="avatar flex-shrink-0 me-3 bg-success rounded">
                    <i class="bx bx-cart text-white fs-3"></i>
                </div>
                <div>
                    <h6 class="mb-1 text-muted">Total Transaksi</h6>
                    <h4 class="mb-0 fw-bold">{{ $transaksiCount ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="avatar flex-shrink-0 me-3 bg-warning rounded">
                    <i class="bx bx-package text-white fs-3"></i>
                </div>
                <div>
                    <h6 class="mb-1 text-muted">Stok Barang</h6>
                    <h4 class="mb-0 fw-bold">{{ $stokTotal ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="avatar flex-shrink-0 me-3 bg-dark rounded">
                    <i class="bx bx-user text-white fs-3"></i>
                </div>
                <div>
                    <h6 class="mb-1 text-muted">User Terdaftar</h6>
                    <h4 class="mb-0 fw-bold">{{ $userCount ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection