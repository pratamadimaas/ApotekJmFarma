<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

// Import Models yang berhubungan
use App\Models\Supplier;
use App\Models\User;
use App\Models\DetailPembelian;
use App\Models\Cabang;

class Pembelian extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     * @var string
     */
    protected $table = 'pembelian';

    /**
     * Kolom-kolom yang dapat diisi secara massal (mass assignable).
     * @var array<int, string>
     */
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
        'status', // approved, pending, cancelled
    ];

    /**
     * Tipe casting untuk kolom tertentu.
     * Menggunakan 'datetime' untuk kolom tanggal agar Carbon object dapat digunakan.
     * @var array<string, string>
     */
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
     * Pembelian ini ditujukan untuk Cabang tertentu (Multi-Cabang Support).
     */
    public function cabang()
    {
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
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('tanggal_pembelian', Carbon::now()->month)
            ->whereYear('tanggal_pembelian', Carbon::now()->year);
    }

    /**
     * Scope untuk mengambil data pembelian yang berstatus 'approved'.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    // --------------------------------------------------------------------
    // ACCESSORS (Mendapatkan Nilai Atribut Kustom)
    // --------------------------------------------------------------------

    /**
     * Hitung total item barang unik dalam pembelian ini (berdasarkan jumlah baris detail).
     * @return int
     */
    public function getTotalItemsAttribute(): int
    {
        // Pastikan relasi sudah di-load untuk menghindari N+1 Query
        if ($this->relationLoaded('detailPembelian')) {
            return $this->detailPembelian->count();
        }
        return $this->detailPembelian()->count();
    }

    /**
     * Hitung total kuantitas barang (sum dari kolom 'jumlah' di detail).
     * @return float
     */
    public function getTotalQuantityAttribute(): float
    {
        // Pastikan relasi sudah di-load untuk menghindari N+1 Query
        if ($this->relationLoaded('detailPembelian')) {
            return $this->detailPembelian->sum('jumlah');
        }
        return $this->detailPembelian()->sum('jumlah');
    }

    // --------------------------------------------------------------------
    // METHODS & STATIC METHODS
    // --------------------------------------------------------------------

    /**
     * Cek apakah pembelian ini bisa dicetak barcodenya.
     * @return bool
     */
    public function canPrintBarcode(): bool
    {
        // Asumsi hanya pembelian yang sudah disetujui ('approved') yang barangnya bisa dicetak barcodenya.
        return $this->status === 'approved';
    }

    /**
     * Generate nomor pembelian otomatis.
     * Format: PO[YYYYMMDD][0001]
     * @return string
     */
    public static function generateNomorPembelian(): string
    {
        $tanggal = Carbon::now()->format('Ymd');
        
        // Cari nomor pembelian terakhir hari ini
        $lastPembelian = self::whereDate('tanggal_pembelian', Carbon::now())
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