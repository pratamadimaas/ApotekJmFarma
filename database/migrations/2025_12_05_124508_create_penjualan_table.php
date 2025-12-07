<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_nota', 50)->unique();
            $table->foreignId('user_id')->constrained('users'); // Kasir
            $table->foreignId('shift_id')->nullable()->constrained('shifts');
            $table->dateTime('tanggal_penjualan');
            $table->string('nama_pelanggan', 200)->nullable();
            $table->decimal('total_penjualan', 15, 2)->default(0);
            $table->decimal('diskon', 12, 2)->default(0);
            $table->decimal('pajak', 12, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('jumlah_bayar', 15, 2)->default(0);
            $table->decimal('kembalian', 15, 2)->default(0);
            $table->enum('metode_pembayaran', ['cash', 'debit', 'credit', 'qris'])->default('cash');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            $table->index('nomor_nota');
            $table->index('tanggal_penjualan');
            $table->index('shift_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};