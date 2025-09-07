<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductClone extends Model
{
    protected $fillable = [
        'original_product_id',
        'cloned_product_id',
        'admin_id'
    ];

    public function originalProduct()
    {
        return $this->belongsTo(Product::class, 'original_product_id');
    }

    public function clonedProduct()
    {
        return $this->belongsTo(Product::class, 'cloned_product_id');
    }
}
