<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'flavor',     // <— simpan nama rasa
        'qty',
        'harga_jual', // disarankan sudah termasuk tambahan rasa
        'subtotal',
    ];

    protected $casts = [
        'qty'        => 'integer',
        'harga_jual' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /** Optional: detail flavor terkait (berdasarkan nama + product_id) */
    public function flavorDetail(): ?ProductFlavor
    {
        if (!$this->flavor) return null;
        return ProductFlavor::where('product_id', $this->product_id)
            ->where('nama_rasa', $this->flavor)
            ->first();
    }
}
