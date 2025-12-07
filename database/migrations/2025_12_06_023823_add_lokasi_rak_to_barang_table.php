<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            // Menambahkan kolom baru
            $table->string('lokasi_rak', 100)->nullable()->after('stok_minimum'); 
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            // Menghapus kolom jika rollback
            $table->dropColumn('lokasi_rak');
        });
    }
};