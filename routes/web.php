<?php

use App\Http\Controllers\Mahasiswa\KartuUjianController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Mahasiswa Routes
Route::middleware(['auth'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/kartu-ujian/print', [KartuUjianController::class, 'print'])
        ->name('kartu-ujian.print');
});
