@extends('layouts.kasir')

@section('kasir_content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="text-muted">Transaksi Hari Ini</div>
                <div class="h3 mb-0">{{ $totalTransaksi ?? 0 }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="text-muted">Omzet Hari Ini</div>
                <div class="h3 mb-0">Rp {{ number_format($omzet ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="text-muted mb-2">Stok Hampir Habis</div>
                <ul class="mb-0">
                    @forelse(($produkHampirHabis ?? []) as $p)
                    <li>{{ $p->nama_produk ?? $p->name }} — stok {{ $p->stok }}</li>
                    @empty
                    <li>Tidak ada yang menipis 🎉</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection