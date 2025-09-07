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
                Supplier
            </li>
        </ol>
    </nav>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daftar Supplier</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="table-responsive text-nowrap">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width:60px;">#</th>
                                <th>Nama Supplier</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Produk</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($suppliers as $i => $s)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $s->name }}</td>
                                <td>{{ $s->email }}</td>
                                <td>
                                    @if($s->is_active)
                                    <span class="badge bg-label-success me-1">Aktif</span>
                                    @else
                                    <span class="badge bg-label-warning me-1">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('suppliers.products', $s->id) }}" class="btn btn-info btn-sm">
                                        <i class="bx bx-package"></i> Lihat Produk
                                    </a>
                                </td>
                                <td class="d-flex gap-1">
                                    <!-- Tombol Detail -->
                                    <a href="{{ route('suppliers.show', $s->id) }}" class="btn btn-primary btn-sm">
                                        <i class="bx bx-show"></i> Detail
                                    </a>

                                    <!-- Tombol Aktif/Nonaktif -->
                                    @if($s->is_active)
                                    <form method="POST" action="{{ route('suppliers.nonaktif', $s->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-sm"
                                            onclick="return confirm('Nonaktifkan supplier ini?')">
                                            <i class="bx bx-x"></i> Nonaktifkan
                                        </button>
                                    </form>
                                    @else
                                    <form method="POST" action="{{ route('suppliers.verify', $s->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="bx bx-check"></i> Setujui
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            @if($suppliers->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada supplier.</td>
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
