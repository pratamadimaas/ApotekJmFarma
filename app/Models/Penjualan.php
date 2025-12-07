<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualan';

    protected $fillable = [
        'nomor_nota',
        'user_id',
        'shift_id',
        'tanggal_penjualan',
        'nama_pelanggan',
        'total_penjualan',
        'diskon',
        'pajak',
        'grand_total',
        'jumlah_bayar',
        'kembalian',
        'metode_pembayaran',
        'keterangan'
    ];

    protected $casts = [
        'tanggal_penjualan' => 'datetime',
        'total_penjualan' => 'decimal:2',
        'diskon' => 'decimal:2',
        'pajak' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'jumlah_bayar' => 'decimal:2',
        'kembalian' => 'decimal:2'
    ];

    // Relationship: Penjualan dimiliki oleh User (Kasir)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: Penjualan dimiliki oleh Shift
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    // Relationship: Penjualan memiliki banyak detail
    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class);
    }

    // Method: Generate nomor nota otomatis
    public static function generateNomorNota()
    {
        $tanggal = now()->format('Ymd');
        $lastNota = self::whereDate('tanggal_penjualan', now())
            ->orderBy('id', 'desc')
            ->first();
        
        $urutan = $lastNota ? intval(substr($lastNota->nomor_nota, -4)) + 1 : 1;
        
        return 'TRX' . $tanggal . str_pad($urutan, 4, '0', STR_PAD_LEFT);
    }

    // Scope: Penjualan hari ini
    public function scopeToday($query)
    {
        return $query->whereDate('tanggal_penjualan', now());
    }

    // Scope: Penjualan bulan ini
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('tanggal_penjualan', now()->month)
            ->whereYear('tanggal_penjualan', now()->year);
    }
}