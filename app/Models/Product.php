<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'supplier_id',
        'category_id',
        'kode_produk',
        'nama_produk',
        'harga_beli',
        'harga_jual',
        'stok',
        'stok_supplier',
        'deskripsi',
        'notif_admin_seen',
        'is_approved',
    ];

    protected $casts = [
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'stok'       => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function order_items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    // === RASA ===
    public function flavors(): HasMany
    {
        return $this->hasMany(ProductFlavor::class);
    }

    /** Helper: cari flavor by name (null kalau tidak ada) */
    public function flavorByName(?string $name): ?ProductFlavor
    {
        if (!$name) return null;
        return $this->flavors->firstWhere('nama_rasa', $name)
            ?? ProductFlavor::where('product_id', $this->id)
                ->where('nama_rasa', $name)->first();
    }

    /** Helper: harga final untuk flavor tertentu (harga dasar + tambahan) */
    public function priceForFlavor(?string $name): float
    {
        $base = (float) $this->harga_jual;
        $fv   = $this->flavorByName($name);
        return $fv ? $base + (float) ($fv->harga_tambahan ?? 0) : $base;
    }

    /** Generator kode (sesuai versi Anda) */
    public static function generateKodeProduk(): string
    {
        $today = date('Ymd');
        $lastProduct = self::whereDate('created_at', date('Y-m-d'))
            ->orderBy('id', 'desc')
            ->first();

        $newNumber = $lastProduct
            ? str_pad(((int) substr($lastProduct->kode_produk, -4)) + 1, 4, '0', STR_PAD_LEFT)
            : '0001';

        return 'PRD' . $today . $newNumber;
    }
}
