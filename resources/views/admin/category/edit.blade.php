@extends('layouts.admin')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard.admin') }}"><i class="bx bx-home"></i> Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('categories.index') }}">Kategori</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Edit Kategori</li>
        </ol>
    </nav>
</div>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card mb-4">
            <h5 class="card-header">Edit Kategori</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('categories.update', $category->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label">Nama Kategori</label>
                        <input type="text" name="nama_kategori" id="nama_kategori"
                            class="form-control @error('nama_kategori') is-invalid @enderror"
                            value="{{ old('nama_kategori', $category->nama_kategori) }}" required>
                        @error('nama_kategori')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="bx bx-arrow-back"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
