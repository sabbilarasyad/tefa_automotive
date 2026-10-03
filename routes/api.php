<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CashierController;
use App\Http\Controllers\Api\PkbController;
use App\Http\Controllers\Api\WorkProgressController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

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
    Route::get('/invoices/{invoice}', [CashierController::class, 'show']);
    Route::post('/invoices/{invoice}/payments', [CashierController::class, 'processPayment']);
    Route::get('/invoices/{invoice}/print', [CashierController::class, 'print']);
    Route::post('/pkb/{pkb}/start', [WorkProgressController::class, 'start']);
    Route::patch('/pkb/{pkb}/spareparts', [WorkProgressController::class, 'updateSpareparts']);
    Route::post('/pkb/{pkb}/documentations', [WorkProgressController::class, 'uploadDocumentation']);
    Route::delete('/pkb/{pkb}/documentations', [WorkProgressController::class, 'deleteDocumentation']);
    Route::post('/pkb/{pkb}/complete', [WorkProgressController::class, 'complete']);
    Route::get('/pkb/{pkb}/checklist', [CashierController::class, 'checklist']);
    Route::post('/pkb/{pkb}/invoice', [CashierController::class, 'generateInvoice']);
    Route::get('/invoices/{invoice}', [CashierController::class, 'show']);
    Route::post('/invoices/{invoice}/payments', [CashierController::class, 'processPayment']);
    Route::get('/invoices/{invoice}/print', [CashierController::class, 'print']);
});
