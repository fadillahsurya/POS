@extends('layouts.admin')
@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard.admin') }}"><i class="bx bx-home"></i> Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="#">Laporan</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Produk Terlaris
            </li>
        </ol>
    </nav>
</div>
<div class="row mb-4">
    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0"><i class="bx bx-star"></i> Produk Terlaris</h5>
            </div>
            <div class="card-body pt-3">
                <form method="GET" class="row g-2 align-items-end mb-4">
                    <div class="col-md-4">
                        <label for="tgl_awal" class="form-label mb-0">Tanggal Awal</label>
                        <input type="date" name="tgl_awal" id="tgl_awal" class="form-control" value="{{ $tglAwal }}">
                    </div>
                    <div class="col-md-4">
                        <label for="tgl_akhir" class="form-label mb-0">Tanggal Akhir</label>
                        <input type="date" name="tgl_akhir" id="tgl_akhir" class="form-control" value="{{ $tglAkhir }}">
                    </div>
                    <div class="col-md-4 d-flex gap-2 mt-3 mt-md-0">
                        <button class="btn btn-primary flex-fill">
                            <i class="bx bx-search"></i> Filter
                        </button>
                        <a href="{{ route('laporan.produk_terlaris_pdf', ['tgl_awal'=>$tglAwal,'tgl_akhir'=>$tglAkhir]) }}"
                            class="btn btn-danger flex-fill" target="_blank">
                            <i class="bx bxs-file-pdf"></i> PDF
                        </a>
                    </div>
                </form>

                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th>Total Terjual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($terlaris as $item)
                            <tr>
                                <td>{{ $item->product->nama_produk ?? '-' }}</td>
                                <td class="fw-bold">{{ $item->total_terjual }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">Tidak ada data penjualan.</td>
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
