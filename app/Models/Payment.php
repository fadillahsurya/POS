<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    protected $fillable = [
        'order_id',
        'midtrans_order_id',
        'metode_bayar',
        'jumlah_bayar',
        'status_bayar',
        'waktu_bayar',
        'payload_midtrans'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}