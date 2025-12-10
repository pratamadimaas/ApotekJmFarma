<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ✅ Ubah enum metode_pembayaran untuk menambahkan 'transfer'
        DB::statement("ALTER TABLE `penjualan` MODIFY `metode_pembayaran` ENUM('cash', 'debit', 'credit', 'qris', 'transfer') DEFAULT 'cash'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke enum lama (tanpa transfer)
        DB::statement("ALTER TABLE `penjualan` MODIFY `metode_pembayaran` ENUM('cash', 'debit', 'credit', 'qris') DEFAULT 'cash'");
    }
};