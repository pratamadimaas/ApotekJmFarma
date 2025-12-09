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
        'barcode',
        'nama_barang',
        'kategori',
        'satuan_terkecil',
        'harga_beli',
        'harga_jual',
        'stok',
        'stok_minimal',
        'lokasi_rak',
        'deskripsi',
        'aktif'
    ];

    protected $casts = [
        'harga_beli' => 'integer',
        'harga_jual' => 'integer',
        'stok' => 'integer',
        'stok_minimal' => 'integer',
        'aktif' => 'boolean',
    ];

    // ============================================
    // RELASI
    // ============================================

    // Relasi ke Satuan Konversi
    public function satuanKonversi()
    {
        return $this->hasMany(SatuanKonversi::class, 'barang_id');
    }

    // Relasi ke Detail Penjualan
    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'barang_id');
    }

    // Relasi ke Detail Pembelian
    public function detailPembelian()
    {
        return $this->hasMany(DetailPembelian::class, 'barang_id');
    }

    // ============================================
    // SCOPES
    // ============================================

    // ✅ Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nama_barang', 'LIKE', "%{$search}%")
              ->orWhere('kode_barang', 'LIKE', "%{$search}%")
              ->orWhere('barcode', 'LIKE', "%{$search}%");
        });
    }

    // ✅ Scope untuk stok rendah/minimal
    public function scopeStokRendah($query)
    {
        return $query->whereRaw('stok <= stok_minimal');
    }

    // ✅ Alias untuk stokRendah (untuk backward compatibility)
    public function scopeStokMinimum($query)
    {
        return $query->whereRaw('stok <= stok_minimal');
    }

    // ✅ Scope untuk barang aktif
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    // Helper: Cek apakah stok rendah
    public function isStokRendah()
    {
        return $this->stok <= $this->stok_minimal;
    }

    // Helper: Format harga beli
    public function getFormattedHargaBeliAttribute()
    {
        return 'Rp ' . number_format($this->harga_beli, 0, ',', '.');
    }

    // Helper: Format harga jual
    public function getFormattedHargaJualAttribute()
    {
        return 'Rp ' . number_format($this->harga_jual, 0, ',', '.');
    }

    // Helper: Get status stok (badge color)
    public function getStatusStokAttribute()
    {
        if ($this->stok <= 0) {
            return 'habis'; // bg-dark
        } elseif ($this->stok <= $this->stok_minimal) {
            return 'rendah'; // bg-danger
        } elseif ($this->stok <= ($this->stok_minimal * 2)) {
            return 'warning'; // bg-warning
        } else {
            return 'aman'; // bg-success
        }
    }

    // Helper: Get badge class untuk status stok
    public function getBadgeStokClassAttribute()
    {
        switch ($this->status_stok) {
            case 'habis':
                return 'bg-dark';
            case 'rendah':
                return 'bg-danger';
            case 'warning':
                return 'bg-warning';
            default:
                return 'bg-success';
        }
    }
}