@extends('layouts.admin')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard.admin') }}"><i class="bx bx-home"></i> Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Kategori</li>
        </ol>
    </nav>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Kelola Kategori</h5>
                <a href="{{ route('categories.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus"></i> Tambah Kategori
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>Nama Kategori</th>
                                <th style="width: 180px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $i => $cat)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $cat->nama_kategori }}</td>
                                <td>
                                    <a href="{{ route('categories.edit', $cat->id) }}" class="btn btn-warning btn-sm">
                                        <i class="bx bx-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('categories.destroy', $cat->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus kategori ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm">
                                            <i class="bx bx-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Belum ada kategori.</td>
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
