@extends('layouts.admin')
@section('content')
<div class="mb-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm mb-0">
      <li class="breadcrumb-item">
        <a href="{{ route('dashboard.admin') }}"><i class="bx bx-home"></i> Dashboard</a>
      </li>
      <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Produk</a></li>
      <li class="breadcrumb-item active" aria-current="page">Edit Produk</li>
    </ol>
  </nav>
</div>

<div class="row justify-content-center">
  <div class="col-md-8 col-lg-7">
    <div class="card mb-4">
      <h5 class="card-header">Edit Produk</h5>
      <div class="card-body">
        {{-- Flash & Error --}}
        @if (session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
          <div class="alert alert-danger">
            <div class="fw-bold mb-1">Terjadi kesalahan:</div>
            <ul class="mb-0">
              @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form id="formEditProduct" method="POST" action="{{ route('products.update', $product->id) }}" enctype="multipart/form-data">
          @csrf @method('PUT')

          {{-- Nama Produk --}}
          <div class="mb-3">
            <label for="nama_produk" class="form-label">Nama Produk</label>
            <input type="text" name="nama_produk" id="nama_produk"
                   class="form-control @error('nama_produk') is-invalid @enderror"
                   value="{{ old('nama_produk', $product->nama_produk) }}" required>
            @error('nama_produk') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          {{-- Kategori --}}
          <div class="mb-3">
            <label for="category_id" class="form-label">Kategori</label>
            <select name="category_id" id="category_id"
                    class="form-select @error('category_id') is-invalid @enderror" required>
              @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ (old('category_id', $product->category_id) == $cat->id) ? 'selected' : '' }}>
                  {{ $cat->nama_kategori }}
                </option>
              @endforeach
            </select>
            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          {{-- Kode Produk --}}
          <div class="mb-3">
            <label class="form-label">Kode Produk</label>
            <input type="text" class="form-control" value="{{ $product->kode_produk }}" readonly>
          </div>

          {{-- Supplier (opsional) --}}
          <div class="mb-3">
            <label for="supplier_id" class="form-label">Supplier (Opsional)</label>
            <select name="supplier_id" id="supplier_id" class="form-select">
              <option value="">Internal (tanpa supplier)</option>
              @foreach($suppliers as $sup)
                <option value="{{ $sup->id }}" {{ (old('supplier_id', $product->supplier_id) == $sup->id) ? 'selected' : '' }}>
                  {{ $sup->nama_supplier ?? $sup->name }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Stok total (akan auto dari rasa jika diaktifkan) --}}
          <div class="mb-3">
            <label for="stok" class="form-label">Stok</label>
            <input type="number" name="stok" id="stok"
                   class="form-control @error('stok') is-invalid @enderror"
                   min="0" value="{{ old('stok', $product->stok) }}" required>
            <div class="form-text" id="stokHint">Isi manual jika tidak menggunakan variasi rasa.</div>
            @error('stok') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          {{-- Harga Beli --}}
          <div class="mb-3">
            <label for="harga_beli" class="form-label">Harga Beli</label>
            <input type="number" name="harga_beli" id="harga_beli"
                   class="form-control @error('harga_beli') is-invalid @enderror"
                   min="0" value="{{ old('harga_beli', $product->harga_beli) }}">
            @error('harga_beli') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          {{-- Harga Jual (dasar) --}}
          <div class="mb-3">
            <label for="harga_jual" class="form-label">Harga Jual (dasar)</label>
            <input type="number" name="harga_jual" id="harga_jual"
                   class="form-control @error('harga_jual') is-invalid @enderror"
                   min="0" value="{{ old('harga_jual', $product->harga_jual) }}" required>
            <div class="form-text">Harga tambahan per-rasa diisi di bagian “Pilihan Rasa”.</div>
            @error('harga_jual') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          {{-- Deskripsi --}}
          <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi"
                      class="form-control @error('deskripsi') is-invalid @enderror"
                      rows="2">{{ old('deskripsi', $product->deskripsi) }}</textarea>
            @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          {{-- Foto Produk --}}
          <div class="mb-3">
            <label for="images" class="form-label">Foto Produk (tambahkan jika ingin ganti/menambah)</label>
            <input type="file" name="images[]" id="images"
                   class="form-control @error('images') is-invalid @enderror" multiple>
            @error('images') <div class="invalid-feedback">{{ $message }}</div> @enderror

            @if(isset($product->images) && count($product->images))
              <div class="mt-2">
                <small>Foto saat ini:</small><br>
                @foreach($product->images as $img)
                  <img src="{{ asset('storage/'.$img->file_path) }}" width="60" class="me-2 mb-2 rounded border">
                @endforeach
              </div>
            @endif
          </div>

          {{-- =========================
               PILIHAN RASA (opsional)
              ========================= --}}
          @php $hasFlavors = isset($product->flavors) && $product->flavors->count() > 0; @endphp
          <div class="mb-2 form-check form-switch">
            <input class="form-check-input" type="checkbox" id="use_flavors" {{ $hasFlavors ? 'checked' : '' }}>
            <label class="form-check-label" for="use_flavors">
              Produk punya variasi rasa
            </label>
          </div>

          <div id="flavorsSection" class="border rounded p-3 mb-4 {{ $hasFlavors ? '' : 'd-none' }}">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="mb-0">Pilihan Rasa</h6>
              <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddFlavor">+ Tambah Rasa</button>
            </div>

            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0" id="tblFlavors">
                <thead class="table-light">
                  <tr>
                    <th style="width:50px">#</th>
                    <th>Nama Rasa</th>
                    <th style="width:220px">Harga Tambahan</th>
                    <th style="width:160px">Stok Rasa</th>
                    <th style="width:70px"></th>
                  </tr>
                </thead>
                <tbody>
                  {{-- Baris existing flavors --}}
                  @if($hasFlavors)
                    @foreach($product->flavors as $i => $fv)
                      <tr>
                        <td class="row-num">{{ $i + 1 }}</td>
                        <td>
                          <input type="hidden" name="flavors[{{ $i }}][id]" value="{{ $fv->id }}">
                          <input type="text" name="flavors[{{ $i }}][nama_rasa]" class="form-control"
                                 value="{{ old("flavors.$i.nama_rasa", $fv->nama_rasa) }}"
                                 placeholder="Contoh: Original" required>
                        </td>
                        <td>
                          <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="flavors[{{ $i }}][harga_tambahan]" class="form-control"
                                   step="0.01" min="0"
                                   value="{{ old("flavors.$i.harga_tambahan", $fv->harga_tambahan ?? 0) }}">
                          </div>
                        </td>
                        <td>
                          <input type="number" name="flavors[{{ $i }}][stok]"
                                 class="form-control flavor-stok" min="0"
                                 value="{{ old("flavors.$i.stok", (int)$fv->stok) }}" required>
                        </td>
                        <td class="text-center">
                          <button type="button" class="btn btn-outline-danger btn-sm btnRowRemove">&times;</button>
                        </td>
                      </tr>
                    @endforeach
                  @endif
                </tbody>
              </table>
            </div>

            <div class="form-text">
              Total stok produk akan otomatis = jumlah semua stok rasa.
            </div>
          </div>

          {{-- Tombol --}}
          <div class="d-flex justify-content-end">
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary me-2">
              <i class="bx bx-arrow-back"></i> Batal
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="bx bx-save"></i> Update
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const useFlavors   = document.getElementById('use_flavors');
  const section      = document.getElementById('flavorsSection');
  const tblBody      = document.querySelector('#tblFlavors tbody');
  const btnAdd       = document.getElementById('btnAddFlavor');
  const stokInput    = document.getElementById('stok');
  const stokHint     = document.getElementById('stokHint') || {textContent:()=>{}};

  function toggleFlavorsUI() {
    const on = useFlavors.checked;
    section.classList.toggle('d-none', !on);
    stokInput.readOnly = on;
    stokHint.textContent = on
      ? 'Stok diisi otomatis dari jumlah stok semua rasa.'
      : 'Isi manual jika tidak menggunakan variasi rasa.';
    if (on && tblBody.children.length === 0) addRow(); // siapkan baris awal
    recalcStock();
  }

  function addRow(data = {}) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="row-num">#</td>
      <td>
        <input type="text" name="flavors[][nama_rasa]" class="form-control" placeholder="Contoh: Original" required>
      </td>
      <td>
        <div class="input-group">
          <span class="input-group-text">Rp</span>
          <input type="number" name="flavors[][harga_tambahan]" class="form-control" step="0.01" min="0" value="0">
        </div>
      </td>
      <td>
        <input type="number" name="flavors[][stok]" class="form-control flavor-stok" min="0" value="0" required>
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-outline-danger btn-sm btnRowRemove">&times;</button>
      </td>
    `;
    tblBody.appendChild(tr);
    if (data.nama_rasa) tr.querySelector('input[name="flavors[][nama_rasa]"]').value = data.nama_rasa;
    if (data.harga_tambahan != null) tr.querySelector('input[name="flavors[][harga_tambahan]"]').value = data.harga_tambahan;
    if (data.stok != null) tr.querySelector('input[name="flavors[][stok]"]').value = data.stok;
    reindex();
  }

  function reindex() {
    [...tblBody.querySelectorAll('tr')].forEach((tr, i) => {
      tr.querySelector('.row-num').textContent = i + 1;
      tr.querySelectorAll('input').forEach(inp => {
        // flavors[][x] -> flavors[i][x]
        inp.name = inp.name.replace(/flavors\[\d*\]/, `flavors[${i}]`);
      });
    });
    recalcStock();
  }

  function recalcStock() {
    if (!useFlavors.checked) return;
    let total = 0;
    tblBody.querySelectorAll('.flavor-stok').forEach(inp => {
      const n = parseInt(inp.value || '0', 10);
      if (!isNaN(n)) total += n;
    });
    stokInput.value = total; // sinkron ke stok total
  }

  // Listeners
  useFlavors.addEventListener('change', toggleFlavorsUI);
  btnAdd.addEventListener('click', () => addRow());
  tblBody.addEventListener('input', (e) => {
    if (e.target.classList.contains('flavor-stok')) recalcStock();
  });
  tblBody.addEventListener('click', (e) => {
    if (e.target.classList.contains('btnRowRemove')) {
      e.target.closest('tr').remove();
      if (tblBody.children.length === 0) addRow();
      reindex();
    }
  });

  // Init
  toggleFlavorsUI(); // set awal sesuai checkbox
  // Recalc stok bila halaman berisi data lama
  recalcStock();
})();
</script>
@endpush
