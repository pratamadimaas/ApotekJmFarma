<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Tambah cabang_id ke tabel penjualan
        if (!Schema::hasColumn('penjualan', 'cabang_id')) {
            Schema::table('penjualan', function (Blueprint $table) {
                $table->foreignId('cabang_id')->nullable()->after('user_id')
                      ->constrained('cabang')->onDelete('cascade');
            });
        }

        // ✅ Tambah cabang_id ke tabel pembelian
        if (!Schema::hasColumn('pembelian', 'cabang_id')) {
            Schema::table('pembelian', function (Blueprint $table) {
                $table->foreignId('cabang_id')->nullable()->after('user_id')
                      ->constrained('cabang')->onDelete('cascade');
            });
        }

        // ✅ Tambah cabang_id ke tabel barang (tanpa after, biarkan di akhir)
        if (!Schema::hasColumn('barang', 'cabang_id')) {
            Schema::table('barang', function (Blueprint $table) {
                $table->foreignId('cabang_id')->nullable()
                      ->constrained('cabang')->onDelete('set null');
            });
        }

        // ✅ Tambah cabang_id ke tabel shifts
        if (!Schema::hasColumn('shifts', 'cabang_id')) {
            Schema::table('shifts', function (Blueprint $table) {
                $table->foreignId('cabang_id')->nullable()->after('user_id')
                      ->constrained('cabang')->onDelete('cascade');
            });
        }

        // ✅ Tambah cabang_id ke tabel stok_opname (jika ada)
        if (Schema::hasTable('stok_opname') && !Schema::hasColumn('stok_opname', 'cabang_id')) {
            Schema::table('stok_opname', function (Blueprint $table) {
                $table->foreignId('cabang_id')->nullable()->after('user_id')
                      ->constrained('cabang')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('penjualan', 'cabang_id')) {
            Schema::table('penjualan', function (Blueprint $table) {
                $table->dropForeign(['cabang_id']);
                $table->dropColumn('cabang_id');
            });
        }

        if (Schema::hasColumn('pembelian', 'cabang_id')) {
            Schema::table('pembelian', function (Blueprint $table) {
                $table->dropForeign(['cabang_id']);
                $table->dropColumn('cabang_id');
            });
        }

        if (Schema::hasColumn('barang', 'cabang_id')) {
            Schema::table('barang', function (Blueprint $table) {
                $table->dropForeign(['cabang_id']);
                $table->dropColumn('cabang_id');
            });
        }

        if (Schema::hasColumn('shifts', 'cabang_id')) {
            Schema::table('shifts', function (Blueprint $table) {
                $table->dropForeign(['cabang_id']);
                $table->dropColumn('cabang_id');
            });
        }

        if (Schema::hasTable('stok_opname') && Schema::hasColumn('stok_opname', 'cabang_id')) {
            Schema::table('stok_opname', function (Blueprint $table) {
                $table->dropForeign(['cabang_id']);
                $table->dropColumn('cabang_id');
            });
        }
    }
};