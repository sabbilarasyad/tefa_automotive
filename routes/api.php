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

Route::middleware('auth:sanctum')->group(function (): void {
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
    Route::get('/spareparts', [ApiSparepartController::class, 'index']);
    Route::get('/spareparts/{sparepart}', [ApiSparepartController::class, 'show']);
    Route::post('/spareparts/{sparepart}/stock-adjustments', [ApiSparepartController::class, 'stockAdjustment']);

    // Vehicle routes
    Route::get('/vehicles/{vehicle}/service-histories', [VehicleController::class, 'serviceHistories']);

    // Reminder routes
    Route::get('/reminders', [ReminderController::class, 'index']);
    Route::post('/reminders/{reminder}/send', [ReminderController::class, 'send']);

    // User routes (Super Admin only)
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword']);

    // Master Data - Services
    Route::get('/master/services', [ServiceController::class, 'index']);
    Route::post('/master/services', [ServiceController::class, 'store']);
    Route::get('/master/services/{service}', [ServiceController::class, 'show']);
    Route::patch('/master/services/{service}', [ServiceController::class, 'update']);
    Route::delete('/master/services/{service}', [ServiceController::class, 'destroy']);

    // Master Data - Spareparts
    Route::get('/master/spareparts', [ApiSparepartController::class, 'masterIndex']);
    Route::post('/master/spareparts', [ApiSparepartController::class, 'masterStore']);
    Route::get('/master/spareparts/{sparepart}', [ApiSparepartController::class, 'masterShow']);
    Route::patch('/master/spareparts/{sparepart}', [ApiSparepartController::class, 'masterUpdate']);
    Route::delete('/master/spareparts/{sparepart}', [ApiSparepartController::class, 'masterDestroy']);

    // Contact form (public, with throttle)
    Route::post('/contact-messages', [ContactController::class, 'store'])->middleware('throttle:10,1');
});