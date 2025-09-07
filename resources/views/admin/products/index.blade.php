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
            <li class="breadcrumb-item active" aria-current="page">
                Produk
            </li>
        </ol>
    </nav>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Kelola Produk</h5>
                <a href="{{ route('products.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus"></i> Tambah Produk
                </a>
            </div>
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped">
    <thead>
        <tr>
            <th style="width: 60px;">#</th>
            <th>Kode Produk</th> {{-- kolom baru --}}
            <th>Nama Produk</th>
            <th>Kategori</th>
            <th>Supplier</th>
            <th>Harga Supplier</th>
            <th>Harga Jual</th>
            <th>Stok</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $i => $p)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $p->kode_produk }}</td> {{-- tampilkan kode produk --}}
            <td>{{ $p->nama_produk }}</td>
            <td>{{ $p->category->nama_kategori ?? '-' }}</td>
            <td>
                @if($p->supplier)
                    {{ $p->supplier->name }}
                @else
                    Internal
                @endif
            </td>
            <td>
                @if(!is_null($p->harga_beli))
                    Rp{{ number_format($p->harga_beli, 0, ',', '.') }}
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>
            <td>
                @if(!is_null($p->harga_jual))
                    Rp{{ number_format($p->harga_jual, 0, ',', '.') }}
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>
            <td>{{ $p->stok }}</td>
            <td>
                <a href="{{ route('products.edit', $p->id) }}" class="btn btn-warning btn-sm">
                    <i class="bx bx-edit"></i> Edit
                </a>
                <form action="{{ route('products.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus produk ini?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm">
                        <i class="bx bx-trash"></i> Hapus
                    </button>
                </form>
            </td>
        </tr>
        @endforeach

        @if($products->isEmpty())
        <tr>
            <td colspan="9" class="text-center text-muted">Belum ada produk.</td>
        </tr>
        @endif
    </tbody>
</table>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
