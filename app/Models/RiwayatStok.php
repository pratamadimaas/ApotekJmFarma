<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatStok extends Model
{
    protected $table = 'riwayat_stok';
    
    protected $fillable = [
        'barang_id',
        'user_id',
        'cabang_id',
        'tanggal',
        'tipe_transaksi',
        'nomor_referensi',
        'stok_sebelum',
        'jumlah_perubahan',
        'stok_sesudah',
        'satuan',
        'keterangan'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'stok_sebelum' => 'decimal:2',
        'jumlah_perubahan' => 'decimal:2',
        'stok_sesudah' => 'decimal:2',
    ];

    // Relasi
    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }
}