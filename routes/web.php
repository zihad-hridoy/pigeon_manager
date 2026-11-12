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

Route::middleware('auth')->group(function () {
    Route::get('/health', [\App\Http\Controllers\HealthController::class, 'index'])->name('health.index');
    Route::post('/health/vaccinations', [\App\Http\Controllers\HealthController::class, 'storeVaccination'])->name('health.vaccinations.store');
    Route::post('/health/diseases', [\App\Http\Controllers\HealthController::class, 'storeDisease'])->name('health.diseases.store');
    Route::patch('/health/diseases/{disease}', [\App\Http\Controllers\HealthController::class, 'updateDiseaseStatus'])->name('health.diseases.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/flights', [\App\Http\Controllers\FlightRecordController::class, 'index'])->name('flights.index');
    Route::post('/flights', [\App\Http\Controllers\FlightRecordController::class, 'store'])->name('flights.store');
    Route::delete('/flights/{flight}', [\App\Http\Controllers\FlightRecordController::class, 'destroy'])->name('flights.destroy');
});

Route::middleware('auth')->group(function () {
    Route::post('/health/vaccinations/bulk', [\App\Http\Controllers\HealthController::class, 'storeBulkVaccination'])->name('health.vaccinations.bulk');
});


// Admin Member Creation & Owner Profiles
Route::middleware('auth')->group(function () {
    Route::get('/members/create', [\App\Http\Controllers\RegisterController::class, 'create'])->name('members.create');
    Route::post('/members', [\App\Http\Controllers\RegisterController::class, 'store'])->name('members.store');
    Route::get('/members', [\App\Http\Controllers\ProfileController::class, 'index'])->name('members.index');
    Route::get('/members/{user}', [\App\Http\Controllers\ProfileController::class, 'show'])->name('members.show');
    Route::get('/profile/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});
