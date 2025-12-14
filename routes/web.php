<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StokOpnameController;
use App\Http\Controllers\CabangController;
use App\Http\Controllers\CabangFilterController;

/*
|--------------------------------------------------------------------------
| 1. Public Routes (Guest)
|--------------------------------------------------------------------------
| Rute yang dapat diakses tanpa login (Halaman Login).
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| 2. Authenticated Routes (Semua User)
|--------------------------------------------------------------------------
| Rute yang diakses setelah login.
*/
Route::middleware('auth')->group(function () {

    // --- 2.1 Core/Auth & Profile ---
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']); // Alias

    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::get('/change-password', [UserController::class, 'showChangePasswordForm'])->name('change.password.form');
    Route::post('/change-password', [UserController::class, 'changePassword'])->name('change.password');

    // --- 2.2 Cabang Filter (Untuk Super Admin) ---
    Route::prefix('cabang-filter')->group(function () {
        Route::post('/set', [CabangFilterController::class, 'setCabangFilter'])->name('set-cabang-filter');
        Route::get('/get', [CabangFilterController::class, 'getCabangFilter'])->name('get-cabang-filter');
        Route::delete('/clear', [CabangFilterController::class, 'clearCabangFilter'])->name('clear-cabang-filter');
    });

    // --- 2.3 Barang (AJAX Helper) ---
    // Digunakan oleh Kasir (Penjualan) dan Admin (Pembelian)
    Route::prefix('barang')->name('barang.')->group(function () {
        Route::get('/search-kasir', [BarangController::class, 'cariBarang'])->name('search-kasir');
        Route::get('/harga-satuan', [BarangController::class, 'hargaSatuan'])->name('harga-satuan');
        Route::get('/by-barcode', [BarangController::class, 'getByBarcode'])->name('by-barcode');
        Route::get('/{id}/satuan', [BarangController::class, 'getSatuan'])->name('satuan');
        Route::get('/{id}/detail', [BarangController::class, 'getBarang'])->name('detail');
    });

    // --- 2.4 Shift Management (Kasir & Admin) ---
    Route::prefix('shift')->name('shift.')->group(function () {
        // Buka/Tutup
        Route::get('/buka', [ShiftController::class, 'formBuka'])->name('buka.form');
        Route::post('/buka', [ShiftController::class, 'buka'])->name('buka.store');
        Route::get('/tutup', [ShiftController::class, 'formTutup'])->name('tutup.form');
        Route::post('/tutup', [ShiftController::class, 'tutup'])->name('tutup.store');
        // Riwayat
        Route::get('/riwayat', [ShiftController::class, 'riwayat'])->name('riwayat');
        Route::get('/hasil/{id}', [ShiftController::class, 'hasil'])->name('hasil');
        Route::get('/riwayat/{id}', [ShiftController::class, 'detail'])->name('detail');
        Route::get('/riwayat/{id}/cetak', [ShiftController::class, 'cetakLaporan'])->name('cetakLaporan');
    });

    // --- 2.5 Penjualan / Kasir (Utama) ---
    Route::prefix('penjualan')->name('penjualan.')->group(function () {
        // Kasir POS
        Route::get('/', [PenjualanController::class, 'index'])->name('index');
        Route::post('/store', [PenjualanController::class, 'store'])->name('store');
        
        // ✅ FIXED: Search & Detail Barang untuk Kasir (AKTIFKAN!)
        Route::get('/search-barang', [PenjualanController::class, 'cariBarang'])->name('search-barang'); 
        Route::get('/barang/{id}', [PenjualanController::class, 'getBarang'])->name('get-barang');
        
        // Riwayat, Detail, & Print
        Route::get('/riwayat', [PenjualanController::class, 'riwayat'])->name('riwayat');
        Route::get('/{id}', [PenjualanController::class, 'show'])->name('show');
        Route::get('/print/{id}', [PenjualanController::class, 'printStruk'])->name('print');
        
        // Return
        Route::get('/cari-nota/{nomorNota}', [PenjualanController::class, 'cariNota'])->name('cari-nota');
        Route::post('/return', [PenjualanController::class, 'prosesReturn'])->name('return');
        
        // Laporan (Penjualan)
        Route::get('/laporan/return-barang', [PenjualanController::class, 'laporanReturn'])->name('laporan-return');
        Route::get('/laporan/invoice', [PenjualanController::class, 'laporanInvoice'])->name('laporan-invoice');
        Route::get('/laporan/invoice/export-excel', [PenjualanController::class, 'exportInvoiceExcel'])->name('laporan-invoice.export-excel');
    });

    // --- 2.6 Stok Opname ---
    Route::prefix('stokopname')->name('stokopname.')->group(function () {
        Route::get('/', [StokOpnameController::class, 'index'])->name('index');
        Route::get('/create', [StokOpnameController::class, 'create'])->name('create');
        Route::post('/scan', [StokOpnameController::class, 'scanBarcode'])->name('scan');
        Route::put('/item/{id}', [StokOpnameController::class, 'updateItem'])->name('update-item');
        Route::delete('/item/{id}', [StokOpnameController::class, 'deleteItem'])->name('delete-item');
        Route::post('/{id}/finalize', [StokOpnameController::class, 'finalize'])->name('finalize');
        Route::get('/{id}', [StokOpnameController::class, 'show'])->name('show');
    });

    // --- 2.7 Laporan Umum (Ringkasan) ---
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('/penjualan', [LaporanController::class, 'penjualan'])->name('penjualan');
        Route::get('/pembelian', [LaporanController::class, 'pembelian'])->name('pembelian');
        Route::get('/stok', [LaporanController::class, 'stok'])->name('stok');
        Route::get('/laba-rugi', [LaporanController::class, 'labaRugi'])->name('labaRugi');
        Route::get('/export-excel', [LaporanController::class, 'exportExcel'])->name('export-excel');
        Route::get('/export-pdf', [LaporanController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/kartu-stok', [LaporanController::class, 'kartuStok'])->name('kartuStok');
    });

    /*
    |--------------------------------------------------------------------------
    | 3. Routes untuk Super Admin & Admin Cabang
    |--------------------------------------------------------------------------
    | Menggunakan middleware 'admin_or_super'
    */
    Route::middleware(['admin_or_super'])->group(function () {

        // --- 3.1 Master Cabang (Super Admin Only) ---
        Route::resource('cabang', CabangController::class)->middleware('super_admin');
        Route::get('/cabang/api/aktif', [CabangController::class, 'getAktif'])->name('cabang.api.aktif');

        // --- 3.2 Master Barang (CRUD & Impor/Ekspor) ---
        Route::prefix('barang')->name('barang.')->group(function () {
            // Non-resource routes
            Route::get('/search', [BarangController::class, 'search'])->name('search');
            Route::get('/import', [BarangController::class, 'importForm'])->name('import-form');
            Route::post('/import-excel', [BarangController::class, 'importExcel'])->name('import-excel');
            Route::get('/download-template', [BarangController::class, 'downloadTemplate'])->name('download-template');
            Route::get('/export-excel', [BarangController::class, 'exportExcel'])->name('export-excel');
        });
        // Resource CRUD
        Route::resource('barang', BarangController::class)->except(['destroy']);
        Route::delete('barang/{barang}', [BarangController::class, 'destroy'])->name('barang.destroy');


        // --- 3.3 Pembelian (CRUD, Approve, & Barcode) ---
        Route::prefix('pembelian')->name('pembelian.')->group(function () {
            // List & CRUD
            Route::get('/', [PembelianController::class, 'index'])->name('index');
            Route::get('/create', [PembelianController::class, 'create'])->name('create');
            Route::get('/pending', [PembelianController::class, 'pending'])->name('pending');
            Route::post('/', [PembelianController::class, 'store'])->name('store');
            Route::get('/{id}', [PembelianController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [PembelianController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PembelianController::class, 'update'])->name('update');
            Route::delete('/{id}', [PembelianController::class, 'destroy'])->name('destroy');
            
            // Aksi
            Route::post('/{id}/approve', [PembelianController::class, 'approve'])->name('approve');

            // ✅ Barcode - Cetak barcode setelah pembelian
            Route::get('/{id}/cetak-barcode', [PembelianController::class, 'cetakBarcode'])->name('cetak-barcode');
            Route::post('/barcode/generate', [PembelianController::class, 'generateBarcode'])->name('barcode.generate');
            Route::post('/{id}/barcode/generate-all', [PembelianController::class, 'generateBarcodeAll'])->name('barcode.generate-all');
        });

        // --- 3.4 Supplier ---
        Route::resource('supplier', SupplierController::class);

        // --- 3.5 User Management ---
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');

        // --- 3.6 Shift (Admin Action) ---
        Route::delete('/shift/{id}', [ShiftController::class, 'destroy'])->name('shift.destroy');

        // --- 3.7 Settings ---
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::post('/update', [SettingController::class, 'update'])->name('update');
        });
    });
});