<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\ProductFlavor;

class ProductController extends Controller
{
    // Semua produk aktif: produk internal & supplier yg sudah di-approve
    public function index()
    {
        $products = Product::with(['category', 'supplier'])
            ->where(function ($q) {
                $q->whereNull('supplier_id')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('supplier_id')->where('is_approved', 1);
                  });
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        // Notifikasi produk supplier yang ditawarkan ke toko, belum di-approve
        $notif_products = Product::with('supplier')
            ->whereNotNull('supplier_id')
            ->where('is_approved', 0)
            ->where('notif_admin_seen', 0)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('admin.products.index', compact('products', 'notif_products'));
    }

    // Detail produk (show admin)
    public function show($id, $notificationId = null)
    {
        $product = Product::with(['category', 'supplier', 'images', 'flavors'])->findOrFail($id);

        // Jika produk supplier, tandai notifikasi sudah dibaca
        if ($product->supplier_id && !$product->notif_admin_seen) {
            $product->notif_admin_seen = 1;
            $product->save();
        }

        // Jika dari notifikasi Laravel, tandai notifikasi sudah dibaca
        if ($notificationId) {
            $notif = auth()->user()->notifications()->where('id', $notificationId)->first();
            if ($notif && !$notif->read_at) {
                $notif->markAsRead();
            }
        }

        return view('admin.products.show', compact('product'));
    }

    // Form tambah produk (admin/supplier)
    public function create()
    {
        $categories = Category::all();
        $suppliers  = User::where('role', 'supplier')->get();
        return view('admin.products.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'nama_produk' => 'required|string|max:100',
            'harga_beli'  => 'nullable|numeric',
            'harga_jual'  => 'required|numeric',
            'stok'        => 'required|numeric|min:0',
            'deskripsi'   => 'nullable|string',
            'images.*'    => 'nullable|image|max:2048',

            // === VARIAN/RASA ===
            'flavors'                     => 'nullable|array',
            'flavors.*.nama_rasa'         => 'required_with:flavors|string|max:100',
            'flavors.*.harga_tambahan'    => 'nullable|numeric|min:0',
            'flavors.*.stok'              => 'required_with:flavors|integer|min:0',
        ]);

        // Validasi harga: jual >= beli
        if ($request->harga_beli !== null && $request->harga_jual < $request->harga_beli) {
            return back()->withInput()->withErrors([
                'harga_jual' => 'Harga jual tidak boleh lebih rendah dari harga beli.'
            ]);
        }

        // Generate kode produk otomatis (gunakan helper Product jika ada)
        if (method_exists(Product::class, 'generateKodeProduk')) {
            $kodeProduk = Product::generateKodeProduk();
        } else {
            // fallback pola lama PRD00001, PRD00002, ...
            $lastProduct = Product::orderBy('id', 'desc')->first();
            $nextNumber  = $lastProduct ? ((int) substr($lastProduct->kode_produk, 3)) + 1 : 1;
            $kodeProduk  = 'PRD' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        }

        // Siapkan data
        $data = $request->only('category_id', 'nama_produk', 'harga_beli', 'harga_jual', 'stok', 'deskripsi');
        $data['kode_produk'] = $kodeProduk;
        $data['supplier_id'] = ($user->role == 'supplier') ? $user->id : $request->supplier_id;

        // Jika flavors dikirim, override stok = sum stok rasa (fallback server-side)
        if ($request->filled('flavors')) {
            $data['stok'] = collect($request->flavors)->sum(fn ($fv) => (int) ($fv['stok'] ?? 0));
        }

        DB::transaction(function () use ($request, $data) {
            $product = Product::create($data);

            // Multi gambar
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $img) {
                    $product->images()->create([
                        'file_path' => $img->store('product_images', 'public')
                    ]);
                }
            }

