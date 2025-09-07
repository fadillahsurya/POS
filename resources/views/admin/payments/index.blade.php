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
            <li class="breadcrumb-item active" aria-current="page">
                Pembayaran
            </li>
        </ol>
    </nav>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pembayaran</h5>
                <form method="GET" class="d-flex align-items-center">
                    <select name="status_bayar" class="form-select me-2" style="min-width:140px;">
                        <option value="">Status (All)</option>
                        <option value="pending" {{ request('status_bayar')=='pending'?'selected':'' }}>Pending</option>
                        <option value="success" {{ request('status_bayar')=='success'?'selected':'' }}>Success</option>
                        <option value="failed" {{ request('status_bayar')=='failed'?'selected':'' }}>Failed</option>
                        <option value="expired" {{ request('status_bayar')=='expired'?'selected':'' }}>Expired</option>
                    </select>
                    <select name="metode_bayar" class="form-select me-2" style="min-width:120px;">
                        <option value="">Metode (All)</option>
                        <option value="midtrans" {{ request('metode_bayar')=='midtrans'?'selected':'' }}>Midtrans</option>
                        <option value="tunai" {{ request('metode_bayar')=='tunai'?'selected':'' }}>Tunai</option>
                        <option value="qris" {{ request('metode_bayar')=='qris'?'selected':'' }}>QRIS</option>
                    </select>
                    <button class="btn btn-primary btn-sm"><i class="bx bx-filter"></i> Filter</button>
                </form>
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
                                <th>#</th>
                                <th>No. Order</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Jumlah Bayar</th>
                                <th>Waktu Bayar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $pay)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $pay->order_id }}</td>
                                <td>{{ ucfirst($pay->metode_bayar) }}</td>
                                <td>
                                    @if($pay->status_bayar == 'success')
                                    <span class="badge bg-label-success">Lunas</span>
                                    @elseif($pay->status_bayar == 'pending')
                                    <span class="badge bg-label-warning">Pending</span>
                                    @elseif($pay->status_bayar == 'expired')
                                    <span class="badge bg-label-secondary">Expired</span>
                                    @elseif($pay->status_bayar == 'failed')
                                    <span class="badge bg-label-danger">Failed</span>
                                    @else
                                    <span class="badge bg-label-info">{{ ucfirst($pay->status_bayar) }}</span>
                                    @endif
                                </td>
                                <td>Rp{{ number_format($pay->jumlah_bayar, 0, ',', '.') }}</td>
                                <td>{{ $pay->waktu_bayar }}</td>
                                <td>
                                    @if($pay->status_bayar != 'success')
                                    <form method="POST" action="{{ route('payments.setLunas', $pay->id) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-success btn-sm" onclick="return confirm('Tandai pembayaran ini sudah lunas?')">
                                            <i class="bx bx-check"></i> Set Lunas
                                        </button>
                                    </form>
                                    @else
                                    <span class="text-success fw-semibold"><i class="bx bx-check-circle"></i> Lunas</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Tidak ada data pembayaran</td>
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
