@extends('layouts.kasir')

@section('kasir_content')
    <h5 class="mb-3">Transaksi POS</h5>

    {{-- Flash message --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        {{-- Keranjang --}}
        <div class="col-lg-7 col-md-12 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Keranjang</h6>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalProduk">
                            + Tambah Produk
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th style="width: 90px;">Qty</th>
                                    <th class="text-end" style="width: 120px;">Total</th>
                                    <th style="width: 80px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($cart ?? []) as $row)
                                    @php
                                        $unit = ($row['harga_jual'] ?? $row['price'] ?? 0);
                                        $line = ($row['qty'] * $unit);
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $row['nama'] ?? $row['name'] }}</div>
                                            @if(!empty($row['variant_label']))
                                                <div class="small text-primary">Varian: {{ $row['variant_label'] }}</div>
                                            @endif
                                            <div class="small text-muted">
                                                Rp {{ number_format($unit, 0, ',', '.') }}
                                            </div>
                                        </td>
                                        <td>
                                            <form method="post" action="{{ route('kasir.pos.update') }}" class="d-flex gap-1 align-items-center">
                                                @csrf
                                                <input type="hidden" name="key" value="{{ $row['key'] ?? ($row['product_id'].':'.($row['flavor_id'] ?? 0)) }}">
                                                
                                                <input type="number" 
                                                    name="qty" 
                                                    min="0" 
                                                    value="{{ $row['qty'] }}" 
                                                    class="form-control form-control-sm text-center fw-bold" 
                                                    style="width:65px; font-size:14px;">

                                                <button class="btn btn-sm btn-outline-secondary">⟳</button>
                                            </form>
                                        </td>

                                        <td class="text-end">Rp {{ number_format($line, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <form method="post" action="{{ route('kasir.pos.remove') }}">
                                                @csrf
                                                <input type="hidden" name="key" value="{{ $row['key'] ?? ($row['product_id'].':'.($row['flavor_id'] ?? 0)) }}">
                                                <button class="btn btn-sm btn-outline-danger">✕</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            Keranjang kosong.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ringkasan & Checkout --}}
        <div class="col-lg-5 col-md-12 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <form method="post" action="{{ route('kasir.pos.checkout') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label fw-semibold">Subtotal</label>
        <input type="text" id="subtotalInput"
               class="form-control"
               value="{{ $subtotal ?? 0 }}"
               readonly>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">Diskon</label>
        <input type="number" name="discount" id="discountInput"
               value="0" min="0" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">Dibayar</label>
        <input type="number" name="paid" id="paidInput"
               min="0" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">Kembalian</label>
        <input type="text" id="kembalianInput" class="form-control" readonly>
    </div>
    <button class="btn btn-primary w-100 mb-2">💳 Bayar & Simpan</button>
</form>

                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PRODUK --}}
    <div class="modal fade" id="modalProduk" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Daftar Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" id="searchProduk" class="form-control" placeholder="Cari produk...">
                    </div>

                    <div class="table-responsive">
                        <table id="tableProduk" class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama</th>
                                    <th style="width: 120px;">Harga</th>
                                    <th style="width: 100px;">Stok</th>
                                    <th style="width: 280px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products ?? [] as $p)
                                    @php
                                        $hasFlavors = method_exists($p, 'flavors') ? $p->flavors->count() > 0 : false;
                                        $hargaBase = (float)($p->harga_jual ?? $p->price ?? 0);
                                    @endphp
                                    <tr>
                                        <td class="nama-produk">{{ $p->nama_produk ?? $p->name }}</td>
                                        <td>Rp {{ number_format($hargaBase, 0, ',', '.') }}</td>
                                        <td>{{ $p->stok ?? $p->stock }}</td>
                                        <td>
                                            <form method="post" action="{{ route('kasir.pos.add') }}" class="d-flex gap-2 align-items-center flex-wrap">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $p->id }}">

                                                @if($hasFlavors)
                                                    <select name="flavor_id" class="form-select form-select-sm" required style="max-width: 160px;">
                                                        <option value="" disabled selected>Pilih varian…</option>
                                                        @foreach($p->flavors as $fv)
                                                            <option value="{{ $fv->id }}" {{ (int)$fv->stok < 1 ? 'disabled' : '' }}>
                                                                {{ $fv->nama_rasa }}
                                                                @if(($fv->harga_tambahan ?? 0) > 0)
                                                                    (+Rp{{ number_format($fv->harga_tambahan, 0, ',', '.') }})
                                                                @endif
                                                                @if((int)$fv->stok < 1) - Habis @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @endif

                                                <input type="number" min="1" name="qty" value="1" class="form-control form-control-sm" style="width:70px">
                                                <button class="btn btn-sm btn-success" {{ ($p->stok ?? 0) < 1 ? 'disabled' : '' }}>
                                                    Tambah
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Belum ada produk ditampilkan.</td>
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

@push('scripts')
<script>
    document.getElementById("searchProduk").addEventListener("keyup", function() {
        let input = this.value.toLowerCase();
        let rows = document.querySelectorAll("#tableProduk tbody tr");

        rows.forEach(function(row) {
            let namaProduk = row.querySelector(".nama-produk")?.textContent.toLowerCase();
            row.style.display = (namaProduk && namaProduk.indexOf(input) > -1) ? "" : "none";
        });
    });
    document.addEventListener("DOMContentLoaded", function() {
    const subtotalEl = document.getElementById("subtotalInput");
    const discountEl = document.getElementById("discountInput");
    const paidEl = document.getElementById("paidInput");
    const kembalianEl = document.getElementById("kembalianInput");

    function hitungKembalian() {
        let subtotal = parseInt(subtotalEl.value) || 0;
        let discount = parseInt(discountEl.value) || 0;
        let paid = parseInt(paidEl.value) || 0;

        let total = subtotal - discount;
        let kembalian = paid - total;

        kembalianEl.value = (kembalian >= 0)
            ? "Rp " + kembalian.toLocaleString("id-ID")
            : "Belum cukup";
    }

    discountEl.addEventListener("input", hitungKembalian);
    paidEl.addEventListener("input", hitungKembalian);
});
</script>
@endpush
