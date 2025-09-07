@extends('layouts.app')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <div class="card shadow-sm border-0 mb-4">
        <div class="row g-0">
          {{-- Kolom Gambar/Slider --}}
          <div class="col-md-5 border-end">
            <div class="card-body">
              @if($produk->images && count($produk->images))
              <div id="productImageCarousel" class="carousel slide mb-3" data-bs-ride="carousel">
                <div class="carousel-inner rounded-3">
                  @foreach($produk->images as $i => $img)
                  <div class="carousel-item {{ $i == 0 ? 'active' : '' }}">
                    <img src="{{ asset('storage/'.$img->file_path) }}"
                         class="d-block w-100"
                         alt="Gambar produk"
                         style="max-height:340px;object-fit:contain;">
                  </div>
                  @endforeach
                </div>
                @if(count($produk->images) > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#productImageCarousel" data-bs-slide="prev">
                  <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#productImageCarousel" data-bs-slide="next">
                  <span class="carousel-control-next-icon"></span>
                </button>
                <div class="carousel-indicators">
                  @foreach($produk->images as $i => $img)
                  <button type="button"
                          data-bs-target="#productImageCarousel"
                          data-bs-slide-to="{{ $i }}"
                          class="{{ $i == 0 ? 'active' : '' }}"
                          aria-current="{{ $i == 0 ? 'true' : 'false' }}"
                          aria-label="Slide {{ $i+1 }}"></button>
                  @endforeach
                </div>
                @endif
              </div>
              @else
              <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height:300px;">
                <i class="bx bx-image-alt text-secondary" style="font-size:3rem;"></i>
              </div>
              @endif
            </div>
          </div>
          {{-- Kolom Detail Produk --}}
          <div class="col-md-7">
            <div class="card-body">
              <span class="badge bg-label-primary mb-2">{{ $produk->category->nama_kategori ?? '-' }}</span>
              <h3 class="fw-semibold mb-2">{{ $produk->nama_produk }}</h3>
              <h4 class="text-primary fw-bold mb-2">Rp{{ number_format($produk->harga_jual, 0, ',', '.') }}</h4>
              <div class="mb-2">
                <span class="badge {{ $produk->stok < 1 ? 'bg-label-danger' : 'bg-label-success' }} px-3 py-2">
                  {{ $produk->stok < 1 ? 'Stok Habis' : 'Stok: '.$produk->stok }}
                </span>
              </div>
              <p class="mb-4">{!! nl2br(e($produk->deskripsi ?? 'Tidak ada deskripsi.')) !!}</p>
<div class="d-flex flex-wrap gap-2">
    {{-- Tombol Keranjang (AJAX) --}}
    <form id="form-keranjang" action="{{ route('home.keranjang.tambah', $produk->id) }}" method="POST" class="d-inline">
      @csrf
      <button id="btnKeranjang" class="btn btn-outline-primary"
        type="submit" {{ $produk->stok < 1 ? 'disabled' : '' }}>
        <i class="bx bx-cart"></i>
        Keranjang
      </button>
    </form>
    {{-- Tombol Beli Sekarang (langsung redirect ke keranjang) --}}
    <form action="{{ route('home.keranjang.tambah', $produk->id) }}" method="POST" class="d-inline">
      @csrf
      <button class="btn btn-success"
        type="submit" {{ $produk->stok < 1 ? 'disabled' : '' }}>
        <i class="bx bx-basket"></i>
        Beli Sekarang
      </button>
    </form>
    <a href="{{ route('home.katalog') }}" class="btn btn-outline-secondary ms-2">← Kembali ke Katalog</a>
</div>

              <div class="alert-dynamic mt-3"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  .carousel-item img { max-height:340px; object-fit:contain; }
  .carousel-inner { background: #f7f7fa; }
  .bg-label-primary { background: #e7e7ff !important; color: #5f61e6 !important; }
  .bg-label-danger { background: #ffeaea !important; color: #e76a6a !important; }
  .bg-label-success { background: #eafff3 !important; color: #22c58b !important; }
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('form-keranjang').addEventListener('submit', function(e) {
        e.preventDefault();

        let btn = document.getElementById('btnKeranjang');
        btn.disabled = true;
        let formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            if(res.success){
                showAlert('Produk berhasil masuk ke keranjang!', 'success');
                // Update badge keranjang jika ada
                if(typeof updateKeranjangBadge === 'function' && res.qty !== undefined) updateKeranjangBadge(res.qty);
                // Atau update manual badge keranjang:
                if(res.qty !== undefined) {
                    let badge = document.querySelector('.navbar .bx-cart ~ .badge, .navbar .bx-cart + .badge');
                    if(badge) badge.textContent = res.qty;
                }
            } else {
                showAlert('Gagal menambah ke keranjang.', 'danger');
            }
        })
        .catch(() => {
            btn.disabled = false;
            showAlert('Terjadi kesalahan. Silakan coba lagi.', 'danger');
        });
    });

    function showAlert(msg, type) {
        let wrap = document.querySelector('.alert-dynamic');
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.className = 'alert-dynamic position-fixed top-0 end-0 m-3';
            wrap.style.zIndex = 9999;
            document.body.appendChild(wrap);
        }
        wrap.innerHTML = `<div class="alert alert-${type} shadow mb-0">${msg}</div>`;
        setTimeout(() => { wrap.innerHTML = '' }, 1800);
    }
</script>
@endpush
