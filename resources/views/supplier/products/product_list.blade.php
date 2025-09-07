@extends('layouts.supplier')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Produk Saya</h5>
                    <a href="{{ route('supplier.products.create') }}" class="btn btn-success">
                        <i class="bx bx-plus"></i> Tambah Produk
                    </a>
                </div>
                <div class="card-body">
                    {{-- Filter & Search --}}
                    <form method="GET" class="row g-2 align-items-end mb-3">
                        <div class="col-md-4">
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama produk...">
                        </div>
                        <div class="col-md-3">
                            <select name="category_id" class="form-select">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->nama_kategori }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100"><i class="bx bx-search"></i> Filter</button>
                        </div>
                        @if(request()->has('q') || request()->has('category_id'))
                        <div class="col-md-2">
                            <a href="{{ route('supplier.products.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                        </div>
                        @endif
                    </form>

                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Produk</th>
                                    <th>Kode Produk</th>
                                    <th>Kategori</th>
                                    <th>Harga Beli</th>
                                    <th>Stok Supplier</th>
                                    <th>Deskripsi</th>
                                    <th>Aksi</th>
                                    <th>Tawarkan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $i => $p)
                                <tr>
                                    <td>{{ $products->firstItem() + $i }}</td>
                                    <td>{{ $p->nama_produk }}</td>
                                    <td>{{ $p->kode_produk }}</td>
                                    <td>{{ $p->category->nama_kategori ?? '-' }}</td>
                                    <td>Rp{{ number_format($p->harga_beli, 0, ',', '.') }}</td>
                                    <td>{{ $p->stok_supplier ?? 0 }}</td>
                                    <td>{{ $p->deskripsi ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('supplier.products.edit', $p->id) }}" class="btn btn-primary btn-sm mb-1">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form action="{{ route('supplier.products.destroy', $p->id) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger btn-sm"><i class="bx bx-trash"></i></button>
                                        </form>
                                    </td>
                                    <td>
                                        {{-- Tombol Tawarkan / Badge Status --}}
                                        @if($p->is_approved == 1)
                                            <span class="badge bg-success">Sudah Masuk Toko</span>
                                        @elseif($p->is_approved == 0 && $p->notif_admin_seen == 0)
                                            <span class="badge bg-warning text-dark">Menunggu Approval</span>
                                        @elseif($p->is_approved == 0 && $p->notif_admin_seen == 1)
                                            <form action="{{ route('supplier.products.offerToStore', $p->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-warning btn-sm" onclick="return confirm('Tawarkan produk ini ke admin?')">
                                                    <i class="bx bx-send"></i> Tawarkan ke Toko
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Belum ada produk.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-3">
                        {{ $products->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
