@extends('layouts.admin')

@section('content')
<h2>Stok & Mutasi Barang</h2>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Produk</th>
            <th>Gudang</th>
            <th>Stok Masuk</th>
            <th>Stok Keluar</th>
            <th>Sisa Stok</th>
        </tr>
    </thead>
    <tbody>
        @foreach($stocks as $i => $stock)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $stock->product->nama_produk ?? '-' }}</td>
            <td>{{ $stock->warehouse->nama_gudang ?? '-' }}</td>
            <td>{{ $stock->stok_masuk }}</td>
            <td>{{ $stock->stok_keluar }}</td>
            <td>{{ $stock->sisa_stok }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
