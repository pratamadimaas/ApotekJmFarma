<?php

namespace App\Imports;

use App\Models\Barang;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Log;

class BarangImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    private $importedCount = 0;
    private $skippedCount = 0;
    private $importErrors = []; // ← Diganti dari $errors

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            // Cek apakah kode_barang sudah ada (skip jika duplicate)
            $exists = Barang::where('kode_barang', $row['kode_barang'])->exists();
            
            if ($exists) {
                $this->skippedCount++;
                $this->importErrors[] = "Kode Barang {$row['kode_barang']} sudah ada (skip)"; // ← Update
                return null;
            }

            $this->importedCount++;

            return new Barang([
                'kode_barang'     => $row['kode_barang'],
                'barcode'         => $row['barcode'] ?? null,
                'nama_barang'     => $row['nama_barang'],
                'kategori'        => $row['kategori'],
                'satuan_terkecil' => $row['satuan_terkecil'],
                'harga_beli'      => $row['harga_beli'] ?? 0,
                'harga_jual'      => $row['harga_jual'] ?? 0,
                'stok'            => $row['stok'] ?? 0,
                'stok_minimal'    => $row['stok_minimal'] ?? 5,
                'lokasi_rak'      => $row['lokasi_rak'] ?? null,
                'deskripsi'       => $row['deskripsi'] ?? null,
                'aktif'           => true,
            ]);

        } catch (\Exception $e) {
            $this->skippedCount++;
            $this->importErrors[] = "Error pada baris: " . $e->getMessage(); // ← Update
            Log::error('Import Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validasi per baris
     */
    public function rules(): array
    {
        return [
            'kode_barang'     => 'required|string|max:50',
            'nama_barang'     => 'required|string|max:255',
            'kategori'        => 'required|string|max:100',
            'satuan_terkecil' => 'required|string|max:20',
            'harga_beli'      => 'nullable|numeric|min:0',
            'harga_jual'      => 'nullable|numeric|min:0',
            'stok'            => 'nullable|numeric|min:0',
            'stok_minimal'    => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Custom error messages
     */
    public function customValidationMessages()
    {
        return [
            'kode_barang.required'     => 'Kode barang wajib diisi',
            'nama_barang.required'     => 'Nama barang wajib diisi',
            'kategori.required'        => 'Kategori wajib diisi',
            'satuan_terkecil.required' => 'Satuan wajib diisi',
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
        return $this->importErrors; // ← Update
    }
}