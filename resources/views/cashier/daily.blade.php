@extends('layouts.kasir')

@section('kasir_content')
<h5 class="mb-3">Laporan Harian ({{ \Carbon\Carbon::parse($today)->format('d M Y') }})</h5>

<div class="card">
    <div class="card-body">
        <p class="mb-2">Total Omzet POS: <strong>Rp {{ number_format($total,0,',','.') }}</strong></p>

        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $o)
                    <tr>
                        <td class="font-monospace">{{ $o->midtrans_order_id }}</td>
                        <td>{{ \Carbon\Carbon::parse($o->tanggal_order)->format('d M Y') }}</td>
                        <td>{{ ucfirst($o->status_order) }}</td>
                        <td class="text-end">Rp {{ number_format($o->total_order,0,',','.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Belum ada transaksi hari ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection