<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // super_admin, admin_cabang, kasir
        'cabang_id',
        'aktif'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'aktif' => 'boolean'
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * User belongs to Cabang
     */
    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    /**
     * User memiliki banyak penjualan
     */
    public function penjualan()
    {
        return $this->hasMany(Penjualan::class);
    }

    /**
     * User memiliki banyak pembelian
     */
    public function pembelian()
    {
        return $this->hasMany(Pembelian::class);
    }

    /**
     * User memiliki banyak shift
     */
    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    // ==========================================
    // ROLE CHECKER METHODS
    // ==========================================

    /**
     * Check if user is super admin
     * 
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is admin cabang
     * 
     * @return bool
     */
    public function isAdminCabang()
    {
        return $this->role === 'admin_cabang';
    }

    /**
     * Check if user is kasir
     * 
     * @return bool
     */
    public function isKasir()
    {
        return $this->role === 'kasir';
    }

    /**
     * Check if user can access all cabang
     * 
     * @return bool
     */
    public function canAccessAllCabang()
    {
        return $this->isSuperAdmin();
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Get accessible cabang IDs
     * 
     * @return array
     */
    public function getAccessibleCabangIds()
    {
        if ($this->isSuperAdmin()) {
            return Cabang::aktif()->pluck('id')->toArray();
        }
        
        return $this->cabang_id ? [$this->cabang_id] : [];
    }

    /**
     * Check if user has access to specific cabang
     * 
     * @param int $cabangId
     * @return bool
     */
    public function hasAccessToCabang($cabangId)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        return $this->cabang_id == $cabangId;
    }

    // ==========================================
    // QUERY SCOPES
    // ==========================================

    /**
     * Scope: User aktif
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Scope: Filter by cabang
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $cabangId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCabang($query, $cabangId)
    {
        return $query->where('cabang_id', $cabangId);
    }

    /**
     * Scope: Filter by role
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $role
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }
}