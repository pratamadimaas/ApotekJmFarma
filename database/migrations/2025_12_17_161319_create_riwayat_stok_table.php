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
        Schema::create('riwayat_stok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barang')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cabang_id')->nullable()->constrained('cabang')->nullOnDelete();
            
            $table->date('tanggal');
            $table->enum('tipe_transaksi', [
                'pembelian', 
                'penjualan', 
                'stok_opname', 
                'edit_manual', 
                'return_pembelian',
                'return_penjualan',
                'penyesuaian'
            ]);
            $table->string('nomor_referensi')->nullable(); // Nomor Nota, SO ID, dll
            
            $table->decimal('stok_sebelum', 15, 2);
            $table->decimal('jumlah_perubahan', 15, 2); // + untuk masuk, - untuk keluar
            $table->decimal('stok_sesudah', 15, 2);
            
            $table->string('satuan', 50);
            $table->text('keterangan')->nullable();
            
            $table->timestamps();
            
            $table->index(['barang_id', 'tanggal']);
            $table->index('tipe_transaksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_stok');
    }
};