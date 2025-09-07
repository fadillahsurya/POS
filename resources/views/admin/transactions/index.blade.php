@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Transaksi Penjualan</h5>
                <a href="{{ route('admin.transactions.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus"></i> Tambah Transaksi
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>No Invoice</th>
                                <th>Tanggal</th>
                                <th>Customer</th>
                                <th>Alamat</th>
                                <th>Metode Bayar</th>
                                <th>Total</th>
                                <th>Status Order</th>
                                <th>Status Bayar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $i => $trx)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $trx->midtrans_order_id ?? $trx->nomor_invoice ?? '-' }}</td>
                                <td>{{ $trx->tanggal_order ?? $trx->tanggal }}</td>
                                <td>{{ $trx->customer->nama_customer ?? $trx->customer->name ?? '-' }}</td>
                                <td>{{ $trx->alamat_kirim ?? '-' }}</td>
                                <td>{{ $trx->payment->metode_bayar ?? '-' }}</td>
                                <td>Rp{{ number_format($trx->total_order ?? $trx->total,0,',','.') }}</td>
                                <td>
                                    @if($trx->status_order == 'selesai')
                                        <span class="badge bg-success">Selesai</span>
                                    @elseif($trx->status_order == 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($trx->status_order) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($trx->payment && $trx->payment->status_bayar == 'success')
                                        <span class="badge bg-success">Lunas</span>
                                    @elseif($trx->payment && $trx->payment->status_bayar == 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($trx->payment && $trx->payment->status_bayar == 'failed')
                                        <span class="badge bg-danger">Gagal</span>
                                    @else
                                        <span class="badge bg-secondary">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.transactions.show', $trx->id) }}" class="btn btn-info btn-sm">
                                        <i class="bx bx-detail"></i> Detail
                                    </a>
                                    <form action="{{ route('admin.transactions.destroy', $trx->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Hapus transaksi ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm">
                                            <i class="bx bx-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            @if($transactions->isEmpty())
                            <tr>
                                <td colspan="10" class="text-center text-muted">Belum ada transaksi penjualan.</td>
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
