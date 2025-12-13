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
                            ->where('status', 'open')
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
                            ->where('status', 'open')
                            ->whereNull('waktu_tutup')
                            ->first();

        if ($shiftAktif) {
            return back()->with('error', 'Anda sudah memiliki shift aktif!');
        }

        $maxRetries = 5;
        $attempt = 0;
        
        while ($attempt < $maxRetries) {
            try {
                $shift = DB::transaction(function () use ($request) {
                    $tanggalHariIni = now()->format('dmY');
                    
                    $lastShift = Shift::whereDate('waktu_buka', today())
                                      ->lockForUpdate()
                                      ->orderBy('id', 'desc')
                                      ->first();
                    
                    if ($lastShift && $lastShift->kode_shift) {
                        $parts = explode('-', $lastShift->kode_shift);
                        $lastNumber = isset($parts[0]) ? (int)$parts[0] : 0;
                        $nomorUrut = $lastNumber + 1;
                    } else {
                        $nomorUrut = 1;
                    }
                    
                    $kodeShift = str_pad($nomorUrut, 2, '0', STR_PAD_LEFT) . '-' . $tanggalHariIni;
                    
                    $exists = Shift::where('kode_shift', $kodeShift)->exists();
                    if ($exists) {
                        throw new \Exception('Duplicate shift code detected');
                    }
                    
                    // ✅ SESUAIKAN DENGAN MODEL: saldo_awal, status = 'open'
                    return Shift::create([
                        'user_id' => Auth::id(),
                        'kode_shift' => $kodeShift,
                        'waktu_buka' => now(),
                        'saldo_awal' => $request->modal_awal, // ✅ Gunakan saldo_awal
                        'status' => 'open' // ✅ Gunakan 'open'
                    ]);
                });

                return redirect()->route('penjualan.index')
                               ->with('success', 'Shift berhasil dibuka! Kode Shift: ' . $shift->kode_shift);

            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $attempt++;
                    if ($attempt >= $maxRetries) {
                        return back()->with('error', 'Gagal membuka shift setelah beberapa percobaan.')
                                   ->withInput();
                    }
                    usleep(rand(10000, 50000));
                    continue;
                }
                throw $e;
            } catch (\Exception $e) {
                $attempt++;
                if ($attempt >= $maxRetries) {
                    return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                               ->withInput();
                }
                usleep(rand(10000, 50000));
                continue;
            }
        }
        
        return back()->with('error', 'Gagal membuka shift. Silakan coba lagi.')
                   ->withInput();
    }

    // Halaman Tutup Shift (formTutup) - BLIND CLOSING
    public function formTutup()
    {
        $shift = Shift::where('user_id', Auth::id())
                     ->where('status', 'open')
                     ->whereNull('waktu_tutup')
                     ->first();

        if (!$shift) {
            return redirect()->route('shift.buka.form')->with('error', 'Tidak ada shift aktif!');
        }

        return view('pages.shift.tutup', compact('shift'));
    }

    // Proses Tutup Shift (tutup)
    public function tutup(Request $request)
    {
        $request->validate([
            'uang_fisik' => 'required|numeric|min:0',
            'catatan' => 'nullable|string'
        ]);

        $shift = Shift::where('user_id', Auth::id())
                     ->where('status', 'open')
                     ->whereNull('waktu_tutup')
                     ->firstOrFail();

        // Hitung total penjualan
        $totalTunai = Penjualan::where('shift_id', $shift->id)
                             ->where('metode_pembayaran', 'cash')
                             ->sum('grand_total');
                             
        $totalPenjualan = Penjualan::where('shift_id', $shift->id)->sum('grand_total');
        
        $nonTunai = Penjualan::where('shift_id', $shift->id)
                            ->whereIn('metode_pembayaran', ['debit', 'credit', 'qris', 'transfer'])
                            ->sum('grand_total');

        // ✅ GUNAKAN saldo_awal dari model
        $uangSeharusnya = $shift->saldo_awal + $totalTunai;
        $selisih = $request->uang_fisik - $uangSeharusnya;

        // ✅ UPDATE SESUAI MODEL: saldo_akhir, keterangan, status = 'closed'
        $shift->update([
            'waktu_tutup' => now(),
            'total_penjualan' => $totalPenjualan,
            'total_cash' => $totalTunai,
            'total_non_cash' => $nonTunai,
            'saldo_akhir' => $request->uang_fisik, // ✅ Gunakan saldo_akhir
            'selisih' => $selisih,
            'keterangan' => $request->catatan, // ✅ Gunakan keterangan
            'status' => 'closed' // ✅ Gunakan 'closed'
        ]);

        return redirect()->route('shift.hasil', $shift->id)
                         ->with('success', 'Shift berhasil ditutup!');
    }

    public function hasil($id)
    {
        $shift = Shift::with('user')->findOrFail($id);

        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'super_admin' && $shift->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$shift->waktu_tutup) {
            return redirect()->route('shift.tutup.form')->with('error', 'Shift belum ditutup!');
        }

        // ✅ GUNAKAN total_cash dan total_non_cash dari database
        $tunai = $shift->total_cash ?? 0;
        $nonTunai = $shift->total_non_cash ?? 0;
        $uangSeharusnya = $shift->saldo_awal + $tunai;

        // Detail per metode pembayaran
        $detailMetode = Penjualan::where('shift_id', $shift->id)
                                ->select('metode_pembayaran', DB::raw('COUNT(*) as jumlah'), DB::raw('SUM(grand_total) as total'))
                                ->groupBy('metode_pembayaran')
                                ->get();

        return view('pages.shift.hasil', compact('shift', 'tunai', 'nonTunai', 'uangSeharusnya', 'detailMetode'));
    }

    // Riwayat Shift (riwayat)
    public function riwayat(Request $request)
    {
        $query = Shift::with('user')
                     ->where('status', 'closed')
                     ->whereNotNull('waktu_tutup');

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('waktu_buka', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('waktu_buka', '<=', $request->tanggal_sampai);
        }

        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'super_admin') {
            $query->where('user_id', Auth::id());
        }

        $shifts = $query->orderBy('waktu_tutup', 'desc')->paginate(20);

        return view('pages.shift.riwayat', compact('shifts'));
    }

    // Detail Shift (detail) - ✅ PERBAIKAN EAGER LOADING
    public function detail($id)
    {
        // ✅ FIX: Load relasi dengan benar menggunakan detailPenjualan (sesuai nama relasi di Model)
        $shift = Shift::with([
            'user',
            'penjualan' => function($q) {
                $q->with(['detailPenjualan.barang']); // ✅ Gunakan detailPenjualan, bukan details
            }
        ])->findOrFail($id);

        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'super_admin' && $shift->user_id !== Auth::id()) {
            abort(403);
        }

        // Statistik detail shift
        $statistik = [
            'total_penjualan' => $shift->penjualan->sum('grand_total'),
            'jumlah_transaksi' => $shift->penjualan->count(),
            'rata_rata_transaksi' => $shift->penjualan->count() > 0 ? $shift->penjualan->sum('grand_total') / $shift->penjualan->count() : 0,
        ];

        $metodePembayaran = $shift->penjualan->groupBy('metode_pembayaran')->map(function($items, $metode) {
            return [
                'metode' => $metode ?: 'cash',
                'jumlah' => $items->count(),
                'total' => $items->sum('grand_total')
            ];
        });

        return view('pages.shift.detail', compact('shift', 'statistik', 'metodePembayaran'));
    }

    // Cetak Laporan Shift ukuran 58mm (cetakLaporan)
    public function cetakLaporan($id)
    {
        $shift = Shift::with(['user', 'penjualan'])->findOrFail($id);

        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'super_admin' && $shift->user_id !== Auth::id()) {
            abort(403);
        }

        $tunai = $shift->total_cash ?? 0;
        $nonTunai = $shift->total_non_cash ?? 0;

        return view('pages.shift.laporan_58mm', compact('shift', 'tunai', 'nonTunai'));
    }
    
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'super_admin') {
            return abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk menghapus shift.');
        }

        $shift = Shift::findOrFail($id);
        $kodeShift = $shift->kode_shift ?? $shift->id;
        
        try {
            $shift->delete();
            return back()->with('success', "Shift $kodeShift berhasil dihapus.");
        } catch (\Exception $e) {
            return back()->with('error', "Gagal menghapus shift $kodeShift. Error: " . $e->getMessage());
        }
    }
}