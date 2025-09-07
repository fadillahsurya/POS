{{-- resources/views/home/katalog.blade.php --}}

@extends('layouts.app')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')

{{-- HERO SECTION --}}
<div class="row align-items-center my-5">
  <div class="col-lg-6 text-center text-lg-start mb-4 mb-lg-0">
    <h1 class="display-4 fw-bold mb-3">
      Temukan Jajanan Terbaik di <span class="text-primary">Shop</span>
    </h1>
    <p class="lead mb-4">
      Nikmati belanja jajanan favorit dengan mudah dan aman. Temukan aneka camilan lezat, jajanan tradisional, hingga snack kekinian semuanya ada di sini, lengkap dengan promo spesial setiap hari!
    </p>
    @auth
      <div class="alert alert-primary mb-4 d-inline-block" style="font-size:1.1rem;">
        <i class="bx bx-user"></i>
        Selamat datang, <strong>{{ auth()->user()->name }}</strong>!
      </div>
    @else
      <a href="{{ route('register') }}" class="btn btn-lg btn-primary px-4 me-2">
        <i class="bx bx-user-plus"></i> Daftar Gratis
      </a>
    @endauth
  </div>
  <div class="col-lg-6 text-center">
    <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}"
         class="img-fluid rounded" alt="POS Katalog" style="max-height: 340px;">
  </div>
</div>

{{-- FILTER KATEGORI --}}
<div class="card shadow-sm border-0 p-3 mb-4">
  <form method="GET" class="row g-2 align-items-center">
    <div class="col-12 col-md-4">
      <select name="kategori" class="form-select" onchange="this.form.submit()">
        <option value="">Semua Kategori</option>
        @foreach($categories as $cat)
          <option value="{{ $cat->id }}" {{ ($kategori == $cat->id) ? 'selected' : '' }}>
            {{ $cat->nama_kategori }}
          </option>
        @endforeach
      </select>
    </div>
  </form>
</div>

{{-- ALERT SERVER --}}
@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif

{{-- PRODUK GRID --}}
<div class="mb-3 mt-4 d-flex align-items-center justify-content-between">
  <h5 class="mb-0 fw-bold"><i class="bx bx-package text-primary"></i> Katalog Produk</h5>
  <span class="text-muted small">{{ $products->total() }} produk ditemukan</span>
</div>

