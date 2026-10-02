<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PkbController;
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
});
