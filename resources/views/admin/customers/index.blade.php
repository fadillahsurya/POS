@extends('layouts.admin')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard.admin') }}"><i class="bx bx-home"></i> Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Customer</li>
        </ol>
    </nav>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daftar Customer</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width:60px;">#</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th style="width:150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $i => $c)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $c->name }}</td>
                                <td>{{ $c->email }}</td>
                                <td>
                                    @if($c->is_active)
                                    <span class="badge bg-label-success me-1">Aktif</span>
                                    @else
                                    <span class="badge bg-label-warning me-1">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('customers.show', $c->id) }}" class="btn btn-info btn-sm">
                                        <i class="bx bx-user"></i> Lihat Profil
                                    </a>
                                    @if($c->is_active)
                                    <form action="{{ route('customers.disable', $c->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-danger btn-sm" onclick="return confirm('Nonaktifkan akun customer ini?')">
                                            <i class="bx bx-block"></i> Nonaktifkan
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            @if($customers->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada customer.</td>
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
