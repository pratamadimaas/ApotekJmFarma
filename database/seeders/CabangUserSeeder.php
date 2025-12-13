<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Cabang;
use App\Models\User;

class CabangUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ✅ BUAT CABANG
        $cabangPusat = Cabang::create([
            'kode_cabang' => 'CB001',
            'nama_cabang' => 'Cabang Pusat Makassar',
            'alamat' => 'Jl. Pahlawan No. 123, Makassar',
            'telepon' => '0411-123456',
            'email' => 'pusat@jmfarma.com',
            'penanggung_jawab' => 'Apt. John Doe',
            'aktif' => true
        ]);

        $cabangUtara = Cabang::create([
            'kode_cabang' => 'CB002',
            'nama_cabang' => 'Cabang Makassar Utara',
            'alamat' => 'Jl. Sungai Saddang No. 45, Makassar',
            'telepon' => '0411-654321',
            'email' => 'utara@jmfarma.com',
            'penanggung_jawab' => 'Apt. Jane Smith',
            'aktif' => true
        ]);

        // ✅ BUAT USERS

        // 1. Super Admin (bisa akses semua cabang)
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@jmfarma.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'cabang_id' => null, // Super admin tidak terikat cabang
            'aktif' => true
        ]);

        // 2. Admin Cabang Pusat
        User::create([
            'name' => 'Admin Cabang Pusat',
            'email' => 'admin.pusat@jmfarma.com',
            'password' => Hash::make('password'),
            'role' => 'admin_cabang',
            'cabang_id' => $cabangPusat->id,
            'aktif' => true
        ]);

        // 3. Kasir Cabang Pusat
        User::create([
            'name' => 'Kasir Pusat 1',
            'email' => 'kasir.pusat1@jmfarma.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
            'cabang_id' => $cabangPusat->id,
            'aktif' => true
        ]);

        // 4. Admin Cabang Utara
        User::create([
            'name' => 'Admin Cabang Utara',
            'email' => 'admin.utara@jmfarma.com',
            'password' => Hash::make('password'),
            'role' => 'admin_cabang',
            'cabang_id' => $cabangUtara->id,
            'aktif' => true
        ]);

        // 5. Kasir Cabang Utara
        User::create([
            'name' => 'Kasir Utara 1',
            'email' => 'kasir.utara1@jmfarma.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
            'cabang_id' => $cabangUtara->id,
            'aktif' => true
        ]);

        $this->command->info('✅ Seeder berhasil: 2 Cabang dan 5 User telah dibuat');
        $this->command->info('');
        $this->command->info('📋 Data Login:');
        $this->command->info('Super Admin: superadmin@jmfarma.com / password');
        $this->command->info('Admin Pusat: admin.pusat@jmfarma.com / password');
        $this->command->info('Kasir Pusat: kasir.pusat1@jmfarma.com / password');
        $this->command->info('Admin Utara: admin.utara@jmfarma.com / password');
        $this->command->info('Kasir Utara: kasir.utara1@jmfarma.com / password');
    }
}