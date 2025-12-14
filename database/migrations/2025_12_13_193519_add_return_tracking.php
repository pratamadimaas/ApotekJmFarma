<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('detail_penjualan', function (Blueprint $table) {
            // Kolom untuk tracking return
            $table->decimal('jumlah_return', 15, 2)->default(0)->after('subtotal');
            $table->text('keterangan_return')->nullable()->after('return_date');
        });
        
        // Tambah kolom di tabel shifts untuk tracking pengeluaran retur
        Schema::table('shifts', function (Blueprint $table) {
            $table->decimal('total_retur', 15, 2)->default(0)->after('total_penjualan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_penjualan', function (Blueprint $table) {
            $table->dropColumn(['jumlah_return', 'keterangan_return']);
        });
        
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('total_retur');
        });
    }
};