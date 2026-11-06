<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClusterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MaterialReceiptController;
use App\Http\Controllers\RecapController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/rekapitulasi', [RecapController::class, 'index'])->name('recap.index');

    Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/laporan/cetak', [ReportController::class, 'printable'])->name('reports.print');
    Route::get('/laporan/excel', [ReportController::class, 'excel'])->name('reports.excel');
    Route::get('/laporan/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');

    // Material masuk: semua pengguna login boleh mencatat; hak ubah/hapus diatur MaterialReceiptPolicy.
    Route::get('material-masuk/opsi-material', [MaterialReceiptController::class, 'materials'])->name('receipts.materials');
    Route::resource('material-masuk', MaterialReceiptController::class)
        ->parameters(['material-masuk' => 'receipt'])->names('receipts')
        ->only(['index', 'store', 'edit', 'update', 'destroy']);

    // Semua pengguna login boleh melihat master data.
    Route::resource('material', MaterialController::class)
        ->parameters(['material' => 'material'])->names('materials')->only(['index']);
    Route::resource('lokasi-cluster', ClusterController::class)
        ->parameters(['lokasi-cluster' => 'cluster'])->names('clusters')->only(['index']);

    // Mengubah master data dan mengelola pengguna: admin saja.
    Route::middleware(EnsureRole::class.':admin')->group(function () {
        Route::resource('pengguna', UserController::class)
            ->parameters(['pengguna' => 'user'])->names('users')->except(['show', 'destroy']);

        Route::resource('material', MaterialController::class)
            ->parameters(['material' => 'material'])->names('materials')->only(['create', 'store', 'edit', 'update']);
        Route::patch('material/{material}/status', [MaterialController::class, 'toggleStatus'])->name('materials.status');

        Route::resource('lokasi-cluster', ClusterController::class)
            ->parameters(['lokasi-cluster' => 'cluster'])->names('clusters')->only(['create', 'store', 'edit', 'update']);
        Route::patch('lokasi-cluster/{cluster}/status', [ClusterController::class, 'toggleStatus'])->name('clusters.status');
    });
});
