<?php

use App\Http\Controllers\LayerController;
use App\Http\Controllers\LayupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers-export-all', [SupplierController::class, 'exportAll'])->name('suppliers.exportAll');
    Route::post('/suppliers-import-all', [SupplierController::class, 'importAll'])->name('suppliers.importAll');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::get('/suppliers/{supplier}/export', [SupplierController::class, 'export'])->name('suppliers.export');
    Route::post('/suppliers/{supplier}/import', [SupplierController::class, 'import'])->name('suppliers.import');
    Route::patch('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

    Route::post('/suppliers/{supplier}/layups', [LayupController::class, 'store'])->name('layups.store');
    Route::get('/suppliers/{supplier}/layups/{layup}', [LayupController::class, 'show'])->scopeBindings()->name('layups.show');
    Route::patch('/layups/{layup}', [LayupController::class, 'update'])->name('layups.update');
    Route::delete('/layups/{layup}', [LayupController::class, 'destroy'])->name('layups.destroy');

    Route::post('/suppliers/{supplier}/layups/{layup}/layers', [LayerController::class, 'store'])
        ->scopeBindings()->name('layers.store');
    Route::patch('/layers/{layer}', [LayerController::class, 'update'])->name('layers.update');
    Route::delete('/layers/{layer}', [LayerController::class, 'destroy'])->name('layers.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