<div id="produk" class="row g-4 mb-5">
  @forelse ($products as $p)
    {{-- Sembunyikan produk supplier yang belum approve --}}
    @continue($p->supplier_id && (int)($p->is_approved ?? 0) !== 1)

    <div class="col-6 col-md-4 col-lg-3">
      <div class="card h-100 shadow-sm border-0 produk-card position-relative" style="transition:transform .15s;">

        {{-- LABEL STOK --}}
        @if($p->stok < 1)
          <span class="badge bg-danger position-absolute top-0 end-0 m-2 shadow">Stok Habis</span>
        @elseif($p->terlaris ?? false)
          <span class="badge bg-success position-absolute top-0 end-0 m-2 shadow">Terlaris</span>
        @endif

        {{-- GAMBAR PRODUK (aman dengan first()) --}}
        @php $firstImg = optional($p->images->first())->file_path; @endphp
        @if($firstImg)
          <img src="{{ asset('storage/'.$firstImg) }}"
               class="card-img-top" alt="{{ $p->nama_produk }}"
               style="height:160px;object-fit:cover;">
        @else
          <div class="d-flex align-items-center justify-content-center bg-light" style="height:160px;">
            <i class="bx bx-image-alt text-secondary" style="font-size:2.2rem;"></i>
          </div>
        @endif

        <div class="card-body d-flex flex-column pb-2">
          <div class="mb-1 text-muted small">{{ $p->category->nama_kategori ?? '-' }}</div>
          <h6 class="fw-bold mb-1">
            <a href="{{ route('home.produk.detail', $p->id) }}" class="text-dark text-decoration-none">
              {{ $p->nama_produk }}
            </a>
          </h6>

          {{-- VARIAN RASA --}}
          @php
            $hasFlavors = isset($p->flavors) ? $p->flavors->count() > 0 : (method_exists($p, 'flavors') ? $p->flavors()->count() > 0 : false);
          @endphp

          @if($hasFlavors)
            <div class="mb-2">
              <select
                id="flavor-select-{{ $p->id }}"
                class="form-select form-select-sm flavor-select"
                data-base-price="{{ (float)$p->harga_jual }}"
                data-price-id="price-{{ $p->id }}"
                data-stock-id="stock-{{ $p->id }}"
                data-beli-hidden-id="beli-flavor-{{ $p->id }}"
                data-cart-hidden-id="cart-flavor-{{ $p->id }}"
                data-beli-btn-id="btn-beli-{{ $p->id }}"
                data-cart-btn-id="btn-cart-{{ $p->id }}"
                data-hint-id="hint-{{ $p->id }}"
              >
                <option value="" selected disabled>Pilih varian rasa…</option>
                @foreach(($p->flavors ?? $p->flavors()->get()) as $fv)
                  <option value="{{ $fv->id }}"
                          data-add="{{ (float)($fv->harga_tambahan ?? 0) }}"
                          data-stok="{{ (int)$fv->stok }}"
                          {{ (int)$fv->stok < 1 ? 'disabled' : '' }}>
                    {{ $fv->nama_rasa }}
                    @if(($fv->harga_tambahan ?? 0) > 0)
                      ( +Rp{{ number_format($fv->harga_tambahan,0,',','.') }} )
                    @endif
                    @if((int)$fv->stok < 1) - Habis @endif
                  </option>
                @endforeach
              </select>
              <small id="hint-{{ $p->id }}" class="text-danger d-none">
                Pilih varian rasa dulu sebelum beli.
              </small>
            </div>
          @endif

          {{-- HARGA --}}
          <div class="mb-1 text-primary fw-semibold" style="font-size:1.1rem;" id="price-{{ $p->id }}">
            @if($hasFlavors)
              <span class="text-muted">Pilih varian untuk melihat harga</span>
            @else
              Rp{{ number_format($p->harga_jual,0,',','.') }}
            @endif
          </div>

          {{-- STOK --}}
          <div class="small mb-2 text-muted" id="stock-{{ $p->id }}">
            @if($hasFlavors)
              Stok: -
            @else
              Stok: {{ (int)$p->stok }}
            @endif
          </div>

          <div class="row g-2 mt-auto">
            <div class="col-6">
              {{-- BELI LANGSUNG --}}
              <form
                action="{{ route('home.beli.sekarang', $p->id) }}"
                method="POST"
                class="form-beli-sekarang"
                @if($hasFlavors) data-flavor-select="flavor-select-{{ $p->id }}" data-hint-id="hint-{{ $p->id }}" @endif
              >
                @csrf
                @if($hasFlavors)
                  <input type="hidden" name="flavor_id" id="beli-flavor-{{ $p->id }}">
                @endif
                <button id="btn-beli-{{ $p->id }}" class="btn btn-success btn-sm w-100"
                        {{ (!$hasFlavors && $p->stok < 1) ? 'disabled' : '' }}>
                  <i class="bx bx-basket"></i> Beli
                </button>
              </form>
            </div>
            <div class="col-6">
              {{-- MASUKKAN KERANJANG AJAX --}}
              <form
                action="{{ route('home.keranjang.tambah', $p->id) }}"
                method="POST"
                class="form-tambah-keranjang"
                @if($hasFlavors) data-flavor-select="flavor-select-{{ $p->id }}" data-hint-id="hint-{{ $p->id }}" @endif
              >
                @csrf
                @if($hasFlavors)
                  <input type="hidden" name="flavor_id" id="cart-flavor-{{ $p->id }}">
                @endif
                <button id="btn-cart-{{ $p->id }}" type="submit"
                        class="btn btn-primary btn-sm w-100 d-flex align-items-center justify-content-center"
                        {{ (!$hasFlavors && $p->stok < 1) ? 'disabled' : '' }} title="Tambah ke Keranjang">
                  <i class="bx bx-cart me-1"></i> Keranjang
                </button>
              </form>
            </div>
          </div>
        </div>{{-- card-body --}}
      </div>
    </div>
  @empty
    <div class="col-12">
      <div class="alert alert-info text-center">Produk tidak ditemukan.</div>
    </div>
  @endforelse
</div>

{{-- PAGINATION --}}
@if($products->hasPages())
  <div class="d-flex justify-content-center">
    {{ $products->withQueryString()->links('pagination::bootstrap-5') }}
  </div>
@endif

{{-- ALERT DINAMIS AJAX --}}
<div class="alert-dynamic"></div>
@endsection

@push('styles')
<style>
  .produk-card:hover {
    transform: translateY(-7px) scale(1.03);
    box-shadow: 0 8px 24px rgba(105,108,255,.08);
  }
  .shake { animation: shake .25s linear 2; }
  @keyframes shake {
    0%,100% { transform: translateX(0) }
    25% { transform: translateX(-4px) }
    75% { transform: translateX(4px) }
  }
</style>
@endpush

