<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Ubah ENUM status untuk menambahkan 'pending'
        DB::statement("ALTER TABLE pembelian MODIFY COLUMN status ENUM('pending', 'approved', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        // Kembalikan ke kondisi semula (sesuaikan dengan ENUM lama Anda)
        DB::statement("ALTER TABLE pembelian MODIFY COLUMN status ENUM('approved', 'cancelled') NOT NULL DEFAULT 'approved'");
    }
};