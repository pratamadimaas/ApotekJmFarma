<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    // Halaman Buka Shift (formBuka) - KODE AMAN
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

    // Proses Buka Shift (buka) - KODE AMAN
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

    // Halaman Tutup Shift (formTutup) - KOREKSI LOGIKA TUNAI
    public function formTutup()
    {
        $shift = Shift::where('user_id', Auth::id())
                     ->whereNull('waktu_tutup')
                     ->first();

        if (!$shift) {
            return redirect()->route('shift.buka.form')->with('error', 'Tidak ada shift aktif!');
        }

        // Penjualan dalam shift ini
        $penjualanShift = Penjualan::where('shift_id', $shift->id);

        // Hitung total penjualan (Grand Total)
        $totalPenjualan = $penjualanShift->sum('grand_total');
        $jumlahTransaksi = $penjualanShift->count();

        // Hitung per metode pembayaran (Menggunakan grand_total/total penjualan)
        $tunai = (clone $penjualanShift)->where('metode_pembayaran', 'cash')->sum('grand_total');

        // Non-tunai mencakup debit, kredit, qris
        $nonTunai = (clone $penjualanShift)->whereIn('metode_pembayaran', ['debit', 'credit', 'qris'])->sum('grand_total');
        
        // Uang Tunai yang seharusnya ada di laci (Modal Awal + Total Penjualan Tunai)
        $uangDilaci = $shift->modal_awal + $tunai;

        return view('pages.shift.tutup', compact('shift', 'totalPenjualan', 'jumlahTransaksi', 'tunai', 'nonTunai', 'uangDilaci'));
    }

    // Proses Tutup Shift (tutup) - KOREKSI LOGIKA TUNAI
    public function tutup(Request $request)
    {
        $request->validate([
            'uang_fisik' => 'required|numeric|min:0',
            'catatan' => 'nullable|string'
        ]);

        $shift = Shift::where('user_id', Auth::id())
                     ->whereNull('waktu_tutup')
                     ->firstOrFail();

        // Hitung total penjualan tunai (Grand Total dari transaksi tunai)
        $totalTunai = Penjualan::where('shift_id', $shift->id)
                             ->where('metode_pembayaran', 'cash')
                             ->sum('grand_total');
                             
        $totalPenjualan = Penjualan::where('shift_id', $shift->id)->sum('grand_total');
        $jumlahTransaksi = Penjualan::where('shift_id', $shift->id)->count();

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

        return redirect()->route('dashboard')->with('success', 'Shift berhasil ditutup!');
    }

    // Riwayat Shift (riwayat) - KODE AMAN (Mengirimkan $shifts)
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

        return view('pages.shift.riwayat', compact('shifts')); // ✅ Mengirimkan $shifts
    }

    // Detail Shift (detail) - KOREKSI EAGER LOADING
    public function detail($id)
    {
        // ✅ Eager load penjualan dan detail barangnya
        $shift = Shift::with(['user', 'penjualan' => function($q) {
            $q->with('detailPenjualan.barang'); // Memuat detail penjualan dan barangnya
        }])->findOrFail($id);

        // Pastikan hanya admin atau pemilik shift yang bisa lihat
        if (Auth::user()->role !== 'admin' && $shift->user_id !== Auth::id()) {
            abort(403);
        }

        return view('pages.shift.detail', compact('shift'));
    }

    // Cetak Laporan Shift (cetakLaporan) - KODE AMAN
    public function cetakLaporan($id)
    {
        $shift = Shift::with(['user', 'penjualan'])->findOrFail($id);

        // Pastikan hanya admin atau pemilik shift yang bisa cetak
        if (Auth::user()->role !== 'admin' && $shift->user_id !== Auth::id()) {
            abort(403);
        }

        return view('pages.shift.laporan', compact('shift'));
    }
}