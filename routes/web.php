<?php

use App\Http\Controllers\Admin\AssignmentController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\CorrectionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\PermitController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RuleController;
use App\Http\Controllers\Admin\SantriController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Officer\ScanController;
use App\Http\Controllers\Parent\DashboardController as ParentDashboardController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Logout
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Officer Routes (Petugas & Pengurus)
Route::middleware(['auth', 'role:petugas,pengurus'])->prefix('officer')->as('officer.')->group(function () {
    Route::get('/scan', [ScanController::class, 'index'])->name('scan');
    Route::post('/qr/resolve', [ScanController::class, 'resolve'])->name('qr.resolve');
    Route::post('/events', [ScanController::class, 'record'])->name('events.record');
    Route::post('/manual-events', [ScanController::class, 'recordManual'])->name('manual.record');
    Route::post('/corrections', [ScanController::class, 'requestCorrection'])->name('corrections.request');
});

// Admin Routes (Pengurus)
Route::middleware(['auth', 'role:pengurus'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/alerts/{id}/followup', [AdminDashboardController::class, 'followupAlert'])->name('alerts.followup');

    // Users & Accounts
    Route::resource('users', UserController::class)->except(['destroy', 'show']);
    Route::patch('/users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');

    // Santri & QR
    Route::resource('santri', SantriController::class)->except(['destroy']);
    Route::post('/santri/{santri}/regenerate-qr', [SantriController::class, 'regenerateQr'])->name('santri.regenerate-qr');
    Route::get('/santri/{santri}/card', [SantriController::class, 'printCard'])->name('santri.card');

    // Master Data & Assignments
    Route::get('/master-data', [MasterDataController::class, 'index'])->name('master.index');
    Route::post('/master-data/campuses', [MasterDataController::class, 'storeCampus'])->name('master.campus.store');
    Route::post('/master-data/locations', [MasterDataController::class, 'storeLocation'])->name('master.location.store');

    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::patch('/assignments/{assignment}/toggle', [AssignmentController::class, 'toggle'])->name('assignments.toggle');

    // Permits (Izin)
    Route::get('/permits', [PermitController::class, 'index'])->name('permits.index');
    Route::get('/permits/create', [PermitController::class, 'create'])->name('permits.create');
    Route::post('/permits', [PermitController::class, 'store'])->name('permits.store');
    Route::post('/permits/{permit}/approve', [PermitController::class, 'approve'])->name('permits.approve');
    Route::post('/permits/{permit}/reject', [PermitController::class, 'reject'])->name('permits.reject');

    // Holiday & Rule Versions
    Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
    Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');

    Route::get('/rules', [RuleController::class, 'index'])->name('rules.index');
    Route::patch('/rules', [RuleController::class, 'update'])->name('rules.update');

    // Corrections
    Route::get('/corrections', [CorrectionController::class, 'index'])->name('corrections.index');
    Route::post('/corrections/{correction}/approve', [CorrectionController::class, 'approve'])->name('corrections.approve');
    Route::post('/corrections/{correction}/reject', [CorrectionController::class, 'reject'])->name('corrections.reject');

    // Reports & Exports
    Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
    Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('/reports/export-csv', [ReportController::class, 'exportDailyCsv'])->name('reports.export-csv');
    Route::get('/reports/print-daily', [ReportController::class, 'printDaily'])->name('reports.print-daily');

    // Audit Logs
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
});

// Parent / Wali Routes
Route::middleware(['auth', 'role:wali,pengurus'])->prefix('parent')->as('parent.')->group(function () {
    Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');
    Route::post('/claim-santri', [ParentDashboardController::class, 'claimSantri'])->name('claim-santri');
});
