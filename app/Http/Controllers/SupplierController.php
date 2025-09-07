<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\ProductFlavor;

use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    // Lihat semua supplier
    public function index()
    {
        $suppliers = User::where('role', 'supplier')->get();
        return view('admin.suppliers.index', compact('suppliers'));
    }

    // Verifikasi supplier baru (is_active = 0)
    public function products($supplier_id)
    {
        $supplier = User::findOrFail($supplier_id);
        $products = Product::where('supplier_id', $supplier_id)->get();
        return view('admin.suppliers.products', compact('supplier', 'products'));
    }

    // Debug version
    public function productsDebug($supplier_id)
    {
        $supplier = User::findOrFail($supplier_id);

        $products = Product::where('supplier_id', $supplier_id)
            ->with('category')
            ->get();

        Log::info('Supplier Products Debug', [
            'supplier_id'           => $supplier_id,
            'supplier_name'         => $supplier->name,
            'products_count'        => $products->count(),
            'all_products_count'    => Product::count(),
            'products_with_sup_id'  => Product::whereNotNull('supplier_id')->count()
        ]);

        return view('admin.suppliers.products-debug', compact('supplier', 'products'));
    }

    /**
     * Pesan produk dari supplier (buat purchase dan clone produk ke toko).
     * Default: pakai agregat qty (mengurangi stok_supplier).
     * Opsional: jika request mengirim 'flavors' => [{nama_rasa, qty}, ...],
     *           maka akan mengurangi stok per-rasa di supplier.
     */
    public function orderProduct(Request $request, $supplier_id, $product_id)
    {
        $request->validate([
            'qty'      => 'required_without:flavors|integer|min:1',
            'flavors'  => 'nullable|array',
            'flavors.*.nama_rasa' => 'required_with:flavors|string|max:100',
            'flavors.*.qty'       => 'required_with:flavors|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $supplier_id, $product_id) {
            // Ambil produk supplier
            $supplierProduct = Product::where('supplier_id', $supplier_id)
                ->with('images', 'flavors')
                ->where('id', $product_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Total qty yang akan ditarik
            $totalQty = 0;

            if ($request->filled('flavors')) {
                // Mode per-rasa
                foreach ($request->flavors as $row) {
                    $nama  = $row['nama_rasa'];
                    $qty   = (int) $row['qty'];
                    $pf    = $supplierProduct->flavors->firstWhere('nama_rasa', $nama);

                    if (!$pf) {
                        abort(422, "Rasa '$nama' tidak ditemukan pada produk supplier.");
                    }
                    if ($pf->stok < $qty) {
                        abort(422, "Stok rasa '$nama' tidak mencukupi (tersedia {$pf->stok}).");
                    }

                    // Kurangi stok rasa supplier
                    ProductFlavor::where('id', $pf->id)->update(['stok' => $pf->stok - $qty]);
                    $totalQty += $qty;
                }

                // Sinkron stok_supplier = sum stok semua rasa (supplier side)
                $newSupplierStock = (int) ProductFlavor::where('product_id', $supplierProduct->id)->sum('stok');
                $supplierProduct->stok_supplier = $newSupplierStock;
                $supplierProduct->save();
            } else {
                // Mode agregat qty (tanpa detail rasa)
                $qty = (int) $request->qty;

                // Cek stok di kolom stok_supplier (bukan stok)
                if ($supplierProduct->stok_supplier === null || $supplierProduct->stok_supplier < $qty) {
                    abort(422, 'Stok supplier tidak cukup.');
                }

                $supplierProduct->stok_supplier -= $qty;
                $supplierProduct->save();
                $totalQty = $qty;
            }

            // Clone / tambah stok ke produk toko (internal/admin)
            $adminProduct = Product::whereNull('supplier_id')
                ->where('nama_produk', $supplierProduct->nama_produk)
                ->where('category_id', $supplierProduct->category_id)
                ->first();

            if ($adminProduct) {
                // Tambah stok admin secara agregat
                $adminProduct->increment('stok', $totalQty);

                // (Opsional) Jika ingin ikut clone varian ke toko: aktifkan blok di bawah ini
                // foreach ($request->flavors ?? [] as $row) {
                //     $pfAdmin = $adminProduct->flavors()->firstOrCreate(
                //         ['nama_rasa' => $row['nama_rasa']],
                //         ['harga_tambahan' => null, 'stok' => 0]
                //     );
                //     $pfAdmin->increment('stok', (int)$row['qty']);
                // }
            } else {
                // Buat produk baru di admin (clone dari supplier, supplier_id = NULL)
                $newProduct = $supplierProduct->replicate();
                $newProduct->supplier_id = null;
                $newProduct->stok        = $totalQty;
                $newProduct->is_approved = 1;              // langsung tersedia di toko
                $newProduct->notif_admin_seen = 1;
                $newProduct->save();

                // Clone foto
                foreach ($supplierProduct->images as $img) {
                    $newProduct->images()->create(['file_path' => $img->file_path]);
                }

                // (Opsional) clone varian ke toko bila order per-rasa
                // if ($request->filled('flavors')) {
                //     foreach ($request->flavors as $row) {
                //         $newProduct->flavors()->create([
                //             'nama_rasa' => $row['nama_rasa'],
                //             'harga_tambahan' => null,
                //             'stok' => (int)$row['qty'],
                //         ]);
                //     }
                // }
            }
        });

        return back()->with('success', 'Stok berhasil masuk ke toko/admin!');
    }

    // Tampilkan detail supplier (user + relasi supplier)
    public function show($id)
    {
        $supplier = User::with('supplier')->findOrFail($id);
        return view('admin.suppliers.show', compact('supplier'));
    }

    // Nonaktifkan supplier (ubah is_active = 0)
    public function nonaktif($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = 0;
        $user->save();
        return back()->with('success', 'Supplier berhasil dinonaktifkan!');
    }
}
