@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <h5 class="card-header">Verifikasi Pengguna Baru</h5>
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
                                <th>#</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Daftar Pada</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($penggunas as $i => $u)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td>
                                    <span class="badge bg-label-info me-1">{{ ucfirst($u->role) }}</span>
                                </td>
                                <td>{{ $u->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('users.approve', $u->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Aktifkan akun ini?')">
                                            <i class="bx bx-check"></i> Setujui
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada pengguna menunggu verifikasi.</td>
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