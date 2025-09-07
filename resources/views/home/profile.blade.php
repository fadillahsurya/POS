@extends('layouts.app')

@section('content')
@php
    $role = auth()->user()->role;
    $profileData = [
        'nama'    => auth()->user()->name,
        'no_hp'   => auth()->user()->no_hp ?? '',
        'alamat'  => auth()->user()->alamat ?? '',
    ];
    if ($role === 'customer') {
        $customer = \App\Models\Customer::where('user_id', auth()->id())->first();
        if ($customer) {
            $profileData['nama']    = $customer->nama_customer ?? auth()->user()->name;
            $profileData['no_hp']   = $customer->no_hp ?? '';
            $profileData['alamat']  = $customer->alamat ?? '';
        }
    } elseif ($role === 'mitra') {
        $mitra = \App\Models\Mitra::where('user_id', auth()->id())->first();
        if ($mitra) {
            $profileData['nama']    = $mitra->nama_mitra ?? auth()->user()->name;
            $profileData['no_hp']   = $mitra->no_hp ?? '';
            $profileData['alamat']  = $mitra->alamat ?? '';
        }
    } elseif ($role === 'supplier') {
        $supplier = \App\Models\Supplier::where('user_id', auth()->id())->first();
        if ($supplier) {
            $profileData['nama']    = $supplier->nama_supplier ?? auth()->user()->name;
            $profileData['no_hp']   = $supplier->no_hp ?? '';
            $profileData['alamat']  = $supplier->alamat ?? '';
        }
    }
@endphp

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow mb-4 border-0">
            <div class="card-header bg-primary d-flex align-items-center">
                <i class="bx bx-user-circle me-2" style="font-size: 1.5rem"></i>
                <span class="text-white fw-semibold">Profil Saya</span>
            </div>
            <div class="card-body pb-3">
                @if(session('success'))
                    <div class="alert alert-success text-center">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="text-center mb-4">
                    <img src="{{ asset('assets/img/avatars/1.png') }}" class="rounded-circle shadow-sm" width="90" height="90" alt="Avatar">
                    <div class="fw-bold mt-2" style="font-size: 1.1rem;">
                        {{ $profileData['nama'] }}
                    </div>
                </div>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label text-end">
                            @if ($role == 'customer')
                                Nama Customer
                            @elseif ($role == 'mitra')
                                Nama Mitra
                            @elseif ($role == 'supplier')
                                Nama Supplier
                            @else
                                Nama
                            @endif
                        </label>
                        <div class="col-sm-8">
                            <input type="text" name="nama" class="form-control"
                                   value="{{ old('nama', $profileData['nama']) }}" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label text-end">Email</label>
                        <div class="col-sm-8">
                            <input type="email" class="form-control" readonly value="{{ auth()->user()->email }}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label text-end">Nomor HP</label>
                        <div class="col-sm-8">
                            <input type="text" name="no_hp" class="form-control"
                                   value="{{ old('no_hp', $profileData['no_hp']) }}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label text-end">Alamat</label>
                        <div class="col-sm-8">
                            <textarea name="alamat" class="form-control" rows="2">{{ old('alamat', $profileData['alamat']) }}</textarea>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label class="col-sm-4 col-form-label text-end">Role</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control-plaintext" readonly value="{{ ucfirst($role) }}">
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bx bx-save"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('home.katalog') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back"></i> Kembali
                        </a>
                    </div>
                </form>
                <hr class="my-4">
<div class="card shadow border-0">
    <div class="card-header bg-light d-flex align-items-center">
        <i class="bx bx-lock me-2" style="font-size:1.2rem"></i>
        <span class="fw-semibold">Ubah Password</span>
    </div>
    <div class="card-body pb-2">
        <form method="POST" action="{{ route('profile.password') }}">
            @csrf
            @if(session('password_success'))
                <div class="alert alert-success">{{ session('password_success') }}</div>
            @endif
            @if(session('password_error'))
                <div class="alert alert-danger">{{ session('password_error') }}</div>
            @endif

            <div class="row mb-3">
                <label class="col-sm-4 col-form-label text-end">Password Lama</label>
                <div class="col-sm-8">
                    <input type="password" name="old_password" class="form-control" required autocomplete="current-password">
                </div>
            </div>
<div class="row mb-3">
    <label class="col-sm-4 col-form-label text-end">Password Baru</label>
    <div class="col-sm-8 position-relative">
        <input type="password" name="new_password" id="new_password" class="form-control pe-5" required autocomplete="new-password">
        <span class="toggle-password" toggle="#new_password" style="position:absolute;top:10px;right:16px;cursor:pointer;">
            <i class="bx bx-hide" id="icon-new-password"></i>
        </span>
    </div>
</div>
<div class="row mb-3">
    <label class="col-sm-4 col-form-label text-end">Konfirmasi Password</label>
    <div class="col-sm-8 position-relative">
        <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control pe-5" required autocomplete="new-password">
        <span class="toggle-password" toggle="#new_password_confirmation" style="position:absolute;top:10px;right:16px;cursor:pointer;">
            <i class="bx bx-hide" id="icon-new-password-confirmation"></i>
        </span>
    </div>
</div>

            <div class="text-center">
                <button type="submit" class="btn btn-warning">
                    <i class="bx bx-key"></i> Ubah Password
                </button>
            </div>
        </form>
    </div>
</div>

            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.querySelectorAll('.toggle-password').forEach(function(el) {
        el.addEventListener('click', function() {
            let input = document.querySelector(this.getAttribute('toggle'));
            let icon = this.querySelector('i');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('bx-hide');
                icon.classList.add('bx-show');
            } else {
                input.type = "password";
                icon.classList.remove('bx-show');
                icon.classList.add('bx-hide');
            }
        });
    });
</script>
@endpush
