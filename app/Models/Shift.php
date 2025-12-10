<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'shifts';

    protected $fillable = [
        'user_id',
        'kode_shift',
        'waktu_buka',
        'waktu_tutup',
        'saldo_awal',
        'total_penjualan',
        'total_cash',
        'total_non_cash',
        'saldo_akhir',
        'selisih',
        'keterangan',
        'status'
    ];

    protected $casts = [
        'waktu_buka' => 'datetime',
        'waktu_tutup' => 'datetime',
        'saldo_awal' => 'decimal:2',
        'total_penjualan' => 'decimal:2',
        'total_cash' => 'decimal:2',
        'total_non_cash' => 'decimal:2',
        'saldo_akhir' => 'decimal:2',
        'selisih' => 'decimal:2'
    ];

    // Relationship: Shift milik User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: Shift memiliki banyak penjualan
    public function penjualan()
    {
        return $this->hasMany(Penjualan::class);
    }

    // Scope: Shift yang masih open
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    // Scope: Shift hari ini
    public function scopeToday($query)
    {
        return $query->whereDate('waktu_buka', now());
    }

    // Method: Cek apakah ada shift aktif untuk user
    public static function hasActiveShift($userId)
    {
        return self::where('user_id', $userId)
            ->where('status', 'open')
            ->exists();
    }

    // Method: Get shift aktif untuk user
    public static function getActiveShift($userId)
    {
        return self::where('user_id', $userId)
            ->where('status', 'open')
            ->first();
    }

    // Method: Tutup shift dan hitung selisih
    public function tutupShift($saldoFisik)
    {
        $this->waktu_tutup = now();
        $this->saldo_akhir = $saldoFisik;
        
        // Hitung yang seharusnya
        $saldoSeharusnya = $this->saldo_awal + $this->total_cash;
        
        // Hitung selisih
        $this->selisih = $saldoFisik - $saldoSeharusnya;
        $this->status = 'closed';
        
        return $this->save();
    }
}