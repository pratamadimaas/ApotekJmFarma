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
| Public Routes (Guest)
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

    // --- Core/Auth ---
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']); // Alias

    // --- Profile & Password ---
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::get('/change-password', [UserController::class, 'showChangePasswordForm'])->name('change.password.form');
    Route::post('/change-password', [UserController::class, 'changePassword'])->name('change.password');

    // --- Cabang Filter (Untuk Super Admin melihat data cabang lain) ---
    Route::prefix('cabang-filter')->group(function () {
        Route::post('/set', [CabangFilterController::class, 'setCabangFilter'])->name('set-cabang-filter');
        Route::get('/get', [CabangFilterController::class, 'getCabangFilter'])->name('get-cabang-filter');
        Route::delete('/clear', [CabangFilterController::class, 'clearCabangFilter'])->name('clear-cabang-filter');
    });

    // --- Shift Management (Kasir & Admin) ---
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
    
    // --- Penjualan / Kasir (Utama) ---
    // Rute yang didefinisikan di sini akan menggantikan blok duplikat di bawah.
    Route::prefix('penjualan')->name('penjualan.')->group(function () {
        // Kasir POS
        Route::get('/', [PenjualanController::class, 'index'])->name('index');
        Route::post('/store', [PenjualanController::class, 'store'])->name('store');
        
        // Search & Detail Barang (AJAX)
        Route::get('/search-barang', [PenjualanController::class, 'cariBarang'])->name('search-barang');
        Route::get('/barang/{id}', [PenjualanController::class, 'getBarang'])->name('get-barang');
        
        // Riwayat, Detail, & Print
        Route::get('/riwayat', [PenjualanController::class, 'riwayat'])->name('riwayat');
        Route::get('/{id}', [PenjualanController::class, 'show'])->name('show');
        Route::get('/print/{id}', [PenjualanController::class, 'printStruk'])->name('print');
        
        // Return
        Route::get('/cari-nota/{nomorNota}', [PenjualanController::class, 'cariNota'])->name('cari-nota');
        Route::post('/return', [PenjualanController::class, 'prosesReturn'])->name('return');
        
        // ✅ LAPORAN BARU (TERPISAH PER CABANG)
    Route::get('/laporan/return-barang', [PenjualanController::class, 'laporanReturn'])->name('laporan-return');
    Route::get('/laporan/invoice', [PenjualanController::class, 'laporanInvoice'])->name('laporan-invoice');
    Route::get('/laporan/invoice/export-excel', [PenjualanController::class, 'exportInvoiceExcel'])->name('laporan-invoice.export-excel');
});
    });

    // --- Barang (AJAX untuk Kasir/Pembelian) ---
    Route::prefix('barang')->name('barang.')->group(function () {
        Route::get('/search-kasir', [BarangController::class, 'cariBarang'])->name('search-kasir');
        Route::get('/harga-satuan', [BarangController::class, 'hargaSatuan'])->name('harga-satuan');
        Route::get('/by-barcode', [BarangController::class, 'getByBarcode'])->name('by-barcode');
        Route::get('/{id}/satuan', [BarangController::class, 'getSatuan'])->name('satuan');
        Route::get('/{id}/detail', [BarangController::class, 'getBarang'])->name('detail');
    });

    // --- Laporan Umum ---
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

    // --- Stok Opname ---
    Route::prefix('stokopname')->name('stokopname.')->group(function () {
        Route::get('/', [StokOpnameController::class, 'index'])->name('index');
        Route::get('/create', [StokOpnameController::class, 'create'])->name('create');
        Route::post('/scan', [StokOpnameController::class, 'scanBarcode'])->name('scan');
        Route::put('/item/{id}', [StokOpnameController::class, 'updateItem'])->name('update-item');
        Route::delete('/item/{id}', [StokOpnameController::class, 'deleteItem'])->name('delete-item');
        Route::post('/{id}/finalize', [StokOpnameController::class, 'finalize'])->name('finalize');
        Route::get('/{id}', [StokOpnameController::class, 'show'])->name('show');
    });


    /*
    |--------------------------------------------------------------------------
    | Routes untuk Super Admin & Admin Cabang (Middleware: admin_or_super)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin_or_super'])->group(function () {

        // --- Master Cabang (Super Admin Only) ---
        Route::resource('cabang', CabangController::class)->middleware('super_admin');
        Route::get('/cabang/api/aktif', [CabangController::class, 'getAktif'])->name('cabang.api.aktif');

        // --- Master Barang (CRUD & Impor/Ekspor) ---
        Route::prefix('barang')->name('barang.')->group(function () {
            Route::get('/search', [BarangController::class, 'search'])->name('search');
            Route::get('/import', [BarangController::class, 'importForm'])->name('import-form');
            Route::post('/import-excel', [BarangController::class, 'importExcel'])->name('import-excel');
            Route::get('/download-template', [BarangController::class, 'downloadTemplate'])->name('download-template');
            Route::get('/export-excel', [BarangController::class, 'exportExcel'])->name('export-excel');
        });
        Route::resource('barang', BarangController::class);

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

        // --- User Management ---
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');

        // --- Shift (Admin Action) ---
        Route::delete('/shift/{id}', [ShiftController::class, 'destroy'])->name('shift.destroy');
        
        // --- Settings ---
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::post('/update', [SettingController::class, 'update'])->name('update');
        });
    });