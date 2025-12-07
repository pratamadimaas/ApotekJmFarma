<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailStokOpname extends Model
{
    use HasFactory;

    protected $table = 'detail_stok_opname';

    protected $fillable = [
        'stok_opname_id',
        'barang_id',
        'stok_sistem',
        'stok_fisik',
        'selisih',
        'expired_date'
    ];

    protected $casts = [
        'expired_date' => 'date'
    ];

    public function stokOpname()
    {
        return $this->belongsTo(StokOpname::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}