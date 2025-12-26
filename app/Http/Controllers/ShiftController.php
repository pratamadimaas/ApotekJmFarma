<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Penjualan;
use App\Traits\CabangFilterTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShiftController extends Controller
{
    use CabangFilterTrait;
    
    // Halaman Buka Shift (formBuka)
    public function formBuka()
    {
        $user = Auth::user();
        $cabangId = $this->getActiveCabangId();
        
        Log::info('Shift FormBuka - Debug', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'user_cabang_id' => $user->cabang_id,
            'active_cabang_id' => $cabangId
        ]);

        // ✅ Validasi: Semua user (termasuk Super Admin) WAJIB punya cabang aktif
        if (!$cabangId) {
            $errorMessage = $user->isSuperAdmin() 
                ? 'Silakan pilih cabang terlebih dahulu sebelum membuka shift.'
                : 'Anda belum terdaftar di cabang manapun. Hubungi administrator.';
                
            return redirect()->back()
                ->with('error', $errorMessage);
        }

        // ✅ Cek shift aktif berdasarkan user DAN cabang
        $shiftAktif = Shift::where('user_id', $user->id)
                            ->where('cabang_id', $cabangId)
                            ->where('status', 'open')
                            ->whereNull('waktu_tutup')
                            ->first();

        if ($shiftAktif) {
            Log::info('Shift FormBuka - Existing Active Shift Found', [
                'shift_id' => $shiftAktif->id,
                'kode_shift' => $shiftAktif->kode_shift,
                'cabang_id' => $shiftAktif->cabang_id
            ]);
            
            return redirect()->route('penjualan.index')
                ->with('info', 'Anda sudah memiliki shift aktif: ' . $shiftAktif->kode_shift);
        }

        return view('pages.shift.buka');
    }

    // Proses Buka Shift (buka)
    public function buka(Request $request)
    {
        $request->validate([
            'modal_awal' => 'required|numeric|min:0'
        ]);

        $user = Auth::user();
        $cabangId = $this->getActiveCabangId();

        Log::info('Shift Buka - Debug', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'user_cabang_id' => $user->cabang_id,
            'active_cabang_id' => $cabangId,
            'modal_awal' => $request->modal_awal
        ]);

        // ✅ Validasi cabang WAJIB ada
        if (!$cabangId) {
            Log::error('Shift Buka Failed - No cabang_id', [
                'user_id' => $user->id,
                'user_role' => $user->role
            ]);
            
            $errorMessage = $user->isSuperAdmin() 
                ? 'Silakan pilih cabang terlebih dahulu!'
                : 'Anda belum terdaftar di cabang manapun. Hubungi administrator.';
                
            return back()->with('error', $errorMessage)->withInput();
        }

        // ✅ Cek shift aktif berdasarkan user DAN cabang
        $shiftAktif = Shift::where('user_id', $user->id)
                            ->where('cabang_id', $cabangId)
                            ->where('status', 'open')
                            ->whereNull('waktu_tutup')
                            ->first();

        if ($shiftAktif) {
            Log::info('Shift Buka - Already Has Active Shift', [
                'shift_id' => $shiftAktif->id,
                'kode_shift' => $shiftAktif->kode_shift
            ]);
            
            return redirect()->route('penjualan.index')
                ->with('info', 'Anda sudah memiliki shift aktif: ' . $shiftAktif->kode_shift);
        }

        // ✅ BUAT SHIFT BARU dengan Transaction
        DB::beginTransaction();
        try {
            $tanggalHariIni = now()->format('dmY');
            
            // ✅ Lock untuk mendapatkan nomor urut terakhir untuk USER INI di cabang ini
            $lastShift = Shift::where('cabang_id', $cabangId)
                              ->where('user_id', $user->id)
                              ->whereDate('waktu_buka', today())
                              ->lockForUpdate()
                              ->orderBy('id', 'desc')
                              ->first();
            
            // ✅ Hitung nomor urut
            $nomorUrut = 1;
            if ($lastShift && $lastShift->kode_shift) {
                // Ekstrak nomor urut dari format: XX-UY-DDMMYYYY
                if (preg_match('/^(\d+)-U\d+-/', $lastShift->kode_shift, $matches)) {
                    $nomorUrut = (int)$matches[1] + 1;
                }
            }
            
            // ✅ FORMAT BARU: 01-U4-26122025 (nomor urut - user ID - tanggal)
            $kodeShift = str_pad($nomorUrut, 2, '0', STR_PAD_LEFT) . '-U' . $user->id . '-' . $tanggalHariIni;
            
            Log::info('Shift Buka - Creating New Shift', [
                'user_id' => $user->id,
                'cabang_id' => $cabangId,
                'kode_shift' => $kodeShift,
                'last_shift_id' => $lastShift ? $lastShift->id : null,
                'nomor_urut' => $nomorUrut
            ]);
            
            // ✅ Create shift baru
            $shift = Shift::create([
                'user_id' => $user->id,
                'cabang_id' => $cabangId,
                'kode_shift' => $kodeShift,
                'waktu_buka' => now(),
                'saldo_awal' => $request->modal_awal,
                'status' => 'open'
            ]);
            
            DB::commit();
            
            Log::info('Shift Buka - Success', [
                'shift_id' => $shift->id,
                'kode_shift' => $shift->kode_shift,
                'cabang_id' => $shift->cabang_id,
                'user_id' => $shift->user_id
            ]);
            
            return redirect()->route('penjualan.index')
                ->with('success', 'Shift berhasil dibuka! Kode: ' . $shift->kode_shift);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            
            Log::error('Shift Buka - Database Error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'user_id' => $user->id,
                'cabang_id' => $cabangId
            ]);
            
            return back()->with('error', 'Gagal membuka shift. Error database: ' . $e->getMessage())
                       ->withInput();
                       
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Shift Buka - Unexpected Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'cabang_id' => $cabangId
            ]);
            
            return back()->with('error', 'Gagal membuka shift: ' . $e->getMessage())
                       ->withInput();
        }
    }

    // Halaman Tutup Shift (formTutup)
    public function formTutup()
    {
        $user = Auth::user();
        $cabangId = $this->getActiveCabangId();
        
        $shift = Shift::where('user_id', $user->id)
                     ->where('cabang_id', $cabangId)
                     ->where('status', 'open')
                     ->whereNull('waktu_tutup')
                     ->first();

        if (!$shift) {
            return redirect()->route('shift.buka.form')
                ->with('error', 'Tidak ada shift aktif!');
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

        $user = Auth::user();
        $cabangId = $this->getActiveCabangId();

        // ✅ Validasi sesi shift: cek user DAN cabang
        $shift = Shift::where('user_id', $user->id)
                     ->where('cabang_id', $cabangId)
                     ->where('status', 'open')
                     ->whereNull('waktu_tutup')
                     ->first();

        if (!$shift) {
            Log::warning('Shift Tutup - No Active Shift Found', [
                'user_id' => $user->id,
                'cabang_id' => $cabangId
            ]);
            
            return redirect()->route('shift.buka.form')
                ->with('error', 'Tidak ada shift aktif di cabang ini!');
        }

        // Hitung total penjualan
        $totalTunai = Penjualan::where('shift_id', $shift->id)
                             ->where('metode_pembayaran', 'cash')
                             ->sum('grand_total');
                             
        $totalPenjualan = Penjualan::where('shift_id', $shift->id)
                                  ->sum('grand_total');
        
        $nonTunai = Penjualan::where('shift_id', $shift->id)
                            ->whereIn('metode_pembayaran', ['debit', 'credit', 'qris', 'transfer'])
                            ->sum('grand_total');

        $uangSeharusnya = $shift->saldo_awal + $totalTunai;
        $selisih = $request->uang_fisik - $uangSeharusnya;

        $shift->update([
            'waktu_tutup' => now(),
            'total_penjualan' => $totalPenjualan,
            'total_cash' => $totalTunai,
            'total_non_cash' => $nonTunai,
            'saldo_akhir' => $request->uang_fisik,
            'selisih' => $selisih,
            'keterangan' => $request->catatan,
            'status' => 'closed'
        ]);

        Log::info('Shift Tutup - Success', [
            'shift_id' => $shift->id,
            'kode_shift' => $shift->kode_shift,
            'cabang_id' => $shift->cabang_id
        ]);

        return redirect()->route('shift.hasil', $shift->id)
                         ->with('success', 'Shift berhasil ditutup!');
    }

    public function hasil($id)
    {
        $shift = Shift::with(['user', 'cabang'])->findOrFail($id);

        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'super_admin' && $shift->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$shift->waktu_tutup) {
            return redirect()->route('shift.tutup.form')
                ->with('error', 'Shift belum ditutup!');
        }

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

    // ✅ Riwayat Shift dengan Filter Cabang (sama seperti StokOpname)
    public function riwayat(Request $request)
    {
        $user = Auth::user();
        $cabangId = $this->getActiveCabangId();
        
        Log::info('Shift Riwayat - Debug', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_cabang_id' => $user->cabang_id,
            'active_cabang_id' => $cabangId,
            'filters' => $request->only(['tanggal_dari', 'tanggal_sampai'])
        ]);

        $query = Shift::with(['user', 'cabang'])
            ->where('status', 'closed')
            ->whereNotNull('waktu_tutup');

        // ✅ Filter berdasarkan cabang (sama seperti StokOpname)
        if ($user->isSuperAdmin()) {
            // Super Admin: filter berdasarkan cabang yang dipilih
            if ($cabangId) {
                $query->where('cabang_id', $cabangId);
                Log::info('Shift Riwayat - Super Admin Filter', ['cabang_id' => $cabangId]);
            } else {
                Log::warning('Shift Riwayat - Super Admin tanpa cabang dipilih, menampilkan semua');
            }
        } else {
            // User biasa: filter berdasarkan cabang user
            if ($cabangId) {
                $query->where('cabang_id', $cabangId);
                Log::info('Shift Riwayat - User Filter', ['cabang_id' => $cabangId]);
            } else {
                // FALLBACK: Jika user tidak punya cabang, tampilkan hanya miliknya sendiri
                $query->where('user_id', $user->id);
                Log::warning('Shift Riwayat - User tanpa cabang, filter by user_id');
            }
        }

        // ✅ Filter Tanggal
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('waktu_buka', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('waktu_buka', '<=', $request->tanggal_sampai);
        }

        // ✅ Filter User (untuk non-admin/super_admin)
        if (!$user->isSuperAdmin() && $user->role !== 'admin') {
            $query->where('user_id', $user->id);
            Log::info('Shift Riwayat - Filter user diterapkan', ['user_id' => $user->id]);
        }

        $shifts = $query->orderBy('waktu_tutup', 'desc')->paginate(20);

        Log::info('Shift Riwayat - Result Count', [
            'total' => $shifts->total(),
            'filters_applied' => $request->hasAny(['tanggal_dari', 'tanggal_sampai'])
        ]);

        return view('pages.shift.riwayat', compact('shifts'));
    }

    // ✅ Detail Shift dengan validasi cabang (sama seperti StokOpname)
    public function detail($id)
    {
        $user = Auth::user();
        $cabangId = $this->getActiveCabangId();

        $shift = Shift::with([
            'user',
            'cabang',
            'penjualan' => function($q) {
                $q->with(['detailPenjualan.barang']);
            }
        ])->findOrFail($id);

        // ✅ Validasi akses berdasarkan cabang
        if (!$user->isSuperAdmin()) {
            if ($shift->cabang_id !== $cabangId) {
                Log::warning('Shift Detail - Access Denied', [
                    'user_id' => $user->id,
                    'shift_cabang_id' => $shift->cabang_id,
                    'user_active_cabang_id' => $cabangId
                ]);
                
                abort(403, 'Anda tidak memiliki akses ke shift ini (cabang tidak sesuai).');
            }
        }

        // Statistik detail shift
        $statistik = [
            'total_penjualan' => $shift->penjualan->sum('grand_total'),
            'jumlah_transaksi' => $shift->penjualan->count(),
            'rata_rata_transaksi' => $shift->penjualan->count() > 0 
                ? $shift->penjualan->sum('grand_total') / $shift->penjualan->count() 
                : 0,
            'cabang_name' => $shift->cabang 
                ? $shift->cabang->nama_cabang 
                : 'Tidak ada cabang'
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
        $shift = Shift::with(['user', 'cabang', 'penjualan'])->findOrFail($id);

        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'super_admin' && $shift->user_id !== Auth::id()) {
            abort(403);
        }

        $tunai = $shift->total_cash ?? 0;
        $nonTunai = $shift->total_non_cash ?? 0;

        return view('pages.shift.laporan_58mm', compact('shift', 'tunai', 'nonTunai'));
    }
    
    public function destroy($id)
    {
        $user = Auth::user();
        $cabangId = $this->getActiveCabangId();

        if ($user->role !== 'admin' && $user->role !== 'super_admin') {
            return abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk menghapus shift.');
        }

        $shift = Shift::where('id', $id)
            ->where('cabang_id', $cabangId)
            ->first();

        if (!$shift) {
            return back()->with('error', 'Shift tidak ditemukan atau bukan milik cabang Anda!');
        }

        $kodeShift = $shift->kode_shift ?? $shift->id;
        
        try {
            $shift->delete();
            
            Log::info('Shift Deleted', [
                'shift_id' => $id,
                'kode_shift' => $kodeShift,
                'cabang_id' => $shift->cabang_id,
                'deleted_by' => $user->id
            ]);
            
            return back()->with('success', "Shift $kodeShift berhasil dihapus.");
        } catch (\Exception $e) {
            Log::error('Shift Delete Error', [
                'shift_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', "Gagal menghapus shift $kodeShift. Error: " . $e->getMessage());
        }
    }
}