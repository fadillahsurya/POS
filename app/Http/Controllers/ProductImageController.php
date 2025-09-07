<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;

class ProductImageController extends Controller
{
    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $user = auth()->user();
        if ($user->role === 'admin' || ($user->role === 'supplier' && $product->supplier_id == $user->id)) {
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $img) {
                    $product->images()->create([
                        'file_path' => $img->store('product_images', 'public')
                    ]);
                }
            }
            return back()->with('success', 'Foto produk diupload!');
        }
        abort(403, 'Akses ditolak!');
    }

    public function destroy($id)
    {
        $img = ProductImage::findOrFail($id);
        $user = auth()->user();
        $product = $img->product;
        if ($user->role === 'admin' || ($user->role === 'supplier' && $product->supplier_id == $user->id)) {
            $img->delete();
            return back()->with('success', 'Foto dihapus!');
        }
        abort(403, 'Akses ditolak!');
    }
}
