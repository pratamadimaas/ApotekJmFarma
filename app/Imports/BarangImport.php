<?php

namespace App\Imports;

use App\Models\Barang;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session; // ✅ Diperlukan untuk Super Admin filter

class BarangImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    private $importedCount = 0;
    private $skippedCount = 0;
    private $importErrors = [];
    private $activeCabangId; // Properti untuk menyimpan ID Cabang yang ditargetkan

    public function __construct()
    {
        $user = Auth::user();
        
        if (!$user) {
            $this->importErrors[] = "User tidak terautentikasi saat memulai import.";
            $this->activeCabangId = null;
            return;
        }

        // Tentukan ID Cabang yang akan digunakan untuk SEMUA baris import
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            // Jika Super Admin: Prioritaskan filter dari Session (dari Navbar)
            $sessionCabangId = Session::get('selected_cabang_id'); 
            
            if (!empty($sessionCabangId)) {
                $this->activeCabangId = (int) $sessionCabangId;
            } else {
                // Jika Super Admin tidak memilih filter, biarkan null agar terjadi error validasi di model()
                $this->activeCabangId = null; 
            }
            
            Log::info('Import Init: Super Admin mode', [
                'session_cabang_id' => $this->activeCabangId ?? 'NULL (Import Dibatalkan)'
            ]);

        } else {
            // Admin Cabang / Kasir: Gunakan cabang_id dari profil user
            $this->activeCabangId = $user->cabang_id;
            
            Log::info('Import Init: Regular user mode', [
                'user_cabang_id' => $this->activeCabangId
            ]);
        }
    }


    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            $cabangId = $this->activeCabangId;

            Log::info('Import Row - Target Cabang ID:', ['cabang_id' => $cabangId, 'row_data' => $row]);

            // ✅ VALIDASI KRITIS: Hentikan jika ID Cabang tidak teridentifikasi
            if (empty($cabangId)) {
                 // Throw exception agar proses import dihentikan total pada baris pertama
                if ($this->importedCount + $this->skippedCount === 0) {
                     $this->importErrors[] = "Import DIBATALKAN: Super Admin harus memilih cabang yang aktif di Navbar filter atau Anda belum ditugaskan ke cabang.";
                     Log::error('Import Stopped: Cabang ID is NULL.');
                     throw new \Exception("Cabang ID tidak teridentifikasi untuk proses import. Pastikan filter cabang sudah diatur."); 
                }
                $this->skippedCount++;
                return null;
            }

            // ✅ Convert barcode dari scientific notation ke string normal
            $barcode = null;
            if (!empty($row['barcode'])) {
                $barcode = $this->convertScientificToString($row['barcode']);
                
                // Cek barcode duplikat di CABANG YANG DITARGETKAN
                $barcodeExists = Barang::where('barcode', $barcode)
                                       ->where('cabang_id', $cabangId)
                                       ->exists();
                
                if ($barcodeExists) {
                    $this->skippedCount++;
                    $this->importErrors[] = "Barcode {$barcode} sudah ada di cabang ID {$cabangId} (skip)";
                    return null;
                }
            }

            // Cek kode_barang duplikat di CABANG YANG DITARGETKAN
            $exists = Barang::where('kode_barang', $row['kode_barang'])
                            ->where('cabang_id', $cabangId)
                            ->exists();
            
            if ($exists) {
                $this->skippedCount++;
                $this->importErrors[] = "Kode Barang {$row['kode_barang']} sudah ada di cabang ID {$cabangId} (skip)";
                return null;
            }

            $this->importedCount++;

            $barangData = [
                'kode_barang'       => $row['kode_barang'],
                'barcode'           => $barcode,
                'nama_barang'       => $row['nama_barang'],
                'kategori'          => $row['kategori'],
                'satuan_terkecil'   => $row['satuan_terkecil'],
                'harga_beli'        => $row['harga_beli'] ?? 0,
                'harga_jual'        => $row['harga_jual'] ?? 0,
                'stok'              => $row['stok'] ?? 0,
                'stok_minimal'      => $row['stok_minimal'] ?? 5,
                'lokasi_rak'        => $row['lokasi_rak'] ?? null,
                'deskripsi'         => $row['deskripsi'] ?? null,
                'aktif'             => true,
                'cabang_id'         => $cabangId, // ✅ Gunakan ID Cabang yang sudah ditentukan di constructor
            ];

            return new Barang($barangData);

        } catch (\Exception $e) {
            $this->skippedCount++;
            $this->importErrors[] = "Error pada baris: " . $e->getMessage();
            Log::error('Import Error Catch:', ['message' => $e->getMessage(), 'row' => $row]);
            return null;
        }
    }

    /**
     * Validasi per baris
     */
    public function rules(): array
    {
        // Note: Duplikasi unique check dilakukan di model() karena kita perlu filter cabang_id
        return [
            'kode_barang'       => 'required|string|max:50',
            'nama_barang'       => 'required|string|max:255',
            'kategori'          => 'required|string|max:100',
            'satuan_terkecil'   => 'required|string|max:20',
            'harga_beli'        => 'nullable|numeric|min:0',
            'harga_jual'        => 'nullable|numeric|min:0',
            'stok'              => 'nullable|numeric|min:0',
            'stok_minimal'      => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Custom error messages
     */
    public function customValidationMessages()
    {
        return [
            'kode_barang.required'      => 'Kode barang wajib diisi',
            'nama_barang.required'      => 'Nama barang wajib diisi',
            'kategori.required'         => 'Kategori wajib diisi',
            'satuan_terkecil.required'  => 'Satuan wajib diisi',
        ];
    }

    /**
     * Get import statistics
     */
    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }

    public function getErrors()
    {
        return $this->importErrors;
    }
    
    /**
     * Helper method untuk mendapatkan Cabang ID yang aktif (untuk Controller)
     */
    public function getActiveCabangId()
    {
        return $this->activeCabangId;
    }


    /**
     * Convert scientific notation string ke normal string
     * Contoh: "8.99277E+12" atau "8,99277E+12" jadi "8992770000000"
     * * @param mixed $value
     * @return string|null
     */
    private function convertScientificToString($value)
    {
        if (empty($value)) {
            return null;
        }

        // Convert ke string dulu
        $value = (string) $value;

        // Jika sudah berupa string normal (tidak ada E atau e), return langsung
        if (!preg_match('/[eE]/', $value)) {
            // Trim whitespace dan return
            return trim($value);
        }

        // Replace koma dengan titik untuk handle format Excel non-US
        $value = str_replace(',', '.', $value);

        // Convert scientific notation ke number, lalu ke string tanpa decimal
        if (is_numeric($value)) {
            // sprintf dengan %.0f untuk convert tanpa desimal
            $converted = sprintf('%.0f', (float) $value);
            
            Log::info('Barcode Conversion', [
                'original' => $value,
                'converted' => $converted
            ]);
            
            return $converted;
        }

        // Fallback: return original value
        return trim($value);
    }
}