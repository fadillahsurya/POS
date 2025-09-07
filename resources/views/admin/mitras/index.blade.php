@extends('layouts.admin')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard.admin') }}"><i class="bx bx-home"></i> Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Mitra</li>
        </ol>
    </nav>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daftar Mitra</h5>
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
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th style="width:200px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mitras as $i => $m)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $m->name }}</td>
                                <td>{{ $m->email }}</td>
                                <td>
                                    @if($m->is_active)
                                    <span class="badge bg-label-success me-1">Aktif</span>
                                    @else
                                    <span class="badge bg-label-warning me-1">Pending/Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('mitras.show', $m->id) }}" class="btn btn-primary btn-sm">
                                        <i class="bx bx-show"></i> Show
                                    </a>
                                    @if($m->is_active)
                                    <form action="{{ route('mitras.disable', $m->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-danger btn-sm" onclick="return confirm('Nonaktifkan akun mitra ini?')">
                                            <i class="bx bx-block"></i> Nonaktifkan
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            @if($mitras->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada mitra.</td>
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
