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
            <li class="breadcrumb-item">
                <a href="{{ route('orders.index') }}">Transaksi Penjualan</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Transaksi Baru
            </li>
        </ol>
    </nav>
</div>
<div class="row justify-content-center">
    <div class="col-lg-7 col-md-10">
        <div class="card shadow">
            <div class="card-header pb-2">
                <h5 class="mb-0"><i class="bx bx-plus-circle me-2"></i>Transaksi Penjualan Baru (Kasir)</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('orders.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Pilih Customer / Mitra</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ ucfirst($c->role) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipe Order</label>
                        <select name="tipe_order" class="form-select" required>
                            <option value="offline">Offline (Kasir)</option>
                            <option value="online">Online (Customer)</option>
                            <option value="mitra">Order Mitra</option>
                        </select>
                    </div>
                    <div id="produk-wrapper">
                        <div class="row g-2 mb-2 produk-row">
                            <div class="col-md-6">
                                <select name="produk_id[]" class="form-select" required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama_produk }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="number" name="qty[]" min="1" class="form-control" value="1" required placeholder="Qty">
                            </div>
                            <div class="col-md-2 d-flex align-items-center">
                                <button type="button" class="btn btn-icon btn-danger btn-remove-produk d-none" onclick="hapusProduk(this)">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary mb-3" onclick="tambahProduk()">
                        <i class="bx bx-plus"></i> Tambah Produk
                    </button>
                    <div>
                        <button class="btn btn-success"><i class="bx bx-save"></i> Simpan Transaksi</button>
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary ms-2"><i class="bx bx-arrow-back"></i> Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function tambahProduk() {
        var html = `
    <div class="row g-2 mb-2 produk-row">
        <div class="col-md-6">
            <select name="produk_id[]" class="form-select" required>
                <option value="">-- Pilih Produk --</option>
                @foreach($products as $p)
                <option value="{{ $p->id }}">{{ $p->nama_produk }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <input type="number" name="qty[]" min="1" class="form-control" value="1" required placeholder="Qty">
        </div>
        <div class="col-md-2 d-flex align-items-center">
            <button type="button" class="btn btn-icon btn-danger btn-remove-produk" onclick="hapusProduk(this)">
                <i class="bx bx-trash"></i>
            </button>
        </div>
    </div>
    `;
        document.getElementById('produk-wrapper').insertAdjacentHTML('beforeend', html);
        toggleRemoveBtn();
    }

    function hapusProduk(btn) {
        btn.closest('.produk-row').remove();
        toggleRemoveBtn();
    }

    function toggleRemoveBtn() {
        let produkRows = document.querySelectorAll('.produk-row');
        produkRows.forEach((row, idx) => {
            let removeBtn = row.querySelector('.btn-remove-produk');
            if (produkRows.length > 1) {
                removeBtn.classList.remove('d-none');
            } else {
                removeBtn.classList.add('d-none');
            }
        });
    }
    window.onload = toggleRemoveBtn;
</script>
@endsection
