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
        'cabang_id',        // ✅ TAMBAHKAN INI
        'kode_shift',
        'waktu_buka',
        'waktu_tutup',
        'saldo_awal',
        'saldo_akhir',
        'total_penjualan',
        'total_cash',
        'total_non_cash',
        'selisih',
        'keterangan',
        'status'
    ];

    protected $casts = [
        'waktu_buka' => 'datetime',
        'waktu_tutup' => 'datetime',
        'saldo_awal' => 'decimal:2',
        'saldo_akhir' => 'decimal:2',
        'total_penjualan' => 'decimal:2',
        'total_cash' => 'decimal:2',
        'total_non_cash' => 'decimal:2',
        'selisih' => 'decimal:2',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Shift belongs to User (Kasir)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Shift belongs to Cabang
     * ✅ TAMBAHKAN RELASI INI
     */
    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    /**
     * Shift has many Penjualan
     */
    public function penjualan()
    {
        return $this->hasMany(Penjualan::class);
    }

    // ==========================================
    // QUERY SCOPES
    // ==========================================

    /**
     * Scope: Shift yang masih aktif/terbuka
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'open')
                     ->whereNull('waktu_tutup');
    }

    /**
     * Scope: Shift yang sudah ditutup
     */
    public function scopeTertutup($query)
    {
        return $query->where('status', 'closed')
                     ->whereNotNull('waktu_tutup');
    }

    /**
     * Scope: Filter by cabang_id
     * ✅ TAMBAHKAN SCOPE INI
     */
    public function scopeByCabang($query, $cabangId)
    {
        if ($cabangId) {
            return $query->where('cabang_id', $cabangId);
        }
        return $query;
    }

    /**
     * Scope: Shift hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('waktu_buka', today());
    }

    /**
     * Scope: Shift dalam range tanggal
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereDate('waktu_buka', '>=', $from)
                     ->whereDate('waktu_buka', '<=', $to);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Get durasi shift dalam menit
     */
    public function getDurasiMenitAttribute()
    {
        if (!$this->waktu_tutup) {
            return null;
        }
        return $this->waktu_buka->diffInMinutes($this->waktu_tutup);
    }

    /**
     * Get durasi shift dalam format readable
     */
    public function getDurasiFormattedAttribute()
    {
        if (!$this->waktu_tutup) {
            return 'Belum ditutup';
        }
        return $this->waktu_buka->diffForHumans($this->waktu_tutup, true);
    }

    /**
     * Check apakah shift memiliki selisih (tidak pas)
     */
    public function hasSelisih()
    {
        return $this->selisih != 0;
    }

    /**
     * Get status selisih (plus/minus/pas)
     */
    public function getStatusSelisihAttribute()
    {
        if ($this->selisih > 0) {
            return 'plus';
        } elseif ($this->selisih < 0) {
            return 'minus';
        }
        return 'pas';
    }

    /**
     * Get jumlah transaksi dalam shift ini
     */
    public function getJumlahTransaksiAttribute()
    {
        return $this->penjualan()->count();
    }

    /**
     * Get rata-rata nilai transaksi
     */
    public function getRataRataTransaksiAttribute()
    {
        $jumlah = $this->jumlah_transaksi;
        if ($jumlah == 0) {
            return 0;
        }
        return $this->total_penjualan / $jumlah;
    }
}