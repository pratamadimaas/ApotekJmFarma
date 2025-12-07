<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang', 50)->unique();
            $table->string('nama_barang', 200);
            $table->string('kategori', 100)->nullable();
            $table->decimal('harga_beli', 12, 2)->default(0);
            $table->decimal('harga_jual', 12, 2)->default(0);
            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(10);
            $table->string('satuan_terkecil', 50)->default('tablet'); 
            $table->date('tanggal_kadaluarsa')->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            
            $table->index('kode_barang');
            $table->index('nama_barang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};