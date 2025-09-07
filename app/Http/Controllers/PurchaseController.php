<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductClone;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin') abort(403);
        $purchases = Purchase::with('supplier')->get();
        return view('admin.purchase.index', compact('purchases'));
    }

    public function create()
    {
        if (auth()->user()->role !== 'admin') abort(403);
        $suppliers = Supplier::all();
        $products = Product::whereNotNull('supplier_id')->get();
        return view('purchase.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403);
        DB::transaction(function () use ($request) {
            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'tanggal_beli' => now(),
                'total_beli' => 0,
                'status_bayar' => 'belum'
            ]);
            $total = 0;
            foreach ($request->product_ids as $idx => $pid) {
                $qty = $request->qtys[$idx];
                $prod = Product::find($pid);
                $subtotal = $prod->harga_beli * $qty;
                $purchase->items()->create([
                    'product_id' => $pid,
                    'qty' => $qty,
                    'harga_satuan' => $prod->harga_beli,
                    'subtotal' => $subtotal
                ]);
                $total += $subtotal;

                // CLONE ke produk toko jika produk dari supplier
                if ($prod->supplier_id) {
                    $prodClone = $prod->replicate();
                    $prodClone->supplier_id = null;
                    $prodClone->stok = $qty;
                    $prodClone->save();
                    ProductClone::create([
                        'original_product_id' => $prod->id,
                        'cloned_product_id' => $prodClone->id,
                        'admin_id' => auth()->user()->id
                    ]);
                }
            }
            $purchase->update(['total_beli' => $total]);
        });
        return redirect()->route('purchases.index')->with('success', 'Pembelian disimpan');
    }

    public function show($id)
    {
        if (auth()->user()->role !== 'admin') abort(403);
        $purchase = Purchase::with('items.product')->findOrFail($id);
        return view('purchase.show', compact('purchase'));
    }

    public function adminReceiveProduct(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:users,id',
            'kode_produk' => 'required',
            'jumlah'      => 'required|numeric|min:1'
        ]);

        // Cek produk di toko/admin (supplier_id NULL) berdasarkan kode_produk
        $productAdmin = \App\Models\Product::whereNull('supplier_id')
            ->where('kode_produk', $request->kode_produk)
            ->first();

        if ($productAdmin) {
            // Sudah ada di admin: tambah stok
            $productAdmin->stok += $request->jumlah;
            $productAdmin->save();
        } else {
            // Belum ada: copy dari produk supplier ke produk admin
            $productSupplier = \App\Models\Product::where('supplier_id', $request->supplier_id)
                ->where('kode_produk', $request->kode_produk)
                ->first();

            if (!$productSupplier) {
                return back()->with('error', 'Produk supplier tidak ditemukan!');
            }

            $newProduct = $productSupplier->replicate();
            $newProduct->supplier_id = null;
            $newProduct->stok = $request->jumlah;
            $newProduct->save();

            // Jika ada relasi images
            foreach ($productSupplier->images as $img) {
                $newProduct->images()->create([
                    'file_path' => $img->file_path
                ]);
            }
        }

        return back()->with('success', 'Stok produk toko berhasil diupdate!');
    }
}
