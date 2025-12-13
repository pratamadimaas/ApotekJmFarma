<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokOpname extends Model
{
    use HasFactory;

    protected $table = 'stok_opname';

    protected $fillable = [
        'user_id',
        'cabang_id', // ✅ KOLOM REAL
        'tanggal',
        'keterangan',
        'status',
        'completed_at'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'completed_at' => 'datetime'
    ];

    /**
     * Relationship: StokOpname belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ✅ Relationship: StokOpname belongs to Cabang
     */
    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    /**
     * Relationship: StokOpname has many DetailStokOpname
     */
    public function details()
    {
        return $this->hasMany(DetailStokOpname::class);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Draft sessions only
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope: Completed sessions only
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * ✅ Scope: Filter by cabang (langsung dari kolom)
     */
    public function scopeByCabang($query, $cabangId)
    {
        if ($cabangId) {
            return $query->where('cabang_id', $cabangId);
        }
        return $query;
    }

    /**
     * HELPER: Check if SO belongs to specific cabang
     */
    public function belongsToCabang($cabangId)
    {
        return $this->cabang_id == $cabangId;
    }
}