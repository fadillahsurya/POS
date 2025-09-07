@extends('layouts.app')

@section('content')
<div class="container my-4">
    <h3 class="mb-4"><i class="bx bx-cart"></i> Keranjang Belanja</h3>

    @if ($keranjang->isEmpty())
        <div class="alert alert-info"><i class="bx bx-info-circle"></i> Keranjang masih kosong.</div>
        <a href="{{ route('home.katalog') }}" class="btn btn-primary mt-3">
            <i class="bx bx-plus"></i> Tambah Produk
        </a>
    @else
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = 0; @endphp
                    @foreach($keranjang as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item['nama'] }}</td>
                            <td>Rp{{ number_format($item['harga'],0,',','.') }}</td>
                            <td>
                                <form action="{{ route('home.keranjang.update', $item['id']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="number" name="qty" min="1" max="999" value="{{ $item['qty'] }}" style="width:65px;" class="form-control d-inline" required>
                                    <button class="btn btn-sm btn-primary ms-1">Update</button>
                                </form>
                            </td>
                            <td>Rp{{ number_format($item['qty'] * $item['harga'],0,',','.') }}</td>
                            <td>
                                <form action="{{ route('home.keranjang.hapus', $item['id']) }}" method="POST" class="d-inline-block">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus item ini dari keranjang?')">
                                        <i class="bx bx-trash"></i> Cancel
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @php
                            $total += $item['qty'] * $item['harga'];
                        @endphp
                    @endforeach

                    <tr>
                        <th colspan="4" class="text-end">Total</th>
                        <th colspan="2" class="text-primary">Rp{{ number_format($total, 0, ',', '.') }}</th>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Tombol tambah produk --}}
        <div class="mt-3 d-flex justify-content-between">
            <a href="{{ route('home.katalog') }}" class="btn btn-primary">
                <i class="bx bx-plus"></i> Tambah Produk
            </a>

            {{-- Tombol checkout dan input alamat --}}
            <form action="{{ route('home.checkout') }}" method="POST" class="d-flex align-items-center">
                @csrf
                <label for="alamat" class="me-2">Alamat Kirim</label>
                <input type="text" class="form-control me-2" id="alamat" name="alamat" placeholder="Boleh kosong, atau wajib ada kata 'Tegal' jika diisi" value="{{ old('alamat') }}" style="width:300px;">
                <button type="submit" class="btn btn-success">
                    <i class="bx bx-shopping-bag"></i> Checkout Sekarang
                </button>
            </form>
        </div>
    @endif
</div>
@endsection
