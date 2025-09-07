<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Tambahkan kolom yang bisa diisi massal
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',        // DITAMBAHKAN
        'is_active',   // DITAMBAHKAN
    ];

    // Sembunyikan saat toArray()/JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Casting tipe data
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean', // Tambahan
    ];

    // Contoh relasi (optional, jika ada di register)
    public function supplier()
    {
        return $this->hasOne(\App\Models\Supplier::class);
    }
    public function mitra()
    {
        return $this->hasOne(\App\Models\Mitra::class);
    }
    public function customer()
    {
        return $this->hasOne(\App\Models\Customer::class);
    }
    
public function cart()
{
    return $this->hasOne(\App\Models\Cart::class);
}

}
