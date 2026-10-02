<?php

use App\Http\Controllers\Admin\PkbController;
use App\Http\Controllers\Admin\PkbProgressController;
use App\Http\Controllers\Auth\WebLoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [WebLoginController::class, 'create'])->name('login');
    Route::post('/login', [WebLoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->get('/mechanic/tasks', [PkbProgressController::class, 'mechanicTasks'])->name('mechanic.tasks');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function (): void {
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
});
