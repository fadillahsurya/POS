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
                <a href="{{ route('products.index') }}">Produk</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Detail Produk
            </li>
        </ol>
    </nav>
</div>
<div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div>
                    <i class="bx bx-package me-2"></i> Detail Produk
                </div>
            </div>
            <div class="card-body">
                {{-- Notifikasi --}}
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <h4 class="mb-3 fw-bold">{{ $product->nama_produk }}</h4>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Kategori</div>
                    <div class="col-7">{{ $product->category->nama_kategori ?? '-' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Supplier</div>
                    <div class="col-7">
                        {{ $product->supplier->name ?? '-' }}
                        @if($product->supplier_id)
                            <span class="badge bg-label-info ms-1">Supplier</span>
                        @else
                            <span class="badge bg-label-secondary ms-1">Produk Toko</span>
                        @endif
                    </div>
                </div>

                {{-- Jika produk dari supplier --}}
                @if($product->supplier_id)
                    <div class="row mb-2">
                        <div class="col-5 text-muted">Harga dari Supplier</div>
                        <div class="col-7 text-primary fw-semibold">
                            @if(!is_null($product->harga_beli))
                                Rp{{ number_format($product->harga_beli,0,',','.') }}
                            @else
                                <span class="text-danger">Belum diisi supplier</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted">Stok Supplier</div>
                        <div class="col-7 fw-semibold">
                            {{ $product->stok_supplier ?? 0 }}
                        </div>
                    </div>
                    {{-- Form approve (jika belum di-approve) --}}
                    @if($product->is_approved == 0 && $product->supplier_id)
                        <form action="{{ route('products.approveSupplierProduct', $product->id) }}" method="POST" class="mt-4">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Harga Jual Toko (Rp)</label>
                                <input type="number" name="harga_jual" class="form-control" required min="1" value="{{ old('harga_jual') }}">
                                <small class="text-muted">Harga dari supplier: <b>Rp{{ number_format($product->harga_beli,0,',','.') }}</b></small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jumlah Stok yang Diambil ke Toko</label>
                                <input type="number" name="qty" class="form-control" required min="1" max="{{ $product->stok_supplier }}" value="{{ old('qty') }}">
                                <small class="text-muted">Stok tersedia di supplier: <b>{{ $product->stok_supplier ?? 0 }}</b></small>
                            </div>
                            <button type="submit" class="btn btn-success"><i class="bx bx-check"></i> Approve & Masukkan ke Toko</button>
                        </form>
                    @else
                        {{-- Jika sudah di-approve, tampilkan info --}}
                        <div class="alert alert-success mt-4">
                            Produk sudah di-approve dan masuk ke stok toko.
                        </div>
                    @endif
                @else
                    <div class="row mb-2">
                        <div class="col-5 text-muted">Harga Jual</div>
                        <div class="col-7 fw-semibold text-success">
                            Rp{{ number_format($product->harga_jual,0,',','.') }}
                        </div>
                    </div>
                @endif

                <div class="row mb-2">
                    <div class="col-5 text-muted">Stok Toko</div>
                    <div class="col-7">{{ $product->stok }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Deskripsi</div>
                    <div class="col-7">{!! nl2br(e($product->deskripsi ?? '-')) !!}</div>
                </div>

                @if($product->images && count($product->images))
                    <div class="mb-2">
                        <div class="text-muted mb-1">Foto Produk:</div>
                        <div>
                            @foreach($product->images as $img)
                                <img src="{{ asset('storage/'.$img->file_path) }}"
                                    width="90"
                                    class="rounded border m-1"
                                    style="object-fit:cover;max-height:90px;">
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center bg-light">
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali ke Daftar Produk
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
