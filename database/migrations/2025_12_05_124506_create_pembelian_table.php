<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelian', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pembelian', 50)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('user_id')->constrained('users'); // User yang input
            $table->date('tanggal_pembelian');
            $table->decimal('total_pembelian', 15, 2)->default(0);
            $table->decimal('diskon', 12, 2)->default(0);
            $table->decimal('pajak', 12, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['draft', 'approved', 'cancelled'])->default('draft');
            $table->timestamps();
            
            $table->index('nomor_pembelian');
            $table->index('tanggal_pembelian');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian');
    }
};