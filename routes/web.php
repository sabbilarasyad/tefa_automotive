<?php

use App\Http\Controllers\Admin\PkbController;
use App\Http\Controllers\Admin\CashierController;
use App\Http\Controllers\Admin\PkbProgressController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\ServiceHistoryController;
use App\Http\Controllers\Admin\ReminderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SparepartController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\ServiceController as PublicServiceController;
use App\Http\Controllers\Public\GalleryController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Auth\WebLoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [WebLoginController::class, 'create'])->name('login');
    Route::post('/login', [WebLoginController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'active'])->get('/mechanic/tasks', [PkbProgressController::class, 'mechanicTasks'])->name('mechanic.tasks');

Route::middleware(['auth', 'active'])->prefix('cashier')->name('cashier.')->group(function (): void {
    Route::get('/', [CashierController::class, 'index'])->name('index');
    Route::get('/invoices/{invoice}', [CashierController::class, 'show'])->name('invoices.show');
    Route::post('/invoices/{invoice}/payments', [CashierController::class, 'processPayment'])->name('invoices.payments.store');
    Route::get('/invoices/{invoice}/receipt', [CashierController::class, 'receipt'])->name('invoices.receipt');
    Route::get('/invoices/{invoice}/receipt.pdf', [CashierController::class, 'pdf'])->name('invoices.pdf');
});

Route::middleware(['auth', 'active'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::post('/logout', [WebLoginController::class, 'destroy'])->name('logout');
    Route::get('/pkb/progress', [PkbProgressController::class, 'index'])->name('pkb.progress.index');
    Route::get('/pkb', [PkbController::class, 'index'])->name('pkb.index');
    Route::get('/pkb/create', [PkbController::class, 'create'])->name('pkb.create');
    Route::post('/pkb', [PkbController::class, 'store'])->name('pkb.store');
    Route::get('/pkb/{pkb}', [PkbController::class, 'show'])->name('pkb.show');
    Route::get('/pkb/{pkb}/progress', [PkbProgressController::class, 'show'])->name('pkb.progress.show');
    Route::post('/pkb/{pkb}/start', [PkbProgressController::class, 'start'])->name('pkb.progress.start');
    Route::patch('/pkb/{pkb}/spareparts', [PkbProgressController::class, 'updateSpareparts'])->name('pkb.progress.spareparts');
    Route::post('/pkb/{pkb}/documentations', [PkbProgressController::class, 'uploadDocumentation'])->name('pkb.progress.documentations.store');
    Route::delete('/pkb/{pkb}/documentations', [PkbProgressController::class, 'deleteDocumentation'])->name('pkb.progress.documentations.destroy');
    Route::post('/pkb/{pkb}/complete', [PkbProgressController::class, 'complete'])->name('pkb.progress.complete');
    Route::patch('/pkb/{pkb}', [PkbController::class, 'update'])->name('pkb.update');
    Route::post('/pkb/{pkb}/assign-mechanic', [PkbController::class, 'assignMechanic'])->name('pkb.assign-mechanic');
    Route::post('/pkb/{pkb}/cancel', [PkbController::class, 'cancel'])->name('pkb.cancel');
    Route::post('/pkb/{pkb}/force-cancel', [PkbController::class, 'forceCancel'])->name('pkb.force-cancel');
    Route::get('/pkb/{pkb}/print', [PkbController::class, 'print'])->name('pkb.print');

    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::post('/stock/{sparepart}/restock', [StockController::class, 'restock'])->name('stock.restock');
    Route::get('/stock/{sparepart}/history', [StockController::class, 'history'])->name('stock.history');

    Route::get('/service-history', [ServiceHistoryController::class, 'index'])->name('service-history.index');
    Route::get('/service-history/{serviceHistory}', [ServiceHistoryController::class, 'show'])->name('service-history.show');

    Route::get('/reminder', [ReminderController::class, 'index'])->name('reminder.index');
    Route::post('/reminder/{reminder}/send', [ReminderController::class, 'send'])->name('reminder.send');

    // User Management (Super Admin only)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

    // Master Data - Services
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

    // Master Data - Spareparts
    Route::get('/spareparts', [SparepartController::class, 'index'])->name('spareparts.index');
    Route::get('/spareparts/create', [SparepartController::class, 'create'])->name('spareparts.create');
    Route::post('/spareparts', [SparepartController::class, 'store'])->name('spareparts.store');
    Route::get('/spareparts/{sparepart}/edit', [SparepartController::class, 'edit'])->name('spareparts.edit');
    Route::put('/spareparts/{sparepart}', [SparepartController::class, 'update'])->name('spareparts.update');
    Route::delete('/spareparts/{sparepart}', [SparepartController::class, 'destroy'])->name('spareparts.destroy');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
    Route::get('/reports/daily/export', [ReportController::class, 'dailyExport'])->name('reports.daily.export');
    Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('/reports/monthly/export', [ReportController::class, 'monthlyExport'])->name('reports.monthly.export');
    Route::get('/reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('/reports/stock/export', [ReportController::class, 'stockExport'])->name('reports.stock.export');
    Route::get('/reports/documentation', [ReportController::class, 'documentation'])->name('reports.documentation');
    Route::get('/reports/documentation/export', [ReportController::class, 'documentationExport'])->name('reports.documentation.export');
    Route::get('/reports/incomplete-payments', [ReportController::class, 'incompletePayments'])->name('reports.incomplete-payments');
    Route::get('/reports/incomplete-payments/export', [ReportController::class, 'incompletePaymentsExport'])->name('reports.incomplete-payments.export');
    Route::get('/reports/reminder', [ReportController::class, 'reminder'])->name('reports.reminder');
    Route::get('/reports/reminder/export', [ReportController::class, 'reminderExport'])->name('reports.reminder.export');
});

// Public Pages (no auth)
Route::get('/', [HomeController::class, 'index'])->name('public.home');
Route::get('/tentang', [AboutController::class, 'index'])->name('public.about');
Route::get('/layanan', [PublicServiceController::class, 'index'])->name('public.services.index');
Route::get('/layanan/{service}', [PublicServiceController::class, 'show'])->name('public.services.show');
Route::get('/galeri', [GalleryController::class, 'index'])->name('public.gallery');
Route::get('/kontak', [ContactController::class, 'index'])->name('public.contact');
Route::post('/kontak', [ContactController::class, 'store'])->name('public.contact.store');
