<?php

// app/Models/StokOpname.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokOpname extends Model
{
    use HasFactory;

    protected $table = 'stok_opname';

    protected $fillable = [
        'user_id',
        'tanggal',
        'keterangan',
        'status',
        'completed_at'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'completed_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(DetailStokOpname::class);
    }
}

// app/Models/DetailStokOpname.php
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