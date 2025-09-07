@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h2>Daftar Users</h2>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Tambah User</a>
</div>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $i => $user)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ ucfirst($user->role) }}</td>
            <td>
                @if($user->is_active)
                <span class="badge bg-success">Aktif</span>
                @else
                <span class="badge bg-danger">Non Aktif</span>
                @endif
            </td>
            <td>
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-warning">Edit</a>
                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Hapus user ini?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection