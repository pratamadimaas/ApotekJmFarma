<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ubah role menjadi: super_admin, admin_cabang, kasir
            $table->string('role', 20)->default('kasir')->change();
            
            // Tambah kolom cabang_id untuk admin_cabang & kasir
            $table->foreignId('cabang_id')->nullable()->after('role')
                  ->constrained('cabang')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['cabang_id']);
            $table->dropColumn('cabang_id');
        });
    }
};