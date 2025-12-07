<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pembelian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelian_id')->constrained('pembelian')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barang');
            $table->integer('jumlah');
            $table->string('satuan', 50);
            $table->decimal('harga_beli', 12, 2);
            $table->decimal('subtotal', 15, 2);
            $table->date('tanggal_kadaluarsa')->nullable();
            $table->string('batch_number', 100)->nullable();
            $table->timestamps();
            
            $table->index('pembelian_id');
            $table->index('barang_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pembelian');
    }
};