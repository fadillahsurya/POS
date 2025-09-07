<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    protected $table = 'orders';

    // Kolom yang boleh diisi massal
    protected $fillable = [
        'user_id',
        'tipe_order',
        'tanggal_order',
        'total_order',
        'status_order',
        'alamat_kirim',
        'catatan',
        'midtrans_order_id',
    ];

    // Relasi ke User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke item pesanan
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    // Relasi ke pembayaran
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'order_id', 'id');
    }

    // Method untuk mengurangi stok produk ketika order selesai
    public function reduceProductStock()
    {
        foreach ($this->items as $item) {
            $product = $item->product;
            if ($product) {
                // Kurangi stok sesuai qty yang dibeli
                $product->stok -= $item->qty;

                // Pastikan stok tidak minus
                if ($product->stok < 0) {
                    $product->stok = 0;
                }

                $product->save();

                // Log untuk tracking
                Log::info('[Stock Reduction] Product stock reduced (Order Completed)', [
                    'product_id' => $product->id,
                    'product_name' => $product->nama_produk,
                    'qty_sold' => $item->qty,
                    'stock_remaining' => $product->stok,
                    'order_id' => $this->id,
                    'midtrans_order_id' => $this->midtrans_order_id,
                    'trigger' => 'Status changed to selesai'
                ]);
            }
        }
    }

    // Method untuk mengembalikan stok produk (jika diperlukan untuk cancel/refund)
    public function restoreProductStock()
    {
        foreach ($this->items as $item) {
            $product = $item->product;
            if ($product) {
                // Kembalikan stok sesuai qty yang dibeli
                $product->stok += $item->qty;
                $product->save();

                // Log untuk tracking
                Log::info('[Stock Restoration] Product stock restored', [
                    'product_id' => $product->id,
                    'product_name' => $product->nama_produk,
                    'qty_restored' => $item->qty,
                    'stock_after_restore' => $product->stok,
                    'order_id' => $this->id,
                    'midtrans_order_id' => $this->midtrans_order_id
                ]);
            }
        }
    }
}
