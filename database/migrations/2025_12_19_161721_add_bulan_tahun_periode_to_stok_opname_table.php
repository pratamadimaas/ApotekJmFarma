<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stok_opname', function (Blueprint $table) {
            // Tambah kolom bulan, tahun, periode
            $table->tinyInteger('bulan')->nullable()->after('tanggal')->comment('1-12');
            $table->year('tahun')->nullable()->after('bulan');
            $table->enum('periode', ['awal', 'akhir'])->default('awal')->after('tahun');
        });
        
        // Update existing records
        DB::statement("
            UPDATE stok_opname 
            SET 
                bulan = MONTH(tanggal),
                tahun = YEAR(tanggal),
                periode = CASE 
                    WHEN DAY(tanggal) <= 15 THEN 'awal' 
                    ELSE 'akhir' 
                END
            WHERE bulan IS NULL OR tahun IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('stok_opname', function (Blueprint $table) {
            $table->dropColumn(['bulan', 'tahun', 'periode']);
        });
    }
};