<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatuanKonversi extends Model
{
    use HasFactory;

    protected $table = 'satuan_konversi';

    protected $fillable = [
        'barang_id',
        'nama_satuan',
        'jumlah_konversi',
        'harga_jual',
        'is_default'
    ];

    protected $casts = [
        'jumlah_konversi' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'is_default' => 'boolean'
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Satuan milik Barang
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    // ==========================================
    // ACCESSOR ATTRIBUTES
    // ==========================================

    /**
     * Hitung harga per satuan terkecil
     * Contoh: Box = Rp 10.000 (isi 10 Pcs) → Rp 1.000/Pcs
     */
    public function getHargaSatuanTerkecilAttribute()
    {
        if ($this->jumlah_konversi == 0) {
            return 0;
        }
        return $this->harga_jual / $this->jumlah_konversi;
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * 🔥 Konversi jumlah satuan ini ke satuan terkecil/dasar
     * 
     * @param float $jumlah Jumlah dalam satuan ini
     * @return float Jumlah dalam satuan terkecil
     * 
     * Contoh: 5 Box × 10 (konversi) = 50 Pcs
     */
    public function konversiKeStok(float $jumlah): float
    {
        return $jumlah * $this->jumlah_konversi;
    }

    /**
     * 🔥 Konversi dari satuan terkecil ke satuan ini
     * 
     * @param float $stok Stok dalam satuan terkecil
     * @return float Jumlah dalam satuan ini
     * 
     * Contoh: 50 Pcs ÷ 10 (konversi) = 5 Box
     */
    public function konversiDariStok(float $stok): float
    {
        if ($this->jumlah_konversi == 0) {
            return 0;
        }
        return $stok / $this->jumlah_konversi;
    }

    /**
     * Check apakah satuan ini adalah satuan default
     */
    public function isDefault(): bool
    {
        return (bool) $this->is_default;
    }

    /**
     * Hitung harga total untuk jumlah tertentu
     * 
     * @param float $jumlah Jumlah dalam satuan ini
     * @return float Total harga
     */
    public function hitungHargaTotal(float $jumlah): float
    {
        return $jumlah * $this->harga_jual;
    }

    /**
     * Get format display satuan
     * Contoh: "Box (10 Pcs) - Rp 10.000"
     */
    public function getDisplayFormatAttribute(): string
    {
        $barang = $this->barang;
        $satuanDasar = $barang ? $barang->satuan_terkecil : 'unit';
        
        return sprintf(
            '%s (%s %s) - Rp %s',
            $this->nama_satuan,
            number_format($this->jumlah_konversi, 0, ',', '.'),
            $satuanDasar,
            number_format($this->harga_jual, 0, ',', '.')
        );
    }

    // ==========================================
    // SCOPE QUERIES
    // ==========================================

    /**
     * Scope: Hanya satuan default
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope: Cari satuan berdasarkan nama (case-insensitive)
     */
    public function scopeBySatuanName($query, string $namaSatuan)
    {
        return $query->whereRaw('LOWER(nama_satuan) = ?', [strtolower($namaSatuan)]);
    }

    // ==========================================
    // STATIC HELPERS
    // ==========================================

    /**
     * 🔥 Cari konversi satuan berdasarkan barang_id dan nama_satuan
     * 
     * @param int $barangId
     * @param string $namaSatuan
     * @return SatuanKonversi|null
     */
    public static function findBySatuan(int $barangId, string $namaSatuan): ?self
    {
        return self::where('barang_id', $barangId)
            ->bySatuanName($namaSatuan)
            ->first();
    }

    /**
     * 🔥 Konversi langsung dari satuan apapun ke satuan dasar
     * 
     * @param int $barangId
     * @param float $jumlah
     * @param string $namaSatuan
     * @return float|null Null jika konversi tidak ditemukan
     */
    public static function convertToBase(int $barangId, float $jumlah, string $namaSatuan): ?float
    {
        $barang = Barang::find($barangId);
        
        if (!$barang) {
            return null;
        }

        // Jika sudah satuan dasar, return langsung
        if (strtolower($namaSatuan) === strtolower($barang->satuan_terkecil)) {
            return $jumlah;
        }

        // Cari konversi
        $konversi = self::findBySatuan($barangId, $namaSatuan);
        
        if (!$konversi) {
            return null; // Konversi tidak ditemukan
        }

        return $konversi->konversiKeStok($jumlah);
    }
}