@push('scripts')
<script>
  // Format Rupiah
  const fmtIDR = (n) => {
    const num = Number(n || 0);
    return isNaN(num) ? '0' : num.toLocaleString('id-ID');
  };

  // Inisialisasi dropdown varian
  document.querySelectorAll('.flavor-select').forEach(select => {
    const basePrice   = parseFloat(select.dataset.basePrice || '0');
    const priceEl     = document.getElementById(select.dataset.priceId);
    const stockEl     = document.getElementById(select.dataset.stockId);
    const beliHidden  = document.getElementById(select.dataset.beliHiddenId);
    const cartHidden  = document.getElementById(select.dataset.cartHiddenId);
    const btnBeli     = document.getElementById(select.dataset.beliBtnId);
    const btnCart     = document.getElementById(select.dataset.cartBtnId);
    const hintEl      = document.getElementById(select.dataset.hintId);

    function needSelectState() {
      if (priceEl) priceEl.innerHTML = '<span class="text-muted">Pilih varian untuk melihat harga</span>';
      if (stockEl) stockEl.textContent = 'Stok: -';
      if (beliHidden) beliHidden.value = '';
      if (cartHidden) cartHidden.value = '';
      if (btnBeli) btnBeli.disabled = true;
      if (btnCart) btnCart.disabled = true;
      if (hintEl) hintEl.classList.remove('d-none');
      select.classList.add('is-invalid');
    }
    function clearHint() {
      select.classList.remove('is-invalid');
      if (hintEl) hintEl.classList.add('d-none');
    }
    function applyVariant() {
      const opt = select.options[select.selectedIndex];
      if (!opt || opt.value === '') { needSelectState(); return; }

      const add  = parseFloat(opt.dataset.add || '0');
      const stok = parseInt(opt.dataset.stok || '0', 10);
      const price = basePrice + add;

      if (priceEl) priceEl.textContent = 'Rp' + fmtIDR(price);
      if (stockEl) stockEl.textContent = 'Stok: ' + (isNaN(stok) ? 0 : stok);
      if (beliHidden) beliHidden.value = opt.value;
      if (cartHidden) cartHidden.value = opt.value;

      const disabled = isNaN(stok) || stok < 1;
      if (btnBeli) btnBeli.disabled = disabled;
      if (btnCart) btnCart.disabled = disabled;

      if (!disabled) clearHint();
    }

    // Set awal (placeholder terpilih => wajib pilih)
    applyVariant();
    select.addEventListener('change', applyVariant);
  });

  // Validasi: cegah submit jika varian belum dipilih
  function ensureVariantOrWarn(form) {
    const selectId = form.getAttribute('data-flavor-select');
    if (!selectId) return true; // tidak butuh varian
    const select = document.getElementById(selectId);
    const hintId = form.getAttribute('data-hint-id');
    const hintEl = hintId ? document.getElementById(hintId) : null;

    const opt = select?.options[select.selectedIndex];
    if (!opt || opt.value === '') {
      if (hintEl) hintEl.classList.remove('d-none');
      select?.classList.add('is-invalid');
      select?.focus({preventScroll:false});
      select?.classList.add('shake');
      setTimeout(() => select?.classList.remove('shake'), 350);
      showAlert('Pilih varian rasa dulu ya 😊', 'warning');
      return false;
    }
    return true;
  }

  // Hook submit Beli (non-AJAX, biarkan submit jika valid)
  document.querySelectorAll('form.form-beli-sekarang').forEach(f=>{
    f.addEventListener('submit', e=>{
      if (!ensureVariantOrWarn(f)) e.preventDefault();
    });
  });

  // AJAX tambah ke keranjang
  document.querySelectorAll('form.form-tambah-keranjang').forEach(form => {
    form.addEventListener('submit', function(e) {
      if (!ensureVariantOrWarn(form)) { e.preventDefault(); return; }

      e.preventDefault();
      let fd = new FormData(this);
      let csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      fetch(this.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: fd
      })
      .then(res => res.json())
      .then(res => {
        if (res.success) {
          showAlert('Produk berhasil masuk ke keranjang!', 'success');
          updateKeranjangBadge(res.qty);
        } else {
          showAlert(res.message || 'Gagal menambah ke keranjang.', 'danger');
        }
      })
      .catch(() => showAlert('Terjadi kesalahan.', 'danger'));
    });
  });

  // Alert helper
  function showAlert(msg, type) {
    let wrap = document.querySelector('.alert-dynamic');
    if (!wrap) {
      wrap = document.createElement('div');
      wrap.className = 'alert-dynamic position-fixed top-0 end-0 m-3';
      wrap.style.zIndex = 9999;
      document.body.appendChild(wrap);
    }
    wrap.innerHTML = `<div class="alert alert-${type} shadow mb-0">${msg}</div>`;
    setTimeout(() => { wrap.innerHTML = '' }, 2200);
  }
  function updateKeranjangBadge(qty) {
    const badge = document.getElementById('keranjang-badge');
    if (badge) badge.textContent = qty > 0 ? qty : '';
  }
</script>
@endpush
