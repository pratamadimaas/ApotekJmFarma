<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatuanKonversi extends Model
{
    use HasFactory;

    protected $table = 'satuan_konversi';

    protected $fillable = [
        'barang_id',
        'nama_satuan',
        'jumlah_konversi',
        'harga_jual',
        'is_default'
    ];

    protected $casts = [
        'jumlah_konversi' => 'integer',
        'harga_jual' => 'integer',
        'is_default' => 'boolean'
    ];

    // Relationship: Satuan milik Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    // Method: Hitung harga per satuan terkecil
    public function getHargaSatuanTerkecilAttribute()
    {
        return $this->harga_jual / $this->jumlah_konversi;
    }

    // Method: Konversi ke satuan terkecil
    public function konversiKeStok($jumlah)
    {
        return $jumlah * $this->jumlah_konversi;
    }

    // Method: Konversi dari satuan terkecil
    public function konversiDariStok($stok)
    {
        return $stok / $this->jumlah_konversi;
    }
}