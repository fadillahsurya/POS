@extends('layouts.supplier')

@section('content')
<h3 class="mb-4 fw-bold"><i class="bx bx-store-alt me-2"></i> Dashboard Supplier</h3>
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="avatar bg-label-primary mb-3 rounded-circle" style="width:56px;height:56px;display:flex;align-items:center;justify-content:center;margin:auto;">
                    <i class="bx bx-package fs-2"></i>
                </div>
                <h6 class="text-muted mb-1">Total Produk</h6>
                <div class="display-4 fw-bold">{{ $totalProduk }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar bg-label-warning rounded-circle me-2">
                        <i class="bx bx-error fs-4"></i>
                    </div>
                    <h6 class="mb-0 text-warning">Produk Stok Menipis</h6>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse($produkMenipis as $p)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span>{{ $p->nama_produk }}</span>
                        <span class="badge bg-label-danger text-danger rounded-pill px-3 py-2">{{ $p->stok }}</span>
                    </li>
                    @empty
                    <li class="list-group-item px-0 py-2">Tidak ada produk menipis</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar bg-label-info rounded-circle me-2">
                        <i class="bx bx-time-five fs-4"></i>
                    </div>
                    <h6 class="mb-0 text-info">Aktivitas Terakhir</h6>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse($lastActivity as $act)
                    <li class="list-group-item px-0 py-2">{{ $act }}</li>
                    @empty
                    <li class="list-group-item px-0 py-2">Belum ada aktivitas</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection