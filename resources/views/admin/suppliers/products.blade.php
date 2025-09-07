@extends('layouts.admin')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard.admin') }}">
                    <i class="bx bx-home"></i> Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('suppliers.index') }}">Supplier</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Produk dari {{ $supplier->name }}
            </li>
        </ol>
    </nav>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bx bx-package me-2"></i> Produk dari {{ $supplier->name }}
                </h5>
                <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px;">#</th>
                                <th>Nama Produk</th>
                                <th>Harga Beli</th>
                                <th>Stok</th>
                                <th style="width:240px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $i => $p)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $p->nama_produk }}</td>
                                <td>Rp{{ number_format($p->harga_beli,0,',','.') }}</td>
                                <td>
                                    <span class="badge bg-label-primary">{{ $p->stok }}</span>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('suppliers.orderProduct', [$supplier->id, $p->id]) }}" class="d-flex align-items-center gap-2">
                                        @csrf
                                        <input type="number" name="qty" min="1" max="{{ $p->stok }}" value="1" class="form-control form-control-sm" style="width:80px;" required>
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="bx bx-cart-add"></i> Pesan & Clone
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada produk dari supplier ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
