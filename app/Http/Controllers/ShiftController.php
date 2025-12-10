<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    // Halaman Buka Shift (formBuka)
    public function formBuka()
    {
        // Cek apakah user sudah punya shift aktif
        $shiftAktif = Shift::where('user_id', Auth::id())
                            ->whereNull('waktu_tutup')
                            ->first();

        if ($shiftAktif) {
            return redirect()->route('penjualan.index')->with('info', 'Anda sudah memiliki shift aktif!');
        }

        return view('pages.shift.buka');
    }

    // Proses Buka Shift (buka) - DENGAN TRANSACTION & LOCK
    public function buka(Request $request)
    {
        $request->validate([
            'modal_awal' => 'required|numeric|min:0'
        ]);

        // Cek shift aktif
        $shiftAktif = Shift::where('user_id', Auth::id())
                            ->whereNull('waktu_tutup')
                            ->first();

        if ($shiftAktif) {
            return back()->with('error', 'Anda sudah memiliki shift aktif!');
        }

        // -----------------------------------------------------------------
        // ✅ GENERATE KODE SHIFT DENGAN RETRY MECHANISM (Prevent Duplicate)
        // -----------------------------------------------------------------
        $maxRetries = 5;
        $attempt = 0;
        
        while ($attempt < $maxRetries) {
            try {
                $shift = DB::transaction(function () use ($request) {
                    $tanggalHariIni = now()->format('dmY'); // Format: DDMMYYYY
                    
                    // 🔒 LOCK TABLE: Ambil nomor urut tertinggi hari ini
                    $lastShift = Shift::whereDate('waktu_buka', today())
                                      ->lockForUpdate()
                                      ->orderBy('id', 'desc')
                                      ->first();
                    
                    // Tentukan nomor urut berikutnya
                    if ($lastShift && $lastShift->kode_shift) {
                        // Extract nomor urut dari kode shift terakhir (format: XX-DDMMYYYY)
                        $parts = explode('-', $lastShift->kode_shift);
                        $lastNumber = isset($parts[0]) ? (int)$parts[0] : 0;
                        $nomorUrut = $lastNumber + 1;
                    } else {
                        // Jika belum ada shift hari ini, mulai dari 1
                        $nomorUrut = 1;
                    }
                    
                    // Format kode shift: [Nomor Urut 2 Digit]-[Tanggal DDMMYYYY]
                    $kodeShift = str_pad($nomorUrut, 2, '0', STR_PAD_LEFT) . '-' . $tanggalHariIni;
                    
                    // Cek apakah kode shift sudah ada (double-check)
                    $exists = Shift::where('kode_shift', $kodeShift)->exists();
                    if ($exists) {
                        throw new \Exception('Duplicate shift code detected');
                    }
                    
                    // Buat shift baru
                    return Shift::create([
                        'user_id' => Auth::id(),
                        'kode_shift' => $kodeShift,
                        'waktu_buka' => now(),
                        'modal_awal' => $request->modal_awal,
                        'status' => 'aktif'
                    ]);
                });

                // Jika berhasil, return success
                return redirect()->route('penjualan.index')
                               ->with('success', 'Shift berhasil dibuka! Kode Shift: ' . $shift->kode_shift);

            } catch (\Illuminate\Database\QueryException $e) {
                // Jika duplicate entry, retry
                if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $attempt++;
                    if ($attempt >= $maxRetries) {
                        return back()->with('error', 'Gagal membuka shift setelah beberapa percobaan. Silakan hubungi administrator.')
                                   ->withInput();
                    }
                    // Wait sedikit sebelum retry (10-50ms)
                    usleep(rand(10000, 50000));
                    continue;
                }
                
                // Jika error lain, langsung throw
                throw $e;
                
            } catch (\Exception $e) {
                // Handle error lainnya
                $attempt++;
                if ($attempt >= $maxRetries) {
                    return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                               ->withInput();
                }
                usleep(rand(10000, 50000));
                continue;
            }
        }
        
        // Jika sampai sini, berarti sudah retry max tapi tetap gagal
        return back()->with('error', 'Gagal membuka shift. Silakan coba lagi.')
                   ->withInput();
        // -----------------------------------------------------------------
    }

    // Halaman Tutup Shift (formTutup) - BLIND CLOSING: Tidak tampilkan data penjualan
    public function formTutup()
    {
        $shift = Shift::where('user_id', Auth::id())
                     ->whereNull('waktu_tutup')
                     ->first();

        if (!$shift) {
            return redirect()->route('shift.buka.form')->with('error', 'Tidak ada shift aktif!');
        }

        // HANYA kirim data shift, TANPA data penjualan untuk blind closing
        return view('pages.shift.tutup', compact('shift'));
    }

    // Proses Tutup Shift (tutup) - Hitung dan tampilkan hasil
    public function tutup(Request $request)
    {
        $request->validate([
            'uang_fisik' => 'required|numeric|min:0',
            'catatan' => 'nullable|string'
        ]);

        $shift = Shift::where('user_id', Auth::id())
                     ->whereNull('waktu_tutup')
                     ->firstOrFail();

        // Hitung total penjualan tunai (Grand Total dari transaksi tunai/cash)
        $totalTunai = Penjualan::where('shift_id', $shift->id)
                             ->where('metode_pembayaran', 'cash')
                             ->sum('grand_total');
                             
        $totalPenjualan = Penjualan::where('shift_id', $shift->id)->sum('grand_total');
        $jumlahTransaksi = Penjualan::where('shift_id', $shift->id)->count();

        // Hitung per metode pembayaran untuk laporan
        $tunai = Penjualan::where('shift_id', $shift->id)
                         ->where('metode_pembayaran', 'cash')
                         ->sum('grand_total');
        
        $nonTunai = Penjualan::where('shift_id', $shift->id)
                            ->whereIn('metode_pembayaran', ['debit', 'credit', 'qris'])
                            ->sum('grand_total');

        $uangSeharusnya = $shift->modal_awal + $totalTunai;
        $selisih = $request->uang_fisik - $uangSeharusnya;

        // Update shift
        $shift->update([
            'waktu_tutup' => now(),
            'total_penjualan' => $totalPenjualan,
            'jumlah_transaksi' => $jumlahTransaksi,
            'uang_fisik' => $request->uang_fisik,
            'selisih' => $selisih,
            'catatan' => $request->catatan,
            'status' => 'tutup'
        ]);
        return redirect()->route('shift.hasil', $shift->id)
                         ->with('success', 'Shift berhasil ditutup!');
    }

    public function hasil($id)
    {
        $shift = Shift::with('user')->findOrFail($id);

        // Pastikan hanya admin atau pemilik shift yang bisa lihat
        if (Auth::user()->role !== 'admin' && $shift->user_id !== Auth::id()) {
            abort(403);
        }

        // Pastikan shift sudah ditutup
        if (!$shift->waktu_tutup) {
            return redirect()->route('shift.tutup.form')->with('error', 'Shift belum ditutup!');
        }

        $tunai = Penjualan::where('shift_id', $shift->id)
                         ->where('metode_pembayaran', 'cash')
                         ->sum('grand_total');
        
        $nonTunai = Penjualan::where('shift_id', $shift->id)
                            ->whereIn('metode_pembayaran', ['debit', 'credit', 'qris'])
                            ->sum('grand_total');

        $uangSeharusnya = $shift->modal_awal + $tunai;

        return view('pages.shift.hasil', compact('shift', 'tunai', 'nonTunai', 'uangSeharusnya'));
    }

    // Riwayat Shift (riwayat)
    public function riwayat(Request $request)
    {
        $query = Shift::with('user')->whereNotNull('waktu_tutup');

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('waktu_buka', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('waktu_buka', '<=', $request->tanggal_sampai);
        }

        // Asumsi user role 'admin' memiliki akses penuh
        if (Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        $shifts = $query->orderBy('waktu_tutup', 'desc')->paginate(20);

        return view('pages.shift.riwayat', compact('shifts'));
    }

    // Detail Shift (detail)
    public function detail($id)
    {
        $shift = Shift::with(['user', 'penjualan' => function($q) {
            $q->with('detailPenjualan.barang');
        }])->findOrFail($id);

        if (Auth::user()->role !== 'admin' && $shift->user_id !== Auth::id()) {
            abort(403);
        }

        return view('pages.shift.detail', compact('shift'));
    }

    // Cetak Laporan Shift ukuran 58mm (cetakLaporan)
    public function cetakLaporan($id)
    {
        $shift = Shift::with(['user', 'penjualan'])->findOrFail($id);

        // Pastikan hanya admin atau pemilik shift yang bisa cetak
        if (Auth::user()->role !== 'admin' && $shift->user_id !== Auth::id()) {
            abort(403);
        }

        return view('pages.shift.laporan_58mm', compact('shift'));
    }
    
    public function destroy($id)
    {
        // 🛡️ Pastikan hanya Admin yang bisa mengakses fitur ini
        if (Auth::user()->role !== 'admin') {
            return abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk menghapus shift.');
        }

        $shift = Shift::findOrFail($id);
        $kodeShift = $shift->kode_shift ?? $shift->id;
        
        try {
            // Lakukan penghapusan Shift
            $shift->delete();

            return back()->with('success', "Shift $kodeShift berhasil dihapus, beserta transaksi terkait (jika ada).");

        } catch (\Exception $e) {
            return back()->with('error', "Gagal menghapus shift $kodeShift. Error: " . $e->getMessage());
        }
    }
}