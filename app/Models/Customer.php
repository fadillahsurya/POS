<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'user_id',
        'nama_customer',
        'alamat',
        'no_hp',
        'foto'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
