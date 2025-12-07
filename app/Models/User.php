<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // admin, kasir
        'aktif'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'aktif' => 'boolean'
    ];

    // Relationship: User memiliki banyak penjualan
    public function penjualan()
    {
        return $this->hasMany(Penjualan::class);
    }

    // Relationship: User memiliki banyak pembelian
    public function pembelian()
    {
        return $this->hasMany(Pembelian::class);
    }

    // Relationship: User memiliki banyak shift
    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    // Method: Check if user is admin
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    // Method: Check if user is kasir
    public function isKasir()
    {
        return $this->role === 'kasir';
    }

    // Scope: User aktif
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}