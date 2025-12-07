<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPembelian extends Model
{
    use HasFactory;

    protected $table = 'detail_pembelian';

    protected $fillable = [
        'pembelian_id',
        'barang_id',
        'jumlah',
        'satuan',
        'harga_beli',
        'subtotal',
        'tanggal_kadaluarsa',
        'batch_number'
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga_beli' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tanggal_kadaluarsa' => 'date'
    ];

    // Relationship: Detail milik Pembelian
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }

    // Relationship: Detail milik Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    // Method: Hitung subtotal otomatis
    public function hitungSubtotal()
    {
        $this->subtotal = $this->jumlah * $this->harga_beli;
        return $this->subtotal;
    }
}