<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'kategori',
        'harga_beli',
        'harga_jual',
        'stok',
        'stok_minimum',
        'satuan_terkecil',
        'tanggal_kadaluarsa',
        'deskripsi',
        'aktif',
        'lokasi_rak' 
    ];

    protected $casts = [
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'stok' => 'integer',
        'stok_minimum' => 'integer',
        'aktif' => 'boolean',
        'tanggal_kadaluarsa' => 'date'
    ];

    // --- Relasi ---

    // Relasi ke Satuan Konversi (contoh: 1 Box = 10 Pcs)
    public function satuanKonversi()
    {
        return $this->hasMany(SatuanKonversi::class);
    }

    // Relasi ke Detail Pembelian
    public function detailPembelian()
    {
        // Asumsi: Anda memiliki model DetailPembelian
        return $this->hasMany(DetailPembelian::class);
    }

    // Relasi ke Detail Penjualan
    public function detailPenjualan()
    {
        // Asumsi: Anda memiliki model DetailPenjualan
        return $this->hasMany(DetailPenjualan::class);
    }

    // --- Query Scopes & Accessors ---

    // Scope untuk memfilter barang aktif
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    // Scope untuk memfilter barang yang mencapai stok minimum
    public function scopeStokMinimum($query)
    {
        return $query->whereRaw('stok <= stok_minimum');
    }

    // Accessor untuk mengecek apakah stok sudah mencapai minimum
    public function getIsStokMinimumAttribute()
    {
        return $this->stok <= $this->stok_minimum;
    }
}