            // === RASA: create
            if ($request->filled('flavors')) {
                foreach ($request->flavors as $fv) {
                    if (empty($fv['nama_rasa'])) continue;

                    // Unik per-produk
                    $exists = ProductFlavor::where('product_id', $product->id)
                        ->where('nama_rasa', $fv['nama_rasa'])
                        ->exists();
                    if ($exists) {
                        abort(422, "Rasa '{$fv['nama_rasa']}' sudah ada untuk produk ini.");
                    }

                    $product->flavors()->create([
                        'nama_rasa'      => $fv['nama_rasa'],
                        'harga_tambahan' => $fv['harga_tambahan'] ?? null,
                        'stok'           => (int) ($fv['stok'] ?? 0),
                    ]);
                }
            }
            // Jika memakai trigger SQL, products.stok otomatis = sum stok flavors.
        });

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan!');
    }

    // Edit produk (admin/supplier)
    public function edit($id)
    {
        $product    = Product::with(['images', 'flavors'])->findOrFail($id);
        $categories = Category::all();
        $suppliers  = User::where('role', 'supplier')->get();
        return view('admin.products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::with('flavors')->findOrFail($id);
        $user    = auth()->user();

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'nama_produk' => 'required|string|max:100',
            'harga_beli'  => 'nullable|numeric',
            'harga_jual'  => 'required|numeric',
            'stok'        => 'required|numeric|min:0',
            'deskripsi'   => 'nullable|string',
            'images.*'    => 'nullable|image|max:2048',

            // === VARIAN/RASA ===
            'flavors'                  => 'nullable|array',
            'flavors.*.id'             => 'nullable|integer|exists:product_flavors,id',
            'flavors.*.nama_rasa'      => 'required_with:flavors|string|max:100',
            'flavors.*.harga_tambahan' => 'nullable|numeric|min:0',
            'flavors.*.stok'           => 'required_with:flavors|integer|min:0',
        ]);

        // Validasi harga: jual >= beli
        if ($request->harga_beli !== null && $request->harga_jual < $request->harga_beli) {
            return back()->withInput()->withErrors([
                'harga_jual' => 'Harga jual tidak boleh lebih rendah dari harga beli.'
            ]);
        }

        // Otorisasi sederhana
        if (!($user->role === 'admin' || ($user->role === 'supplier' && $product->supplier_id == $user->id))) {
            abort(403, 'Akses ditolak!');
        }

        DB::transaction(function () use ($request, $product, $user) {
            // Data update produk
            $upd = $request->only('category_id', 'nama_produk', 'harga_beli', 'harga_jual', 'stok', 'deskripsi');

            // Admin boleh ganti supplier; supplier tidak
            if ($user->role === 'admin') {
                $upd['supplier_id'] = $request->supplier_id;
            }

            // Jika flavors dikirim, override stok = sum stok rasa (fallback server-side)
            if ($request->filled('flavors')) {
                $upd['stok'] = collect($request->flavors)->sum(fn ($fv) => (int) ($fv['stok'] ?? 0));
            }

            $product->update($upd);

            // Tambah foto baru jika ada (append)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $img) {
                    $product->images()->create([
                        'file_path' => $img->store('product_images', 'public')
                    ]);
                }
            }

            // === RASA: sinkronisasi hanya bila key 'flavors' ada di request
            if ($request->has('flavors')) {
                $sentIds = [];

                foreach (($request->flavors ?? []) as $fv) {
                    if (empty($fv['nama_rasa'])) continue;

                    // Cek unik per-produk (kecuali record yg sedang diupdate)
                    $q = ProductFlavor::where('product_id', $product->id)
                        ->where('nama_rasa', $fv['nama_rasa']);
                    if (!empty($fv['id'])) {
                        $q->where('id', '<>', (int) $fv['id']);
                    }
                    if ($q->exists()) {
                        abort(422, "Rasa '{$fv['nama_rasa']}' sudah ada untuk produk ini.");
                    }

                    if (!empty($fv['id'])) {
                        // update existing
                        $pf = $product->flavors()->where('id', (int) $fv['id'])->firstOrFail();
                        $pf->update([
                            'nama_rasa'      => $fv['nama_rasa'],
                            'harga_tambahan' => $fv['harga_tambahan'] ?? null,
                            'stok'           => (int) ($fv['stok'] ?? 0),
                        ]);
                        $sentIds[] = $pf->id;
                    } else {
                        // create new
                        $pf = $product->flavors()->create([
                            'nama_rasa'      => $fv['nama_rasa'],
                            'harga_tambahan' => $fv['harga_tambahan'] ?? null,
                            'stok'           => (int) ($fv['stok'] ?? 0),
                        ]);
                        $sentIds[] = $pf->id;
                    }
                }

                // Hapus flavor yang tidak ada di kiriman
                if (!empty($sentIds)) {
                    $product->flavors()->whereNotIn('id', $sentIds)->delete();
                } else {
                    // Jika kosongkan semua, hapus semua rasa
                    $product->flavors()->delete();
                }
            }
            // Jika memakai trigger SQL, products.stok otomatis = sum stok flavors.
        });

        return redirect()->route('products.index')->with('success', 'Produk berhasil diupdate!');
    }

    // Hapus produk (dengan pengecekan order item)
    public function destroy($id)
    {
        $product = Product::with('images')->findOrFail($id);
        $user    = auth()->user();

        if ($user->role === 'admin' || ($user->role === 'supplier' && $product->supplier_id == $user->id)) {
            // Tidak boleh hapus jika pernah dipakai transaksi
            $orderItemExist = OrderItem::where('product_id', $product->id)->exists();
            if ($orderItemExist) {
                return back()->with('error', 'Produk tidak dapat dihapus karena sudah pernah dipakai di transaksi.');
            }

            // Hapus foto terkait
            foreach ($product->images as $img) {
                Storage::disk('public')->delete($img->file_path);
                $img->delete();
            }

            // Rasa akan terhapus otomatis jika FK ON DELETE CASCADE (product_flavors.product_id)
            $product->delete();

            return back()->with('success', 'Produk berhasil dihapus!');
        }
        abort(403, 'Akses ditolak!');
    }

    // PROSES: Terima produk dari supplier ke toko
    public function receiveFromSupplier(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty'        => 'required|numeric|min:1',
        ]);

        // Produk supplier
        $supplierProduct = Product::with('images')->where('id', $request->product_id)
            ->whereNotNull('supplier_id')
            ->firstOrFail();

        // Cari produk utama di toko (matching nama & kategori)
        $mainProduct = Product::whereNull('supplier_id')
            ->where('nama_produk', $supplierProduct->nama_produk)
            ->where('category_id', $supplierProduct->category_id)
            ->first();

        if ($mainProduct) {
            $mainProduct->stok += $request->qty;
            $mainProduct->save();
        } else {
            $newProduct = $supplierProduct->replicate();
            $newProduct->supplier_id     = null;
            $newProduct->stok            = $request->qty;
            $newProduct->is_approved     = 1;
            $newProduct->notif_admin_seen= 1;
            $newProduct->save();

            // Copy foto
            foreach ($supplierProduct->images as $img) {
                $newProduct->images()->create(['file_path' => $img->file_path]);
            }
        }

        // Tandai notif telah dilihat
        $supplierProduct->notif_admin_seen = 1;
        $supplierProduct->save();

        return back()->with('success', 'Stok berhasil ditambahkan ke produk toko!');
    }

    // ===================
    // APPROVAL SUPPLIER
    // ===================
    public function pendingApproval()
    {
        $products = Product::whereNotNull('supplier_id')
            ->where('is_approved', 0)
            ->where('notif_admin_seen', 0)
            ->with('supplier', 'category')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.products.pending', compact('products'));
    }

    // Form Approve Produk Supplier (dengan harga jual toko)
    public function showApprovalForm($id)
    {
        $product = Product::with('supplier', 'category')
            ->whereNotNull('supplier_id')
            ->where('is_approved', 0)
            ->findOrFail($id);

        return view('admin.products.approve', compact('product'));
    }

    public function approveSupplierProduct(Request $request, $id)
    {
        $request->validate([
            'harga_jual' => 'required|numeric|min:1',
            'qty'        => 'required|integer|min:1',
        ]);

        // Ambil produk supplier
        $product = Product::findOrFail($id);

        // Validasi stok_supplier cukup
        if ($product->stok_supplier === null || $product->stok_supplier < $request->qty) {
            return back()->with('error', 'Stok supplier tidak cukup!');
        }

        // Tambah stok toko & kurangi stok supplier
        $product->stok          += $request->qty;
        $product->stok_supplier -= $request->qty;

        // Update harga jual toko
        $product->harga_jual     = $request->harga_jual;

        // Tandai sudah approve
        $product->is_approved     = 1;
        $product->notif_admin_seen= 1;
        $product->save();

        return back()->with('success', 'Stok toko bertambah dan stok supplier berkurang!');
    }
}
