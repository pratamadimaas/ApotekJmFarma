<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Setting;
use App\Models\Barang;
use App\Models\SatuanKonversi;
use App\Models\Supplier;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Users
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@apotek.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'aktif' => true
        ]);

        $kasir = User::create([
            'name' => 'Kasir 1',
            'email' => 'kasir@apotek.com',
            'password' => Hash::make('kasir123'),
            'role' => 'kasir',
            'aktif' => true
        ]);

        // 2. Create Settings Default
        $settings = [
            [
                'key' => 'nama_apotek',
                'value' => 'Apotek Sehat',
                'type' => 'string',
                'group' => 'apotek',
                'label' => 'Nama Apotek',
                'description' => 'Nama apotek yang ditampilkan di aplikasi'
            ],
            [
                'key' => 'alamat_apotek',
                'value' => 'Jl. Kesehatan No. 123, Jakarta',
                'type' => 'string',
                'group' => 'apotek',
                'label' => 'Alamat Apotek',
                'description' => 'Alamat lengkap apotek'
            ],
            [
                'key' => 'telepon_apotek',
                'value' => '021-1234567',
                'type' => 'string',
                'group' => 'apotek',
                'label' => 'Telepon',
                'description' => 'Nomor telepon apotek'
            ],
            [
                'key' => 'margin_default',
                'value' => '30',
                'type' => 'number',
                'group' => 'kasir',
                'label' => 'Margin Harga Default (%)',
                'description' => 'Persentase margin untuk hitung harga jual otomatis'
            ],
            [
                'key' => 'pajak_penjualan',
                'value' => '10',
                'type' => 'number',
                'group' => 'kasir',
                'label' => 'Pajak Penjualan (%)',
                'description' => 'Persentase pajak yang dikenakan pada penjualan'
            ],
            [
                'key' => 'minimal_stok_alert',
                'value' => '10',
                'type' => 'number',
                'group' => 'general',
                'label' => 'Minimal Stok Alert',
                'description' => 'Jumlah minimal stok sebelum muncul peringatan'
            ],
            [
                'key' => 'auto_print_struk',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'kasir',
                'label' => 'Auto Print Struk',
                'description' => 'Otomatis print struk setelah transaksi'
            ]
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }

        // 3. Create Suppliers
        $suppliers = [
            [
                'kode_supplier' => 'SUP-0001',
                'nama_supplier' => 'PT. Kimia Farma',
                'alamat' => 'Jakarta Pusat',
                'telepon' => '021-5555001',
                'email' => 'supply@kimiafarma.co.id',
                'contact_person' => 'Budi Santoso',
                'aktif' => true
            ],
            [
                'kode_supplier' => 'SUP-0002',
                'nama_supplier' => 'PT. Kalbe Farma',
                'alamat' => 'Jakarta Timur',
                'telepon' => '021-5555002',
                'email' => 'supply@kalbe.co.id',
                'contact_person' => 'Siti Nurhaliza',
                'aktif' => true
            ],
            [
                'kode_supplier' => 'SUP-0003',
                'nama_supplier' => 'PT. Sanbe Farma',
                'alamat' => 'Bandung',
                'telepon' => '022-5555003',
                'email' => 'supply@sanbe.co.id',
                'contact_person' => 'Ahmad Fauzi',
                'aktif' => true
            ]
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }

        // 4. Create Sample Barang dengan Multi Satuan
        $barangList = [
            [
                'kode_barang' => 'OBT-0001',
                'nama_barang' => 'Paracetamol 500mg',
                'kategori' => 'Obat Umum',
                'harga_beli' => 500,
                'harga_jual' => 650,
                'stok' => 1000, // dalam tablet
                'stok_minimum' => 100,
                'satuan_terkecil' => 'tablet',
                'tanggal_kadaluarsa' => now()->addYears(2),
                'deskripsi' => 'Obat penurun panas dan pereda nyeri',
                'aktif' => true,
                'satuan' => [
                    ['nama_satuan' => 'Tablet', 'jumlah_konversi' => 1, 'harga_jual' => 650, 'is_default' => true],
                    ['nama_satuan' => 'Strip', 'jumlah_konversi' => 10, 'harga_jual' => 6000, 'is_default' => false],
                    ['nama_satuan' => 'Box', 'jumlah_konversi' => 100, 'harga_jual' => 55000, 'is_default' => false]
                ]
            ],
            [
                'kode_barang' => 'OBT-0002',
                'nama_barang' => 'Amoxicillin 500mg',
                'kategori' => 'Antibiotik',
                'harga_beli' => 1200,
                'harga_jual' => 1560,
                'stok' => 500,
                'stok_minimum' => 50,
                'satuan_terkecil' => 'kapsul',
                'tanggal_kadaluarsa' => now()->addYears(1),
                'deskripsi' => 'Antibiotik untuk infeksi bakteri',
                'aktif' => true,
                'satuan' => [
                    ['nama_satuan' => 'Kapsul', 'jumlah_konversi' => 1, 'harga_jual' => 1560, 'is_default' => true],
                    ['nama_satuan' => 'Strip', 'jumlah_konversi' => 10, 'harga_jual' => 15000, 'is_default' => false]
                ]
            ],
            [
                'kode_barang' => 'OBT-0003',
                'nama_barang' => 'Vitamin C 1000mg',
                'kategori' => 'Vitamin',
                'harga_beli' => 800,
                'harga_jual' => 1040,
                'stok' => 800,
                'stok_minimum' => 80,
                'satuan_terkecil' => 'tablet',
                'tanggal_kadaluarsa' => now()->addMonths(18),
                'deskripsi' => 'Suplemen vitamin C dosis tinggi',
                'aktif' => true,
                'satuan' => [
                    ['nama_satuan' => 'Tablet', 'jumlah_konversi' => 1, 'harga_jual' => 1040, 'is_default' => true],
                    ['nama_satuan' => 'Botol', 'jumlah_konversi' => 30, 'harga_jual' => 30000, 'is_default' => false]
                ]
            ],
            [
                'kode_barang' => 'OBT-0004',
                'nama_barang' => 'Obat Batuk Hitam (OBH)',
                'kategori' => 'Obat Batuk',
                'harga_beli' => 8000,
                'harga_jual' => 10400,
                'stok' => 60, // dalam botol (60ml per botol)
                'stok_minimum' => 10,
                'satuan_terkecil' => 'botol',
                'tanggal_kadaluarsa' => now()->addMonths(24),
                'deskripsi' => 'Sirup obat batuk 60ml',
                'aktif' => true,
                'satuan' => [
                    ['nama_satuan' => 'Botol', 'jumlah_konversi' => 1, 'harga_jual' => 10400, 'is_default' => true]
                ]
            ]
        ];

        foreach ($barangList as $barangData) {
            $satuan = $barangData['satuan'];
            unset($barangData['satuan']);
            
            $barang = Barang::create($barangData);
            
            foreach ($satuan as $s) {
                SatuanKonversi::create([
                    'barang_id' => $barang->id,
                    'nama_satuan' => $s['nama_satuan'],
                    'jumlah_konversi' => $s['jumlah_konversi'],
                    'harga_jual' => $s['harga_jual'],
                    'is_default' => $s['is_default']
                ]);
            }
        }

        $this->command->info('✅ Seeding completed successfully!');
        $this->command->info('Admin: admin@apotek.com / admin123');
        $this->command->info('Kasir: kasir@apotek.com / kasir123');
    }
}