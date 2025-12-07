<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_penjualan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained('penjualan')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barang');
            $table->integer('jumlah');
            $table->string('satuan', 50);
            $table->decimal('harga_jual', 12, 2);
            $table->decimal('diskon_item', 12, 2)->default(0);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
            
            $table->index('penjualan_id');
            $table->index('barang_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_penjualan');
    }
};