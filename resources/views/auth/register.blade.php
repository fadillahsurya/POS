@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow border-0 mt-5">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="bx bx-user-plus"></i> Register Akun
                </div>
                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" id="registerForm">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Daftar Sebagai</label>
                            <select name="role" class="form-select" id="roleSelect" required>
                                <option value="">-- Pilih --</option>
                                <option value="customer" {{ old('role') == 'customer' ? 'selected' : '' }}>Customer</option>
                                <option value="mitra" {{ old('role') == 'mitra' ? 'selected' : '' }}>Mitra</option>
                                <option value="supplier" {{ old('role') == 'supplier' ? 'selected' : '' }}>Supplier</option>
                            </select>
                        </div>

                        <!-- Field khusus Customer -->
                        <div id="customer-fields" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">No HP</label>
                                <input type="text" name="customer_no_hp" value="{{ old('customer_no_hp') }}"
                                    class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea name="customer_alamat" class="form-control">{{ old('customer_alamat') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Foto Profil (opsional)</label>
                                <input type="file" name="foto" class="form-control" accept="image/*">
                            </div>
                        </div>

                        <!-- Field khusus Mitra -->
                        <div id="mitra-fields" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Nama Usaha</label>
                                <input type="text" name="nama_usaha" value="{{ old('nama_usaha') }}"
                                    class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">No HP</label>
                                <input type="text" name="mitra_no_hp" value="{{ old('mitra_no_hp') }}"
                                    class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea name="mitra_alamat" class="form-control">{{ old('mitra_alamat') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Foto Profil (opsional)</label>
                                <input type="file" name="foto" class="form-control" accept="image/*">
                            </div>
                        </div>

                        <!-- Field khusus Supplier -->
                        <div id="supplier-fields" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Perusahaan</label>
                                <input type="text" name="perusahaan" value="{{ old('perusahaan') }}"
                                    class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">No HP</label>
                                <input type="text" name="supplier_no_hp" value="{{ old('supplier_no_hp') }}"
                                    class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea name="supplier_alamat" class="form-control">{{ old('supplier_alamat') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Foto Profil (opsional)</label>
                                <input type="file" name="foto" class="form-control" accept="image/*">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-semibold">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showRoleFields(role) {
            document.getElementById('customer-fields').style.display = (role === 'customer') ? '' : 'none';
            document.getElementById('mitra-fields').style.display = (role === 'mitra') ? '' : 'none';
            document.getElementById('supplier-fields').style.display = (role === 'supplier') ? '' : 'none';

            // Required field customer
            var customerFields = document.querySelectorAll('#customer-fields input, #customer-fields textarea');
            customerFields.forEach(function(field) {
                if (field.type !== 'file') {
                    field.required = (role === 'customer');
                }
            });

            // Required field mitra
            var mitraFields = document.querySelectorAll('#mitra-fields input, #mitra-fields textarea');
            mitraFields.forEach(function(field) {
                if (field.type !== 'file') {
                    field.required = (role === 'mitra');
                }
            });

            // Required field supplier
            var supplierFields = document.querySelectorAll('#supplier-fields input, #supplier-fields textarea');
            supplierFields.forEach(function(field) {
                if (field.type !== 'file') {
                    field.required = (role === 'supplier');
                }
            });
        }
        document.addEventListener('DOMContentLoaded', function() {
            var select = document.getElementById('roleSelect');
            showRoleFields(select.value);
            select.addEventListener('change', function() {
                showRoleFields(this.value);
            });
        });
    </script>
@endsection
