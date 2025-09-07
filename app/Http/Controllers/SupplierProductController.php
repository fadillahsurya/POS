<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductFlavor;

class SupplierProductController extends Controller
{
    // List produk milik supplier login (belum & sudah di-approve)
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Product::where('supplier_id', $user->id)
            ->with(['category', 'images', 'flavors']); // <-- load flavors

        if ($request->filled('q')) {
            $query->where('nama_produk', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products   = $query->orderByDesc('created_at')->paginate(10);
        $categories = Category::all();

        return view('supplier.products.product_list', compact('products', 'categories'));
    }

    // Form tambah produk supplier
    public function create()
    {
        $categories = Category::all();
        return view('supplier.products.product_create', compact('categories'));
    }

    // Simpan produk baru (status: belum di-approve)
    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'nama_produk'    => 'required|string|max:100',
            'kode_produk'    => 'required|string|max:50|unique:products,kode_produk',
            'harga_beli'     => 'required|numeric|min:0',
            'stok_supplier'  => 'required|integer|min:0',
            'category_id'    => 'required|exists:categories,id',
            'deskripsi'      => 'nullable|string|max:255',
            'foto.*'         => 'nullable|image|max:2048',

            // === VARIAN/RASA (opsional) ===
            'flavors'                     => 'nullable|array',
            'flavors.*.nama_rasa'         => 'required_with:flavors|string|max:100',
            'flavors.*.harga_tambahan'    => 'nullable|numeric|min:0',
            'flavors.*.stok'              => 'required_with:flavors|integer|min:0',
        ]);

        // Jika flavors dikirim, override stok_supplier = jumlah stok rasa
        $stokSupplier = (int) $request->stok_supplier;
        if ($request->filled('flavors')) {
            $stokSupplier = collect($request->flavors)->sum(fn($fv) => (int)($fv['stok'] ?? 0));
        }

        $data = $request->only('nama_produk', 'kode_produk', 'harga_beli', 'category_id', 'deskripsi');
        $data['stok_supplier']   = $stokSupplier;
        $data['supplier_id']     = $user->id;
        $data['notif_admin_seen']= 1;    // belum ditawarkan
        $data['is_approved']     = 0;    // belum di-approve toko
        $data['harga_jual']      = null; // akan diisi admin saat approve

        DB::transaction(function () use ($request, $data) {
            $product = Product::create($data);

            // Upload multi foto
            if ($request->hasFile('foto')) {
                foreach ($request->file('foto') as $img) {
                    $path = $img->store('produk', 'public');
                    $product->images()->create(['file_path' => $path]);
                }
            }

            // === VARIAN: create
            if ($request->filled('flavors')) {
                foreach ($request->flavors as $fv) {
                    if (empty($fv['nama_rasa'])) continue;

                    // Unik per-produk
                    $exists = ProductFlavor::where('product_id', $product->id)
                        ->where('nama_rasa', $fv['nama_rasa'])->exists();
                    if ($exists) {
                        abort(422, "Rasa '{$fv['nama_rasa']}' sudah ada untuk produk ini.");
                    }

                    $product->flavors()->create([
                        'nama_rasa'      => $fv['nama_rasa'],
                        'harga_tambahan' => $fv['harga_tambahan'] ?? null,
                        'stok'           => (int) ($fv['stok'] ?? 0),  // stok per-rasa (supplier side)
                    ]);
                }
            }
        });

        return redirect()
            ->route('supplier.products.index')
            ->with('success', 'Produk berhasil ditambahkan! Silakan klik "Tawarkan ke Toko" untuk meminta approval.');
    }

    public function offerToStore(Request $request, $id)
    {
        $user    = auth()->user();
        $product = Product::where('supplier_id', $user->id)->findOrFail($id);

        if ($product->is_approved == 1) {
            return back()->with('info', 'Produk sudah di-approve dan masuk ke toko.');
        }
        if ($product->notif_admin_seen == 0) {
            return back()->with('info', 'Produk sudah pernah ditawarkan ke toko. Tunggu approval.');
        }

        $product->notif_admin_seen = 0; // admin akan dapat notif
        $product->save();

        return redirect()
            ->route('supplier.products.index')
            ->with('success', 'Produk berhasil ditawarkan ke toko! Tunggu approval admin.');
    }

    // Edit produk supplier
    public function edit($id)
    {
        $user      = auth()->user();
        $product   = Product::where('supplier_id', $user->id)
                        ->with(['images', 'flavors']) // <-- load flavors
                        ->findOrFail($id);
        $categories= Category::all();
        return view('supplier.products.product_edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $user    = auth()->user();
        $product = Product::where('supplier_id', $user->id)
                    ->with('flavors')
                    ->findOrFail($id);

        $request->validate([
            'nama_produk'    => 'required|string|max:100',
            'kode_produk'    => 'required|string|max:50|unique:products,kode_produk,' . $product->id,
            'harga_beli'     => 'required|numeric|min:0',
            'stok_supplier'  => 'required|integer|min:0',
            'category_id'    => 'required|exists:categories,id',
            'deskripsi'      => 'nullable|string|max:255',
            'foto.*'         => 'nullable|image|max:2048',

            // === VARIAN/RASA (opsional) ===
            'flavors'                  => 'nullable|array',
            'flavors.*.id'             => 'nullable|integer|exists:product_flavors,id',
            'flavors.*.nama_rasa'      => 'required_with:flavors|string|max:100',
            'flavors.*.harga_tambahan' => 'nullable|numeric|min:0',
            'flavors.*.stok'           => 'required_with:flavors|integer|min:0',
        ]);

        // Hitung stok_supplier final
        $stokSupplier = (int) $request->stok_supplier;
        if ($request->filled('flavors')) {
            $stokSupplier = collect($request->flavors)->sum(fn($fv) => (int)($fv['stok'] ?? 0));
        }

        DB::transaction(function () use ($request, $product, $stokSupplier) {
            // Update produk
            $product->update([
                'nama_produk'   => $request->nama_produk,
                'kode_produk'   => $request->kode_produk,
                'harga_beli'    => $request->harga_beli,
                'stok_supplier' => $stokSupplier,
                'category_id'   => $request->category_id,
                'deskripsi'     => $request->deskripsi,
            ]);

            // Upload foto baru (append)
            if ($request->hasFile('foto')) {
                foreach ($request->file('foto') as $img) {
                    $path = $img->store('produk', 'public');
                    $product->images()->create(['file_path' => $path]);
                }
            }

            // === VARIAN: sinkronisasi bila key 'flavors' dikirim
            if ($request->has('flavors')) {
                $sentIds = [];

                foreach (($request->flavors ?? []) as $fv) {
                    if (empty($fv['nama_rasa'])) continue;

                    // Unik per-produk (kecuali record yang sedang diedit)
                    $q = ProductFlavor::where('product_id', $product->id)
                        ->where('nama_rasa', $fv['nama_rasa']);
                    if (!empty($fv['id'])) {
                        $q->where('id', '<>', (int)$fv['id']);
                    }
                    if ($q->exists()) {
                        abort(422, "Rasa '{$fv['nama_rasa']}' sudah ada untuk produk ini.");
                    }

                    if (!empty($fv['id'])) {
                        $pf = $product->flavors()->where('id', (int)$fv['id'])->firstOrFail();
                        $pf->update([
                            'nama_rasa'      => $fv['nama_rasa'],
                            'harga_tambahan' => $fv['harga_tambahan'] ?? null,
                            'stok'           => (int) ($fv['stok'] ?? 0),
                        ]);
                        $sentIds[] = $pf->id;
                    } else {
                        $pf = $product->flavors()->create([
                            'nama_rasa'      => $fv['nama_rasa'],
                            'harga_tambahan' => $fv['harga_tambahan'] ?? null,
                            'stok'           => (int) ($fv['stok'] ?? 0),
                        ]);
                        $sentIds[] = $pf->id;
                    }
                }

                // Hapus rasa yang tidak dikirim
                if (!empty($sentIds)) {
                    $product->flavors()->whereNotIn('id', $sentIds)->delete();
                } else {
                    $product->flavors()->delete();
                }
            }
        });

        return redirect()
            ->route('supplier.products.index')
            ->with('success', 'Produk berhasil diupdate!');
    }

    public function destroy($id)
    {
        $user    = auth()->user();
        $product = Product::where('supplier_id', $user->id)
                    ->with('images')
                    ->findOrFail($id);

        // Tidak boleh hapus jika sudah pernah dipakai order
        $orderItemExist = \App\Models\OrderItem::where('product_id', $product->id)->exists();
        if ($orderItemExist) {
            return back()->with('error', 'Produk tidak dapat dihapus karena sudah pernah dipakai di transaksi.');
        }

        foreach ($product->images as $img) {
            Storage::disk('public')->delete($img->file_path);
            $img->delete();
        }

        $product->delete();
        return back()->with('success', 'Produk berhasil dihapus.');
    }

    // List produk toko (bukan milik supplier, sudah di-approve)
    public function produkToko(Request $request)
    {
        $query = Product::with('category')
            ->whereNull('supplier_id')
            ->orWhere(function ($q) {
                $q->whereNotNull('supplier_id')->where('is_approved', 1);
            });

        if ($request->filled('q')) {
            $query->where('nama_produk', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products   = $query->orderByDesc('created_at')->paginate(10);
        $categories = Category::all();

        return view('supplier.products.product_toko', compact('products', 'categories'));
    }
}
