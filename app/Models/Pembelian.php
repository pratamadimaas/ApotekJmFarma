<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Supplier;
use App\Models\User;
use App\Models\DetailPembelian;
use App\Models\Cabang; // Pastikan ini di-import

class Pembelian extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'pembelian';

    // Kolom-kolom yang dapat diisi secara massal
    protected $fillable = [
        'nomor_pembelian',
        'supplier_id',
        'user_id',
        'cabang_id',
        'tanggal_pembelian',
        'total_pembelian',
        'diskon',
        'pajak',
        'grand_total',
        'keterangan',
        'status',
    ];

    // Tipe casting untuk kolom tertentu
    protected $casts = [
        'tanggal_pembelian' => 'date',
        'total_pembelian'   => 'decimal:2',
        'diskon'            => 'decimal:2',
        'pajak'             => 'decimal:2',
        'grand_total'       => 'decimal:2',
    ];

    // --------------------------------------------------------------------
    // RELATIONS
    // --------------------------------------------------------------------

    /**
     * Pembelian ini dilakukan oleh User (Petugas).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Pembelian ini berasal dari Supplier.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Pembelian ini ditujukan untuk Cabang tertentu.
     */
    public function cabang()
    {
        // Menggunakan import Cabang::class yang diasumsikan berada di App\Models
        return $this->belongsTo(Cabang::class);
    }

    /**
     * Pembelian ini memiliki banyak Detail Pembelian (item barang).
     */
    public function detailPembelian()
    {
        return $this->hasMany(DetailPembelian::class);
    }

    // --------------------------------------------------------------------
    // SCOPES
    // --------------------------------------------------------------------

    /**
     * Scope untuk mengambil data pembelian pada bulan dan tahun saat ini.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('tanggal_pembelian', now()->month)
            ->whereYear('tanggal_pembelian', now()->year);
    }

    /**
     * Scope untuk mengambil data pembelian yang berstatus 'approved'.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // --------------------------------------------------------------------
    // STATIC METHODS
    // --------------------------------------------------------------------

    /**
     * Generate nomor pembelian otomatis.
     * Format: PO[YYYYMMDD][0001]
     */
    public static function generateNomorPembelian()
    {
        $tanggal = now()->format('Ymd');
        
        // Cari nomor pembelian terakhir hari ini
        $lastPembelian = self::whereDate('tanggal_pembelian', now())
            ->orderBy('id', 'desc')
            ->first();
        
        // Hitung urutan berikutnya
        $urutan = $lastPembelian 
            ? intval(substr($lastPembelian->nomor_pembelian, -4)) + 1 
            : 1;
        
        // Gabungkan
        return 'PO' . $tanggal . str_pad($urutan, 4, '0', STR_PAD_LEFT);
    }
}