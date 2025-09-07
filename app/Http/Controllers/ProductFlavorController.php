<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductFlavor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductFlavorController extends Controller
{
    /** Pastikan user berhak mengelola produk ini (admin / supplier pemilik) */
    private function authorizeProduct(Product $product): void
    {
        $user = auth()->user();
        if ($user?->role === 'admin') return;
        if ($user?->role === 'supplier' && (int) $product->supplier_id === (int) $user->id) return;

        abort(403, 'Akses ditolak.');
    }

    /** List rasa untuk 1 produk */
    public function index($productId)
    {
        $product = Product::with('flavors')->findOrFail($productId);
        $this->authorizeProduct($product);

        if (request()->wantsJson()) {
            return response()->json([
                'product' => $product->only(['id','nama_produk','stok']),
                'flavors' => $product->flavors()->orderBy('nama_rasa')->get(),
            ]);
        }

        // Jika mau tampilan halaman, arahkan ke view Anda sendiri:
        return view('admin.products.flavors', [
            'product' => $product,
            'flavors' => $product->flavors()->orderBy('nama_rasa')->get(),
        ]);
    }

    /** Tambah 1 rasa */
    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $this->authorizeProduct($product);

        $data = $request->validate([
            'nama_rasa'      => ['required','string','max:100',
                Rule::unique('product_flavors','nama_rasa')->where(fn($q)=>$q->where('product_id',$product->id))
            ],
            'harga_tambahan' => ['nullable','numeric','min:0'],
            'stok'           => ['required','integer','min:0'],
        ]);

        $flavor = $product->flavors()->create($data);

        return $this->respondSaved($request, 'Rasa ditambahkan.', [
            'flavor' => $flavor
        ]);
    }

    /** Update 1 rasa */
    public function update(Request $request, $productId, $flavorId)
    {
        $product = Product::findOrFail($productId);
        $this->authorizeProduct($product);

        $flavor = $product->flavors()->where('id',$flavorId)->firstOrFail();

        $data = $request->validate([
            'nama_rasa'      => ['required','string','max:100',
                Rule::unique('product_flavors','nama_rasa')
                    ->where(fn($q)=>$q->where('product_id',$product->id))
                    ->ignore($flavor->id)
            ],
            'harga_tambahan' => ['nullable','numeric','min:0'],
            'stok'           => ['required','integer','min:0'],
        ]);

        $flavor->update($data);

        return $this->respondSaved($request, 'Rasa diperbarui.', [
            'flavor' => $flavor->fresh()
        ]);
    }

    /** Hapus 1 rasa */
    public function destroy(Request $request, $productId, $flavorId)
    {
        $product = Product::findOrFail($productId);
        $this->authorizeProduct($product);

        $flavor = $product->flavors()->where('id',$flavorId)->firstOrFail();
        $flavor->delete();

        return $this->respondSaved($request, 'Rasa dihapus.');
    }

    /**
     * Bulk sync rasa untuk 1 produk (create/update/delete sekaligus).
     * Terima payload array "flavors" dengan elemen:
     *   - id (opsional saat update)
     *   - nama_rasa (wajib)
     *   - harga_tambahan (opsional)
     *   - stok (wajib)
     */
    public function sync(Request $request, $productId)
    {
        $product = Product::with('flavors')->findOrFail($productId);
        $this->authorizeProduct($product);

        $payload = $request->validate([
            'flavors'                     => ['nullable','array'],
            'flavors.*.id'                => ['nullable','integer','exists:product_flavors,id'],
            'flavors.*.nama_rasa'         => ['required_with:flavors','string','max:100'],
            'flavors.*.harga_tambahan'    => ['nullable','numeric','min:0'],
            'flavors.*.stok'              => ['required_with:flavors','integer','min:0'],
        ]);

        $sentIds = [];
        DB::transaction(function () use ($product, $payload, &$sentIds) {
            foreach (($payload['flavors'] ?? []) as $fv) {
                // Cek unik per produk
                $uniqueQuery = ProductFlavor::where('product_id',$product->id)
                    ->where('nama_rasa',$fv['nama_rasa']);

                if (!empty($fv['id'])) {
                    $uniqueQuery->where('id','<>',$fv['id']);
                }
                if ($uniqueQuery->exists()) {
                    abort(422, "Rasa '{$fv['nama_rasa']}' sudah ada untuk produk ini.");
                }

                if (!empty($fv['id'])) {
                    $pf = $product->flavors()->where('id',$fv['id'])->firstOrFail();
                    $pf->update([
                        'nama_rasa'      => $fv['nama_rasa'],
                        'harga_tambahan' => $fv['harga_tambahan'] ?? null,
                        'stok'           => (int) $fv['stok'],
                    ]);
                    $sentIds[] = $pf->id;
                } else {
                    $pf = $product->flavors()->create([
                        'nama_rasa'      => $fv['nama_rasa'],
                        'harga_tambahan' => $fv['harga_tambahan'] ?? null,
                        'stok'           => (int) $fv['stok'],
                    ]);
                    $sentIds[] = $pf->id;
                }
            }

            // Hapus yang tidak dikirim
            if (!empty($sentIds)) {
                $product->flavors()->whereNotIn('id',$sentIds)->delete();
            } else {
                // Jika tidak ada satupun dikirim, kosongkan semua rasa
                $product->flavors()->delete();
            }
        });

        return $this->respondSaved($request, 'Rasa tersinkron.', [
            'flavors' => $product->flavors()->orderBy('nama_rasa')->get()
        ]);
    }

    /** Helper respons: JSON untuk AJAX, redirect back untuk form biasa */
    private function respondSaved(Request $request, string $message, array $extra = [])
    {
        if ($request->wantsJson()) {
            return response()->json(array_merge(['message'=>$message], $extra));
        }
        return back()->with('success', $message);
    }
}
