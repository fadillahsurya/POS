@extends('layouts.app') 

@section('head')
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
@endsection

@section('content')
    {{-- ALERT SERVER --}}
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-primary d-flex align-items-center">
                    <i class="bx bx-shopping-bag me-2" style="font-size:1.4rem"></i>
                    <span class="fw-semibold text-white">Pesanan Saya</span>
                </div>
                <div class="card-body table-responsive">
                    @if ($orders->isEmpty())
                        <div class="alert alert-info text-center mb-0">
                            <i class="bx bx-info-circle"></i> Belum ada pesanan.
                        </div>
                    @else
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Alamat Kirim</th>
                                    <th style="width:120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $o)
                                    <tr>
                                        <td class="fw-semibold">{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="small text-muted">
                                                {{ \Carbon\Carbon::parse($o->tanggal_order)->format('d/m/Y H:i') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">
                                                Rp{{ number_format($o->total_order, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill bg-{{ 
                                                $o->status_order == 'lunas' ? 'success' : 
                                                ($o->status_order == 'pending' ? 'warning text-dark' : 'secondary') 
                                            }}">
                                                <i class="bx bx-{{ 
                                                    $o->status_order == 'lunas' ? 'check-circle' : 
                                                    ($o->status_order == 'pending' ? 'time-five' : 'info-circle') 
                                                }}"></i>
                                                {{ ucfirst($o->status_order) }}
                                            </span>
                                        </td>
                                        <td>{{ $o->alamat_kirim }}</td>
<td>
    <a href="{{ route('home.myorders.detail', $o->id) }}"
        class="btn btn-sm btn-outline-primary">
        <i class="bx bx-show"></i> Detail
    </a>
    @if ($o->status_order == 'pending')
        <form action="{{ route('home.myorders.lanjutkan_pembayaran', $o->id) }}"
            method="POST" class="d-inline mt-1">
            @csrf
            <button type="submit" class="btn btn-sm btn-success">
                <i class="bx bx-credit-card"></i> Lanjutkan Bayar
            </button>
        </form>
        <form action="{{ route('home.myorders.cancel', $o->id) }}"
            method="POST" class="d-inline mt-1"
            onsubmit="return confirm('Batalkan pesanan ini?');">
            @csrf
            <button type="submit" class="btn btn-sm btn-danger">
                <i class="bx bx-x"></i> Cancel
            </button>
        </form>
    @endif
</td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
