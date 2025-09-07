<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Product;

class ProductOfferNotification extends Notification
{
    use Queueable;
    public $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function via($notifiable)
    {
        return ['database']; // simpan di tabel notifications
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Penawaran Produk Baru',
            'body'  => 'Produk "' . $this->product->nama_produk . '" telah ditawarkan oleh supplier.',
            'product_id' => $this->product->id,
        ];
    }
}
