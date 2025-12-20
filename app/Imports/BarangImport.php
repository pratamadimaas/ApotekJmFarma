<?php

namespace App\Imports;

use App\Models\Barang;
use App\Models\SatuanKonversi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class BarangImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    private $importedCount = 0;
    private $skippedCount = 0;
    private $importErrors = [];
    private $activeCabangId;

    public function __construct()
    {
        $user = Auth::user();
        
        if (!$user) {
            $this->importErrors[] = "User tidak terautentikasi saat memulai import.";
            $this->activeCabangId = null;
            return;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            $sessionCabangId = Session::get('selected_cabang_id'); 
            
            if (!empty($sessionCabangId)) {
                $this->activeCabangId = (int) $sessionCabangId;
            } else {
                $this->activeCabangId = null; 
            }
            
            Log::info('Import Init: Super Admin mode', [
                'session_cabang_id' => $this->activeCabangId ?? 'NULL (Import Dibatalkan)'
            ]);

        } else {
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

            // Validasi cabang ID
            if (empty($cabangId)) {
                if ($this->importedCount + $this->skippedCount === 0) {
                    $this->importErrors[] = "Import DIBATALKAN: Super Admin harus memilih cabang yang aktif di Navbar filter atau Anda belum ditugaskan ke cabang.";
                    Log::error('Import Stopped: Cabang ID is NULL.');
                    throw new \Exception("Cabang ID tidak teridentifikasi untuk proses import. Pastikan filter cabang sudah diatur."); 
                }
                $this->skippedCount++;
                return null;
            }

            // Convert barcode dari scientific notation
            $barcode = null;
            if (!empty($row['barcode'])) {
                $barcode = $this->convertScientificToString($row['barcode']);
                
                $barcodeExists = Barang::where('barcode', $barcode)
                                       ->where('cabang_id', $cabangId)
                                       ->exists();
                
                if ($barcodeExists) {
                    $this->skippedCount++;
                    $this->importErrors[] = "Barcode {$barcode} sudah ada di cabang ID {$cabangId} (skip)";
                    return null;
                }
            }

            // Cek duplikasi kode barang
            $exists = Barang::where('kode_barang', $row['kode_barang'])
                            ->where('cabang_id', $cabangId)
                            ->exists();
            
            if ($exists) {
                $this->skippedCount++;
                $this->importErrors[] = "Kode Barang {$row['kode_barang']} sudah ada di cabang ID {$cabangId} (skip)";
                return null;
            }

            // ✅ Gunakan DB Transaction untuk konsistensi data
            DB::beginTransaction();
            
            try {
                // Buat data barang
                $barang = Barang::create([
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
                    'cabang_id'         => $cabangId,
                ]);

                // ✅ Import Satuan Konversi (Maksimal 5)
                $this->importSatuanKonversi($barang, $row);

                DB::commit();
                $this->importedCount++;
                
                return $barang;

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            $this->skippedCount++;
            $this->importErrors[] = "Error pada baris: " . $e->getMessage();
            Log::error('Import Error Catch:', ['message' => $e->getMessage(), 'row' => $row]);
            return null;
        }
    }

    /**
     * ✅ Import Satuan Konversi dari Excel (Maksimal 5)
     */
    private function importSatuanKonversi(Barang $barang, array $row)
    {
        // Loop untuk 5 satuan konversi
        for ($i = 1; $i <= 5; $i++) {
            $namaSatuan = $row["konversi_{$i}_nama"] ?? null;
            $jumlahKonversi = $row["konversi_{$i}_jumlah"] ?? null;
            $hargaJual = $row["konversi_{$i}_harga"] ?? null;
            $isDefault = $row["konversi_{$i}_default"] ?? null;

            // Skip jika nama satuan atau jumlah konversi kosong
            if (empty($namaSatuan) || empty($jumlahKonversi)) {
                continue;
            }

            // Validasi jumlah konversi harus angka positif
            if (!is_numeric($jumlahKonversi) || $jumlahKonversi <= 0) {
                Log::warning("Konversi {$i} untuk {$barang->kode_barang} diabaikan: jumlah konversi tidak valid");
                continue;
            }

            // Buat satuan konversi
            SatuanKonversi::create([
                'barang_id' => $barang->id,
                'nama_satuan' => trim($namaSatuan),
                'jumlah_konversi' => (int) $jumlahKonversi,
                'harga_jual' => !empty($hargaJual) ? (int) $hargaJual : 0,
                'is_default' => $this->parseBoolean($isDefault)
            ]);

            Log::info("Konversi {$i} untuk {$barang->kode_barang} berhasil ditambahkan", [
                'nama_satuan' => $namaSatuan,
                'jumlah' => $jumlahKonversi
            ]);
        }
    }

    /**
     * Helper untuk parse boolean dari Excel (Ya/Tidak, 1/0, TRUE/FALSE)
     */
    private function parseBoolean($value)
    {
        if (empty($value)) {
            return false;
        }

        $value = strtolower(trim($value));
        
        return in_array($value, ['ya', 'yes', 'true', '1', 1], true);
    }

    /**
     * Validasi per baris
     */
    public function rules(): array
    {
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
    
    public function getActiveCabangId()
    {
        return $this->activeCabangId;
    }

    /**
     * Convert scientific notation string ke normal string
     */
    private function convertScientificToString($value)
    {
        if (empty($value)) {
            return null;
        }

        $value = (string) $value;

        if (!preg_match('/[eE]/', $value)) {
            return trim($value);
        }

        $value = str_replace(',', '.', $value);

        if (is_numeric($value)) {
            $converted = sprintf('%.0f', (float) $value);
            
            Log::info('Barcode Conversion', [
                'original' => $value,
                'converted' => $converted
            ]);
            
            return $converted;
        }

        return trim($value);
    }
}