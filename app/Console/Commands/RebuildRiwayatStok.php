<?php

namespace App\Console\Commands;

use App\Models\Barang;
use App\Models\RiwayatStok;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\StokOpname;
use App\Models\SatuanKonversi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RebuildStokCommand extends Command
{
    protected $signature = 'stok:rebuild {--force : Force rebuild tanpa konfirmasi}';
    protected $description = 'Rebuild stok dan riwayat stok dari semua transaksi';

    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  Command ini akan menghapus SEMUA riwayat stok dan rebuild ulang. Lanjutkan?')) {
                $this->error('❌ Dibatalkan.');
                return 1;
            }
        }

        $this->info('🚀 Memulai rebuild stok...');
        
        DB::beginTransaction();
        try {
            // Step 1: Backup
            $this->info('📦 Backup data...');
            $backupTime = now()->format('YmdHis');
            try {
                DB::statement("CREATE TABLE IF NOT EXISTS riwayat_stok_backup_{$backupTime} AS SELECT * FROM riwayat_stok");
            } catch (\Exception $e) {
                $this->warn('⚠️  Backup gagal (mungkin tabel sudah ada): ' . $e->getMessage());
            }
            
            // Step 2: Clear riwayat_stok
            $this->info('🗑️  Menghapus riwayat stok lama...');
            RiwayatStok::truncate();
            $this->info('✅ Riwayat stok lama dihapus');
            
            // Step 3: Reset stok
            $this->info('🔄 Reset stok barang...');
            Barang::query()->update(['stok' => 0]);
            
            // Step 4: Collect transaksi
            $this->info('📦 Mengambil data transaksi...');
            $transaksi = $this->collectTransaksi();
            
            $this->info("📊 Total transaksi ditemukan: " . count($transaksi));
            
            // Step 5: Process transaksi
            $bar = $this->output->createProgressBar(count($transaksi));
            $bar->start();
            
            $processed = 0;
            $errors = 0;
            
            foreach ($transaksi as $t) {
                try {
                    $this->processTransaksi($t);
                    $processed++;
                } catch (\Exception $e) {
                    $errors++;
                    Log::error('❌ Rebuild Error:', [
                        'type' => $t['type'],
                        'id' => $t['id'],
                        'barang' => $t['detail']->barang->nama_barang ?? 'Unknown',
                        'error' => $e->getMessage()
                    ]);
                }
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            
            DB::commit();
            
            $this->info("✅ BERHASIL! Riwayat stok telah direbuild.");
            $this->info("📊 Total riwayat dibuat: {$processed}");
            
            if ($errors > 0) {
                $this->warn("⚠️  Total error: {$errors}");
            }
            
            // Summary
            $this->showSummary();
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Fatal Error: ' . $e->getMessage());
            Log::error('Rebuild Fatal Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    private function collectTransaksi(): array
    {
        $transaksi = [];
        
        // Pembelian
        $pembelian = Pembelian::with('detail.barang.satuanKonversi')
            ->whereIn('status', ['approved', 'completed'])
            ->orderBy('tanggal_pembelian')
            ->orderBy('created_at')
            ->get();
            
        foreach ($pembelian as $p) {
            foreach ($p->detail as $d) {
                $transaksi[] = [
                    'type' => 'pembelian',
                    'id' => $p->id,
                    'detail' => $d,
                    'parent' => $p,
                    'tanggal' => $p->tanggal_pembelian,
                    'created_at' => $p->created_at
                ];
            }
        }
        
        // Penjualan
        $penjualan = Penjualan::with('detail.barang.satuanKonversi')
            ->where('status', 'completed')
            ->orderBy('tanggal_penjualan')
            ->orderBy('created_at')
            ->get();
            
        foreach ($penjualan as $p) {
            foreach ($p->detail as $d) {
                if (!$d->is_return) {
                    $transaksi[] = [
                        'type' => 'penjualan',
                        'id' => $p->id,
                        'detail' => $d,
                        'parent' => $p,
                        'tanggal' => $p->tanggal_penjualan,
                        'created_at' => $p->created_at
                    ];
                }
            }
        }
        
        // Stok Opname
        $stokOpname = StokOpname::with('detail.barang.satuanKonversi')
            ->where('status', 'approved')
            ->orderBy('tanggal_opname')
            ->orderBy('created_at')
            ->get();
            
        foreach ($stokOpname as $so) {
            foreach ($so->detail as $d) {
                $selisih = $d->stok_fisik - $d->stok_sistem;
                if ($selisih != 0) {
                    $transaksi[] = [
                        'type' => 'stok_opname',
                        'id' => $so->id,
                        'detail' => $d,
                        'parent' => $so,
                        'tanggal' => $so->tanggal_opname,
                        'created_at' => $so->created_at,
                        'selisih' => $selisih
                    ];
                }
            }
        }
        
        // Sort by tanggal dan created_at
        usort($transaksi, function($a, $b) {
            $cmp = $a['tanggal'] <=> $b['tanggal'];
            if ($cmp === 0) {
                return $a['created_at'] <=> $b['created_at'];
            }
            return $cmp;
        });
        
        return $transaksi;
    }

    private function processTransaksi(array $t)
    {
        $detail = $t['detail'];
        $parent = $t['parent'];
        $barang = $detail->barang;
        
        if (!$barang) {
            throw new \Exception("Barang tidak ditemukan untuk detail ID: {$detail->id}");
        }

        // 🔥 KONVERSI SATUAN KE SATUAN DASAR
        $jumlahDasar = $this->convertToBase($detail->jumlah, $detail->satuan, $barang);
        
        // Hitung perubahan stok
        if ($t['type'] === 'pembelian') {
            $perubahan = $jumlahDasar;
        } elseif ($t['type'] === 'penjualan') {
            $perubahan = -$jumlahDasar;
        } else { // stok_opname
            $perubahan = $t['selisih'];
        }
        
        // Update stok barang
        $stokLama = $barang->fresh()->stok; // Fresh data dari DB
        $stokBaru = $stokLama + $perubahan;
        $barang->update(['stok' => $stokBaru]);
        
        // Log konversi untuk debug
        if ($detail->satuan !== $barang->satuan_terkecil) {
            Log::info('🔄 Konversi', [
                'barang' => $barang->nama_barang,
                'dari' => "{$detail->jumlah} {$detail->satuan}",
                'ke' => "{$jumlahDasar} {$barang->satuan_terkecil}",
                'stok_lama' => $stokLama,
                'perubahan' => $perubahan,
                'stok_baru' => $stokBaru
            ]);
        }
        
        // Buat riwayat (SESUAI STRUKTUR TABEL)
        RiwayatStok::create([
            'barang_id' => $barang->id,
            'user_id' => $parent->user_id ?? 1,
            'cabang_id' => $barang->cabang_id,
            'tanggal' => $t['tanggal'],
            'tipe_transaksi' => $t['type'],
            'nomor_referensi' => $this->getNomorReferensi($t['type'], $parent),
            'stok_sebelum' => $stokLama,
            'jumlah_perubahan' => $perubahan,
            'stok_sesudah' => $stokBaru,
            'satuan' => $barang->satuan_terkecil,
            'keterangan' => $this->getKeterangan($t['type'], $parent, $detail, $jumlahDasar)
        ]);
    }

    private function convertToBase(float $jumlah, string $satuan, Barang $barang): float
    {
        // Jika sudah satuan dasar, return langsung
        if (strtolower($satuan) === strtolower($barang->satuan_terkecil)) {
            return $jumlah;
        }

        // Cari konversi satuan (case-insensitive)
        $konversi = $barang->satuanKonversi
            ->first(fn($k) => strtolower($k->nama_satuan) === strtolower($satuan));

        if (!$konversi) {
            Log::warning('⚠️  Satuan tidak ditemukan, menggunakan 1:1', [
                'barang_id' => $barang->id,
                'barang' => $barang->nama_barang,
                'satuan_input' => $satuan,
                'satuan_dasar' => $barang->satuan_terkecil,
                'satuan_tersedia' => $barang->satuanKonversi->pluck('nama_satuan')->toArray()
            ]);
            return $jumlah;
        }

        // Konversi: jumlah × faktor konversi
        $result = $jumlah * $konversi->jumlah_konversi;
        
        return $result;
    }

    private function getNomorReferensi(string $type, $parent): ?string
    {
        if ($type === 'pembelian') {
            return $parent->nomor_pembelian;
        } elseif ($type === 'penjualan') {
            return $parent->nomor_nota;
        } elseif ($type === 'stok_opname') {
            return $parent->nomor_opname;
        }
        return null;
    }

    private function getKeterangan(string $type, $parent, $detail, float $jumlahDasar): string
    {
        $nomor = $this->getNomorReferensi($type, $parent);
        $barang = $detail->barang;
        
        if ($detail->satuan !== $barang->satuan_terkecil) {
            // Ada konversi, tampilkan
            return ucfirst($type) . " {$nomor} - {$detail->jumlah} {$detail->satuan} = {$jumlahDasar} {$barang->satuan_terkecil}";
        } else {
            // Tidak ada konversi
            return ucfirst($type) . " {$nomor}";
        }
    }

    private function showSummary()
    {
        $barang = Barang::where('stok', '!=', 0)
            ->orderBy('nama_barang')
            ->get();
        
        if ($barang->isEmpty()) {
            $this->warn('⚠️  Tidak ada barang dengan stok');
            return;
        }
        
        $rows = [];
        foreach ($barang as $b) {
            $rows[] = [
                $b->nama_barang,
                $b->stok . ' ' . $b->satuan_terkecil,
                RiwayatStok::where('barang_id', $b->id)->count()
            ];
        }
        
        $this->table(
            ['Barang', 'Stok Akhir', 'Total Riwayat'],
            $rows
        );
    }
}