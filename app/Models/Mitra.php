<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mitra extends Model
{
    protected $fillable = [
        'user_id',
        'nama_mitra',
        'nama_usaha',
        'no_hp',
        'alamat',
        'foto'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
