<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    use HasFactory;

    protected $table = 'cabang';

    protected $fillable = [
        'kode_cabang',
        'nama_cabang',
        'alamat',
        'telepon',
        'email',
        'penanggung_jawab',
        'aktif'
    ];

    protected $casts = [
        'aktif' => 'boolean'
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Cabang memiliki banyak users
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Cabang memiliki banyak penjualan
     */
    public function penjualan()
    {
        return $this->hasMany(Penjualan::class);
    }

    /**
     * Cabang memiliki banyak pembelian
     */
    public function pembelian()
    {
        return $this->hasMany(Pembelian::class);
    }

    /**
     * Cabang memiliki banyak barang
     * ✅ FIXED: hasMany karena barang.cabang_id langsung ke cabang
     * Jika Anda ingin sistem stok per cabang dengan pivot table,
     * gunakan belongsToMany dan buat tabel stok_cabang
     */
    public function barang()
    {
        return $this->hasMany(Barang::class);
    }

    /**
     * Cabang memiliki banyak shift
     */
    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    // ==========================================
    // QUERY SCOPES
    // ==========================================

    /**
     * Scope: Cabang aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Scope: Cari cabang by kode
     */
    public function scopeByKode($query, $kode)
    {
        return $query->where('kode_cabang', $kode);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Get total stok barang di cabang ini
     */
    public function getTotalStokAttribute()
    {
        return $this->barang()->sum('stok');
    }

    /**
     * Get total nilai stok (harga beli x stok)
     */
    public function getTotalNilaiStokAttribute()
    {
        return $this->barang()->get()->sum(function($barang) {
            return $barang->harga_beli * $barang->stok;
        });
    }

    /**
     * Get jumlah barang stok minimal
     */
    public function getBarangStokMinimalAttribute()
    {
        return $this->barang()->whereRaw('stok <= stok_minimal')->count();
    }

    /**
     * Cek apakah cabang ini adalah cabang utama/pusat
     */
    public function isPusat()
    {
        return stripos($this->nama_cabang, 'pusat') !== false 
            || stripos($this->kode_cabang, 'PST') !== false;
    }
}