<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';

    protected $fillable = [
        'kode_supplier',
        'nama_supplier',
        'alamat',
        'telepon',
        'email',
        'contact_person',
        'aktif'
    ];

    protected $casts = [
        'aktif' => 'boolean'
    ];

    // Relationship: Supplier memiliki banyak pembelian
    public function pembelian()
    {
        return $this->hasMany(Pembelian::class);
    }

    // Scope: Supplier aktif
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    // Method: Generate kode supplier otomatis
    public static function generateKodeSupplier()
    {
        $lastSupplier = self::orderBy('id', 'desc')->first();
        $urutan = $lastSupplier ? intval(substr($lastSupplier->kode_supplier, 4)) + 1 : 1;
        
        return 'SUP-' . str_pad($urutan, 4, '0', STR_PAD_LEFT);
    }
}