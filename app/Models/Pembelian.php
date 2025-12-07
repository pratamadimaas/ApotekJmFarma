<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Supplier;
use App\Models\User;
use App\Models\DetailPembelian;

class Pembelian extends Model
{
    use HasFactory;

    protected $table = 'pembelian';

    protected $fillable = [
        'nomor_pembelian',
        'supplier_id',
        'user_id',
        'tanggal_pembelian',
        'total_pembelian',
        'diskon',
        'pajak',
        'grand_total',
        'keterangan',
        'status'
    ];

    protected $casts = [
        'tanggal_pembelian' => 'date',
        'total_pembelian' => 'decimal:2',
        'diskon' => 'decimal:2',
        'pajak' => 'decimal:2',
        'grand_total' => 'decimal:2'
    ];

    // Relationship: Pembelian milik Supplier
    public function supplier()
    {
        // Now correctly resolved via the 'use App\Models\Supplier' statement
        return $this->belongsTo(Supplier::class);
    }

    // Relationship: Pembelian milik User
    public function user()
    {
        // Now correctly resolved via the 'use App\Models\User' statement
        return $this->belongsTo(User::class);
    }

    // Relationship: Pembelian memiliki banyak detail
    public function detailPembelian()
    {
        // Now correctly resolved via the 'use App\Models\DetailPembelian' statement
        return $this->hasMany(DetailPembelian::class);
    }

    // Method: Generate nomor pembelian otomatis
    public static function generateNomorPembelian()
    {
        $tanggal = now()->format('Ymd');
        $lastPembelian = self::whereDate('tanggal_pembelian', now())
            ->orderBy('id', 'desc')
            ->first();
        
        $urutan = $lastPembelian ? intval(substr($lastPembelian->nomor_pembelian, -4)) + 1 : 1;
        
        return 'PO' . $tanggal . str_pad($urutan, 4, '0', STR_PAD_LEFT);
    }

    // Scope: Pembelian bulan ini
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('tanggal_pembelian', now()->month)
            ->whereYear('tanggal_pembelian', now()->year);
    }

    // Scope: Pembelian yang sudah approved
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}