<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); 
            $table->dateTime('waktu_buka');
            $table->dateTime('waktu_tutup')->nullable();
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->decimal('total_penjualan', 15, 2)->default(0);
            $table->decimal('total_cash', 15, 2)->default(0);
            $table->decimal('total_non_cash', 15, 2)->default(0);
            $table->decimal('saldo_akhir', 15, 2)->default(0);
            $table->decimal('selisih', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('status');
            $table->index('waktu_buka');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};