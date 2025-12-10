<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel penjualan
        Schema::table('penjualan', function (Blueprint $table) {
            $table->string('nomor_referensi', 100)
                  ->nullable()
                  ->after('metode_pembayaran');
        });

        // Tabel detail_penjualan
        Schema::table('detail_penjualan', function (Blueprint $table) {
            $table->boolean('is_return')->default(false)->after('subtotal');
            $table->dateTime('return_date')->nullable()->after('is_return');
            $table->string('return_keterangan')->nullable()->after('return_date');
        });
    }

    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn('nomor_referensi');
        });

        Schema::table('detail_penjualan', function (Blueprint $table) {
            $table->dropColumn(['is_return', 'return_date', 'return_keterangan']);
        });
    }
};
