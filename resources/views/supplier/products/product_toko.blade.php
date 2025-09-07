@extends('layouts.supplier')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-package me-2"></i> Produk Toko Internal
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th>Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $p)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $p->nama_produk }}</td>
                                    <td>{{ $p->category->nama_kategori ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-label-primary">{{ $p->stok }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada produk.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection