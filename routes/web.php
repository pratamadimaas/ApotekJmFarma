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

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Semua User)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Logout & Dashboard (Semua User)
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // --- Penjualan / Kasir (Utama) ---
    Route::prefix('penjualan')->name('penjualan.')->group(function () {
        Route::get('/', [PenjualanController::class, 'index'])->name('index');
        Route::post('/store', [PenjualanController::class, 'store'])->name('store');
        Route::get('/riwayat', [PenjualanController::class, 'riwayat'])->name('riwayat');
        Route::get('/{id}', [PenjualanController::class, 'show'])->name('show');
        Route::get('/print/{id}', [PenjualanController::class, 'printStruk'])->name('print');
        Route::get('/search-barang', [PenjualanController::class, 'cariBarang'])->name('search-barang');
        Route::get('/barang/{id}', [PenjualanController::class, 'getBarang'])->name('get-barang');
    });

    // --- Master Barang (CRUD Penuh) ---
    Route::get('/barang/search', [StokOpnameController::class, 'searchBarang'])->name('barang.search');
    Route::get('/barang/harga-satuan', [BarangController::class, 'hargaSatuan'])->name('barang.harga-satuan');
    Route::resource('barang', BarangController::class);
    Route::get('/barang/{id}/satuan', [BarangController::class, 'getSatuan'])->name('barang.satuan');
    
    // --- Shift Management (UPDATED - Blind Closing System) ---
    Route::prefix('shift')->name('shift.')->group(function () {
        // Buka Shift
        Route::get('/buka', [ShiftController::class, 'formBuka'])->name('buka.form');
        Route::post('/buka', [ShiftController::class, 'buka'])->name('buka.store');
        
        // Tutup Shift (Blind Closing - Tidak tampilkan data penjualan)
        Route::get('/tutup', [ShiftController::class, 'formTutup'])->name('tutup.form');
        Route::post('/tutup', [ShiftController::class, 'tutup'])->name('tutup.store');
        
        Route::get('/hasil/{id}', [ShiftController::class, 'hasil'])->name('hasil');
        
        // Riwayat & Detail Shift
        Route::get('/riwayat', [ShiftController::class, 'riwayat'])->name('riwayat');
        Route::get('/riwayat/{id}', [ShiftController::class, 'detail'])->name('detail');
        
        // ✅ UPDATED: Cetak Laporan 58mm (Thermal Printer Format)
        Route::get('/riwayat/{id}/cetak', [ShiftController::class, 'cetakLaporan'])->name('cetakLaporan');
    });

    // --- Laporan ---
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('/penjualan', [LaporanController::class, 'penjualan'])->name('penjualan');
        Route::get('/pembelian', [LaporanController::class, 'pembelian'])->name('pembelian');
        Route::get('/stok', [LaporanController::class, 'stok'])->name('stok');
        Route::get('/laba-rugi', [LaporanController::class, 'labaRugi'])->name('labaRugi');
        Route::get('/export-excel', [LaporanController::class, 'exportExcel'])->name('export-excel');
        Route::get('/export-pdf', [LaporanController::class, 'exportPdf'])->name('export-pdf');
    });

    // --- Profile & Self-Service Password Change (Semua User) ---
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');
    
    // Tambahan: Ubah Password Mandiri
    Route::get('/change-password', [UserController::class, 'showChangePasswordForm'])->name('change.password.form');
    Route::post('/change-password', [UserController::class, 'changePassword'])->name('change.password');

    // ✅ --- Stok Opname dengan Scan Barcode (SEMUA USER: Admin & Kasir) ---
    Route::prefix('stokopname')->name('stokopname.')->group(function () {
        // Halaman utama (daftar riwayat SO)
        Route::get('/', [StokOpnameController::class, 'index'])->name('index');
        
        // Halaman scan barcode (create sesi baru)
        Route::get('/create', [StokOpnameController::class, 'create'])->name('create');
        
        // API untuk scan barcode
        Route::post('/scan', [StokOpnameController::class, 'scanBarcode'])->name('scan');
        
        // Update item (stok fisik & expired date)
        Route::put('/item/{id}', [StokOpnameController::class, 'updateItem'])->name('update-item');
        
        // Delete item dari sesi
        Route::delete('/item/{id}', [StokOpnameController::class, 'deleteItem'])->name('delete-item');
        
        // Finalize SO (selesaikan dan update stok sistem)
        Route::post('/{id}/finalize', [StokOpnameController::class, 'finalize'])->name('finalize');
        
        // Lihat detail sesi SO yang sudah selesai
        Route::get('/{id}', [StokOpnameController::class, 'show'])->name('show');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Only Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin')->group(function () {

        // --- Pembelian ---
        Route::prefix('pembelian')->name('pembelian.')->group(function () {
            Route::get('/', [PembelianController::class, 'index'])->name('index');
            Route::get('/create', [PembelianController::class, 'create'])->name('create');
            Route::post('/store', [PembelianController::class, 'store'])->name('store');
            Route::get('/{id}', [PembelianController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [PembelianController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PembelianController::class, 'update'])->name('update');
            Route::delete('/{id}', [PembelianController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/approve', [PembelianController::class, 'approve'])->name('approve');
        });

        // --- Supplier ---
        Route::resource('supplier', SupplierController::class);

        // --- User Management (CRUD) ---
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');

        // --- Settings ---
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::post('/update', [SettingController::class, 'update'])->name('update');
        });
    });
});