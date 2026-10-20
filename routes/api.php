<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CashierController;
use App\Http\Controllers\Api\PkbController;
use App\Http\Controllers\Api\WorkProgressController;
use App\Http\Controllers\Api\SparepartController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\ReminderController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SparepartController as ApiSparepartController;
use App\Http\Controllers\Api\ContactController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Public endpoint (no auth)
Route::post('/contact-messages', [ContactController::class, 'store'])->middleware('throttle:10,1');

Route::middleware(['auth:sanctum', 'active'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // PKB routes
    Route::get('/pkb', [PkbController::class, 'index']);
    Route::post('/pkb', [PkbController::class, 'store']);
    Route::get('/pkb/{pkb}', [PkbController::class, 'show']);
    Route::patch('/pkb/{pkb}', [PkbController::class, 'update']);
    Route::post('/pkb/{pkb}/assign-mechanic', [PkbController::class, 'assignMechanic']);
    Route::post('/pkb/{pkb}/cancel', [PkbController::class, 'cancel']);
    Route::post('/pkb/{pkb}/force-cancel', [PkbController::class, 'forceCancel']);
    Route::get('/pkb/{pkb}/print', [PkbController::class, 'print']);
    Route::get('/pkb/{pkb}/status-histories', [PkbController::class, 'statusHistories']);
    Route::get('/pkb/{pkb}/checklist', [CashierController::class, 'checklist']);
    Route::post('/pkb/{pkb}/invoice', [CashierController::class, 'generateInvoice']);

    // Invoice routes
    Route::get('/invoices/{invoice}', [CashierController::class, 'show']);
    Route::post('/invoices/{invoice}/payments', [CashierController::class, 'processPayment']);
    Route::get('/invoices/{invoice}/print', [CashierController::class, 'print']);

    // Work Progress routes
    Route::post('/pkb/{pkb}/start', [WorkProgressController::class, 'start']);
    Route::patch('/pkb/{pkb}/spareparts', [WorkProgressController::class, 'updateSpareparts']);
    Route::post('/pkb/{pkb}/documentations', [WorkProgressController::class, 'uploadDocumentation']);
    Route::delete('/pkb/{pkb}/documentations', [WorkProgressController::class, 'deleteDocumentation']);
    Route::post('/pkb/{pkb}/complete', [WorkProgressController::class, 'complete']);

    // Sparepart routes
    Route::get('/spareparts', [SparepartController::class, 'index'])->name('api.spareparts.index');
    Route::get('/spareparts/{sparepart}', [SparepartController::class, 'show'])->name('api.spareparts.show');
    Route::post('/spareparts/{sparepart}/stock-adjustments', [SparepartController::class, 'stockAdjustment'])->name('api.spareparts.stock-adjustments');

    // Vehicle routes
    Route::get('/vehicles/{vehicle}/service-histories', [VehicleController::class, 'serviceHistories'])->name('api.vehicles.service-histories');

    // Reminder routes
    Route::get('/reminders', [ReminderController::class, 'index'])->name('api.reminders.index');
    Route::post('/reminders/{reminder}/send', [ReminderController::class, 'send'])->name('api.reminders.send');

    // User routes (Super Admin only)
    Route::get('/users', [UserController::class, 'index'])->name('api.users.index');
    Route::post('/users', [UserController::class, 'store'])->name('api.users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('api.users.show');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('api.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('api.users.destroy');
    Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('api.users.reset-password');

    // Master Data - Services
    Route::get('/master/services', [ServiceController::class, 'index'])->name('api.master.services.index');
    Route::post('/master/services', [ServiceController::class, 'store'])->name('api.master.services.store');
    Route::get('/master/services/{service}', [ServiceController::class, 'show'])->name('api.master.services.show');
    Route::patch('/master/services/{service}', [ServiceController::class, 'update'])->name('api.master.services.update');
    Route::delete('/master/services/{service}', [ServiceController::class, 'destroy'])->name('api.master.services.destroy');

    // Master Data - Spareparts
    Route::get('/master/spareparts', [SparepartController::class, 'masterIndex'])->name('api.master.spareparts.index');
    Route::post('/master/spareparts', [SparepartController::class, 'masterStore'])->name('api.master.spareparts.store');
    Route::get('/master/spareparts/{sparepart}', [SparepartController::class, 'masterShow'])->name('api.master.spareparts.show');
    Route::patch('/master/spareparts/{sparepart}', [SparepartController::class, 'masterUpdate'])->name('api.master.spareparts.update');
    Route::delete('/master/spareparts/{sparepart}', [SparepartController::class, 'masterDestroy'])->name('api.master.spareparts.destroy');

    // Contact form (public, with throttle) - MOVED OUTSIDE auth:sanctum
    // Route::post('/contact-messages', [ContactController::class, 'store'])->middleware('throttle:10,1');
});