<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            // ✅ Tambah composite unique (kode_barang + cabang_id)
            $table->unique(['kode_barang', 'cabang_id'], 'barang_kode_cabang_unique');
            
            // ✅ Tambah composite unique (barcode + cabang_id)
            $table->unique(['barcode', 'cabang_id'], 'barang_barcode_cabang_unique');
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropUnique('barang_kode_cabang_unique');
            $table->dropUnique('barang_barcode_cabang_unique');
        });
    }
};