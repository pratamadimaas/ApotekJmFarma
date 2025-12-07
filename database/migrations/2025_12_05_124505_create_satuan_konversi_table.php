<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('satuan_konversi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barang')->onDelete('cascade');
            $table->string('nama_satuan', 50); // Box, Strip, Botol
            $table->integer('jumlah_konversi'); // 1 Box = 10 Strip
            $table->decimal('harga_jual', 12, 2)->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->index('barang_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('satuan_konversi');
    }
};