<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenjualan extends Model
{
    use HasFactory;

    protected $table = 'detail_penjualan';

    protected $fillable = [
        'penjualan_id',
        'barang_id',
        'jumlah',
        'satuan',
        'harga_jual',
        'diskon_item',
        'subtotal'
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga_jual' => 'decimal:2',
        'diskon_item' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    // Relationship: Detail milik Penjualan
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    // Relationship: Detail milik Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    // Method: Hitung subtotal dengan diskon
    public function hitungSubtotal()
    {
        $total = $this->jumlah * $this->harga_jual;
        $this->subtotal = $total - $this->diskon_item;
        return $this->subtotal;
    }
}