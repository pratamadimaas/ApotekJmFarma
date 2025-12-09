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

    // Proses Buka Shift (buka)
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

        // Buat shift baru
        $shift = Shift::create([
            'user_id' => Auth::id(),
            'waktu_buka' => now(),
            'modal_awal' => $request->modal_awal,
            'status' => 'aktif'
        ]);

        return redirect()->route('penjualan.index')->with('success', 'Shift berhasil dibuka!');
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

        // Redirect ke halaman hasil tutup shift dengan data lengkap
        return redirect()->route('shift.hasil', $shift->id)
                        ->with('success', 'Shift berhasil ditutup!');
    }

    // Halaman Hasil Tutup Shift - Tampilkan hasil setelah tutup shift
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

        // Hitung detail per metode pembayaran
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
        // Eager load penjualan dan detail barangnya
        $shift = Shift::with(['user', 'penjualan' => function($q) {
            $q->with('detailPenjualan.barang');
        }])->findOrFail($id);

        // Pastikan hanya admin atau pemilik shift yang bisa lihat
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
}