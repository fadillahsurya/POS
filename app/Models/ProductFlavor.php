<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductFlavor extends Model
{
    protected $table = 'product_flavors';

    protected $fillable = [
        'product_id',
        'nama_rasa',
        'harga_tambahan',
        'stok',
    ];

    protected $casts = [
        'harga_tambahan' => 'decimal:2',
        'stok'            => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
