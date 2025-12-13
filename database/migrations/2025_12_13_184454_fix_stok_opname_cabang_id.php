<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ✅ STEP 1: Update semua stok_opname yang cabang_id-nya NULL
        // Ambil cabang_id dari user yang membuat SO tersebut
        
        Log::info('Migration: Starting to fix stok_opname cabang_id NULL values');
        
        $updated = DB::table('stok_opname')
            ->whereNull('cabang_id')
            ->update([
                'cabang_id' => DB::raw('(SELECT cabang_id FROM users WHERE users.id = stok_opname.user_id LIMIT 1)')
            ]);
        
        Log::info('Migration: Updated stok_opname records', ['count' => $updated]);
        
        // ✅ STEP 2: Cek apakah masih ada yang NULL (user tidak punya cabang)
        $stillNull = DB::table('stok_opname')
            ->whereNull('cabang_id')
            ->count();
        
        if ($stillNull > 0) {
            Log::warning('Migration: Some stok_opname still have NULL cabang_id', [
                'count' => $stillNull,
                'reason' => 'User does not have cabang_id'
            ]);
            
            // ✅ OPTIONAL: Set default cabang_id = 1 untuk yang masih NULL
            // Uncomment jika ingin set default
            /*
            DB::table('stok_opname')
                ->whereNull('cabang_id')
                ->update(['cabang_id' => 1]);
            
            Log::info('Migration: Set default cabang_id = 1 for remaining NULL records');
            */
        }
        
        // ✅ STEP 3: Tampilkan ringkasan per cabang
        $summary = DB::table('stok_opname')
            ->select('cabang_id', DB::raw('COUNT(*) as total'))
            ->groupBy('cabang_id')
            ->get();
        
        Log::info('Migration: Stok Opname distribution by cabang', [
            'summary' => $summary->toArray()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu rollback karena ini fix data
        Log::info('Migration: Rollback fix_stok_opname_cabang_id (no action needed)');
    }
};