@extends('layouts.kasir')

@section('kasir_content')
<h5 class="mb-3">Riwayat Transaksi POS</h5>

@if(isset($orders) && $orders->count())
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th style="width: 180px;">Order ID</th>
                        <th style="width: 160px;">Tanggal</th>
                        <th style="width: 120px;">Status</th>
                        <th class="text-end" style="width: 160px;">Total</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $o)
                    <tr>
                        <td class="font-monospace">{{ $o->midtrans_order_id }}</td>
                        {{-- UBAH: gunakan created_at agar urut by datetime --}}
                        <td>{{ $o->created_at->format('d M Y') }}</td>
                        <td>
                            <span class="badge bg-{{ $o->status_order === 'selesai' ? 'success' : ($o->status_order === 'lunas' ? 'primary' : 'secondary') }}">
                                {{ ucfirst($o->status_order) }}
                            </span>
                        </td>
                        <td class="text-end">Rp {{ number_format($o->total_order, 0, ',', '.') }}</td>
                        <td>
                            <button 
                                class="btn btn-sm btn-info btn-detail" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalDetail"
                                data-order='@json($o->load("items.product"))'>
                                🔍 Detail
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-2">
            {{ $orders->withQueryString()->links() }}
        </div>
    </div>
</div>
@else
<div class="alert alert-light border">Belum ada transaksi POS.</div>
@endif


<!-- MODAL DETAIL -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Order ID:</strong> <span id="detailOrderId"></span></p>
                <p><strong>Tanggal:</strong> <span id="detailTanggal"></span></p>
                <p><strong>Status:</strong> <span id="detailStatus"></span></p>
                <p><strong>Total:</strong> <span id="detailTotal"></span></p>
                
                <hr>
                <h6>🛒 Daftar Produk</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" id="detailItemsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const detailOrderId = document.getElementById("detailOrderId");
    const detailTanggal = document.getElementById("detailTanggal");
    const detailStatus = document.getElementById("detailStatus");
    const detailTotal = document.getElementById("detailTotal");
    const detailItemsTable = document.querySelector("#detailItemsTable tbody");

    document.querySelectorAll(".btn-detail").forEach(btn => {
        btn.addEventListener("click", function () {
            let order = JSON.parse(this.dataset.order);

            // isi informasi dasar
            detailOrderId.textContent = order.midtrans_order_id;
            // UBAH: tampilkan created_at agar ada jam-menit
            detailTanggal.textContent = new Date(order.created_at).toLocaleString("id-ID");
            detailStatus.textContent = order.status_order;
            detailTotal.textContent = "Rp " + (order.total_order).toLocaleString("id-ID");

            // isi tabel barang
            detailItemsTable.innerHTML = "";
            if (order.items && order.items.length > 0) {
                order.items.forEach(item => {
                    let tr = document.createElement("tr");
                    tr.innerHTML = `
                        <td>${item.product?.nama_produk ?? "Produk terhapus"}</td>
                        <td class="text-center">${item.qty}</td>
                        <td class="text-end">Rp ${(item.harga_jual).toLocaleString("id-ID")}</td>
                        <td class="text-end">Rp ${(item.subtotal).toLocaleString("id-ID")}</td>
                    `;
                    detailItemsTable.appendChild(tr);
                });
            } else {
                detailItemsTable.innerHTML = `<tr><td colspan="4" class="text-center text-muted">Tidak ada detail barang.</td></tr>`;
            }
        });
    });
});
</script>
@endpush
