<?php

use App\Http\Controllers\ProfileController;
use App\Models\Suppliers;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/suppliers', function () {
    $suppliers = Suppliers::withCount('layups')->latest()->paginate(5);

    return view('suppliers', ['suppliers' => $suppliers]);
})->middleware(['auth', 'verified'])->name('suppliers');

Route::get('/suppliers/{supplier}', function (Suppliers $supplier) {
    $layups = $supplier->layups()->latest()->paginate(5);

    return view('layup-manager', [
        'supplier' => $supplier,
        'layups'   => $layups,
    ]);
})->middleware(['auth', 'verified'])->name('suppliers.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
