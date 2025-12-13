<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            // Tambah kolom jika belum ada
            if (!Schema::hasColumn('shifts', 'total_cash')) {
                $table->decimal('total_cash', 15, 2)->default(0)->after('total_penjualan');
            }
            
            if (!Schema::hasColumn('shifts', 'total_non_cash')) {
                $table->decimal('total_non_cash', 15, 2)->default(0)->after('total_cash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['total_cash', 'total_non_cash']);
        });
    }
};