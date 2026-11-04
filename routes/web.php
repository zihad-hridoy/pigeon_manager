<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PigeonController;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login')->name('login.post');
    Route::post('/logout', 'logout')->name('logout');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('pigeons', PigeonController::class);
});

use App\Http\Controllers\MedicalRecordController;

Route::middleware('auth')->group(function () {
    Route::get('pigeons/{pigeon}/medical', [MedicalRecordController::class, 'index'])->name('medical.index');
    Route::post('pigeons/{pigeon}/vaccinations', [MedicalRecordController::class, 'storeVaccination'])->name('medical.vaccinations.store');
    Route::delete('vaccinations/{record}', [MedicalRecordController::class, 'destroyVaccination'])->name('medical.vaccinations.destroy');
    Route::post('pigeons/{pigeon}/diseases', [MedicalRecordController::class, 'storeDisease'])->name('medical.diseases.store');
    Route::delete('diseases/{record}', [MedicalRecordController::class, 'destroyDisease'])->name('medical.diseases.destroy');
});
