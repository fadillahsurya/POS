@extends('layouts.admin')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard.admin') }}"><i class="bx bx-home"></i> Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('customers.index') }}">Customer</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Profil
            </li>
        </ol>
    </nav>
</div>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="flex-shrink-0 me-3">
                            <img src="{{ $customer->customer && $customer->customer->foto ? asset('storage/'.$customer->customer->foto) : asset('assets/img/avatars/1.png') }}"
                                alt="Foto Customer" class="rounded-circle shadow" width="90" height="90" style="object-fit:cover;">
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1">{{ $customer->customer->nama_customer ?? $customer->name }}</h4>
                            <div class="mb-2 text-muted" style="font-size: 1.05rem;">
                                <i class="bx bx-envelope"></i> {{ $customer->email }}
                            </div>
                            <span class="badge {{ $customer->is_active ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $customer->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>
                    <hr>
                    <dl class="row mb-0">
                        <dt class="col-4 text-end text-muted">No HP</dt>
                        <dd class="col-8">{{ $customer->customer->no_hp ?? '-' }}</dd>
                        <dt class="col-4 text-end text-muted">Alamat</dt>
                        <dd class="col-8">{{ $customer->customer->alamat ?? '-' }}</dd>
                    </dl>
                    <div class="text-end mt-4">
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-primary">
                            <i class="bx bx-arrow-back"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
