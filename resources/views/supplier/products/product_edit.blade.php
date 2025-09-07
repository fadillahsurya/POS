@extends('layouts.supplier')

@section('content')
@php
  $oldFlavors = old('flavors');
  $hasOldFlavors = is_array($oldFlavors) && count($oldFlavors) > 0;
  $hasExistingFlavors = isset($product->flavors) && $product->flavors->count() > 0;
@endphp
<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
      <div class="card mt-4 shadow-sm">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Edit Produk</h5>
        </div>

        <div class="card-body">
          @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif
          @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          <form id="formEditSupplierProduct" method="POST" action="{{ route('supplier.products.update', $product->id) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="mb-3">
              <label class="form-label">Nama Produk</label>
              <input type="text" name="nama_produk" class="form-control" required
                     value="{{ old('nama_produk', $product->nama_produk) }}">
            </div>

            <div class="mb-3">
              <label class="form-label">Kode Produk</label>
              <input type="text" name="kode_produk" class="form-control" required
                     value="{{ old('kode_produk', $product->kode_produk) }}">
            </div>

            <div class="mb-3">
              <label class="form-label">Kategori</label>
              <select name="category_id" class="form-select" required>
                <option value="">-- Pilih Kategori --</option>
                @foreach($categories as $cat)
                  <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                    {{ $cat->nama_kategori }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Harga Beli</label>
              <input type="number" name="harga_beli" class="form-control" min="0" step="0.01" required
                     value="{{ old('harga_beli', $product->harga_beli) }}">
            </div>

            {{-- Stok Supplier (akan auto dari varian bila aktif) --}}
            <div class="mb-3">
              <label class="form-label">Stok Supplier</label>
              <input type="number" name="stok_supplier" id="stok_supplier" class="form-control" min="0" required
                     value="{{ old('stok_supplier', $product->stok_supplier) }}">
              <div class="form-text" id="stokSupplierHint">
                Isi manual jika tidak menggunakan varian rasa.
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Deskripsi</label>
              <textarea name="deskripsi" class="form-control" rows="2">{{ old('deskripsi', $product->deskripsi) }}</textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Foto Produk (upload baru untuk menambah, max 2MB per file)</label>
              <input type="file" name="foto[]" class="form-control" accept="image/*" multiple>
            </div>

            @if($product->images && count($product->images))
              <div class="mb-3">
                <label class="form-label">Foto Saat Ini:</label><br>
                @foreach($product->images as $img)
                  <div class="d-inline-block text-center me-2 mb-2">
                    <img src="{{ asset('storage/'.$img->file_path) }}" width="65" class="rounded shadow mb-1">
                    <form action="{{ route('products.images.destroy', $img->id) }}" method="POST"
                          onsubmit="return confirm('Hapus foto ini?')" style="display:inline;">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger d-block mx-auto mt-1" title="Hapus Foto">
                        <i class="bx bx-trash"></i>
                      </button>
                    </form>
                  </div>
                @endforeach
              </div>
            @endif

            {{-- =========================
                 VARIAN RASA (opsional)
               ========================= --}}
            @php
              $toggleOn = $hasOldFlavors || $hasExistingFlavors;
            @endphp
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="use_flavors" {{ $toggleOn ? 'checked' : '' }}>
              <label class="form-check-label" for="use_flavors">Produk punya varian rasa</label>
            </div>

            <div id="flavorsSection" class="border rounded p-3 mb-4 {{ $toggleOn ? '' : 'd-none' }}">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Varian Rasa</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddFlavor">+ Tambah Varian</button>
              </div>

              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" id="tblFlavors">
                  <thead class="table-light">
                    <tr>
                      <th style="width:50px">#</th>
                      <th>Nama Rasa</th>
                      <th style="width:220px">Harga Tambahan</th>
                      <th style="width:160px">Stok Varian</th>
                      <th style="width:70px"></th>
                    </tr>
                  </thead>
                  <tbody>
                    {{-- Prioritas tampilkan old() saat validasi gagal --}}
                    @if($hasOldFlavors)
                      @foreach($oldFlavors as $i => $fv)
                        <tr>
                          <td class="row-num">{{ $i + 1 }}</td>
                          <td>
                            @if(!empty($fv['id']))
                              <input type="hidden" name="flavors[{{ $i }}][id]" value="{{ $fv['id'] }}">
                            @endif
                            <input type="text" name="flavors[{{ $i }}][nama_rasa]" class="form-control"
                                   value="{{ $fv['nama_rasa'] ?? '' }}" placeholder="Contoh: Original" required>
                          </td>
                          <td>
                            <div class="input-group">
                              <span class="input-group-text">Rp</span>
                              <input type="number" name="flavors[{{ $i }}][harga_tambahan]" class="form-control"
                                     step="0.01" min="0" value="{{ $fv['harga_tambahan'] ?? 0 }}">
                            </div>
                          </td>
                          <td>
                            <input type="number" name="flavors[{{ $i }}][stok]" class="form-control flavor-stok"
                                   min="0" value="{{ $fv['stok'] ?? 0 }}" required>
                          </td>
                          <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm btnRowRemove">&times;</button>
                          </td>
                        </tr>
                      @endforeach
                    @elseif($hasExistingFlavors)
                      @foreach($product->flavors as $i => $fv)
                        <tr>
                          <td class="row-num">{{ $i + 1 }}</td>
                          <td>
                            <input type="hidden" name="flavors[{{ $i }}][id]" value="{{ $fv->id }}">
                            <input type="text" name="flavors[{{ $i }}][nama_rasa]" class="form-control"
                                   value="{{ $fv->nama_rasa }}" placeholder="Contoh: Original" required>
                          </td>
                          <td>
                            <div class="input-group">
                              <span class="input-group-text">Rp</span>
                              <input type="number" name="flavors[{{ $i }}][harga_tambahan]" class="form-control"
                                     step="0.01" min="0" value="{{ $fv->harga_tambahan ?? 0 }}">
                            </div>
                          </td>
                          <td>
                            <input type="number" name="flavors[{{ $i }}][stok]" class="form-control flavor-stok"
                                   min="0" value="{{ (int)$fv->stok }}" required>
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
                Stok supplier akan otomatis = jumlah semua stok varian saat toggle aktif.
              </div>
            </div>

            <div class="d-flex justify-content-end">
              <button class="btn btn-primary"><i class="bx bx-save"></i> Simpan Perubahan</button>
            </div>
          </form>

          <a href="{{ route('supplier.products.index') }}" class="btn btn-link mt-3">
            <i class="bx bx-arrow-back"></i> Kembali ke Daftar Produk
          </a>
        </div>
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
  const stokSupInput = document.getElementById('stok_supplier');
  const stokHint     = document.getElementById('stokSupplierHint');
  const form         = document.getElementById('formEditSupplierProduct');

  function setDisabled(el, disabled) {
    el.querySelectorAll('input,select,textarea,button').forEach(i => {
      // Jangan disable tombol tambah/hapus agar UI tetap bisa dipakai
      if (i.classList.contains('btnRowRemove') || i.id === 'btnAddFlavor') return;
      i.disabled = disabled;
    });
  }

  function toggleFlavorsUI() {
    const on = useFlavors.checked;
    section.classList.toggle('d-none', !on);
    stokSupInput.readOnly = on;
    stokHint.textContent = on
      ? 'Stok diisi otomatis dari jumlah stok semua varian.'
      : 'Isi manual jika tidak menggunakan varian rasa.';
    setDisabled(section, !on);

    if (on && tblBody.children.length === 0) addRow(); // baris awal
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
    if (data.id) tr.insertAdjacentHTML('afterbegin', `<input type="hidden" name="flavors[][id]" value="${data.id}">`);
    if (data.nama_rasa) tr.querySelector('input[name="flavors[][nama_rasa]"]').value = data.nama_rasa;
    if (data.harga_tambahan != null) tr.querySelector('input[name="flavors[][harga_tambahan]"]').value = data.harga_tambahan;
    if (data.stok != null) tr.querySelector('input[name="flavors[][stok]"]').value = data.stok;
    reindex();
    if (!useFlavors.checked) setDisabled(tr, true);
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
    stokSupInput.value = total; // sinkron stok supplier
  }

  // Listeners
  useFlavors.addEventListener('change', toggleFlavorsUI);
  if (btnAdd) btnAdd.addEventListener('click', () => addRow());
  tblBody.addEventListener('input', (e) => {
    if (e.target.classList.contains('flavor-stok')) recalcStock();
  });
  tblBody.addEventListener('click', (e) => {
    if (e.target.classList.contains('btnRowRemove')) {
      e.target.closest('tr').remove();
      if (tblBody.children.length === 0 && useFlavors.checked) addRow();
      reindex();
    }
  });

  // Init
  toggleFlavorsUI();
  // Recalc stok bila ada nilai lama
  recalcStock();
})();
</script>
@endpush
