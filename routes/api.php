<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\RegisterCabinetController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Admin\CabinetController;
use App\Http\Controllers\Api\CabinetPublicController;
use App\Http\Controllers\Api\RdvController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('cabinets')->group(function () {
    Route::get('/', [CabinetPublicController::class, 'index']);
    Route::get('/{id}', [CabinetPublicController::class, 'show']);
    Route::get('/{cabinetId}/creneaux', [RdvController::class, 'indexCreneaux']);
    Route::get('/{cabinetId}/slots', [RdvController::class, 'getAvailableSlots']);
});

Route::prefix('auth')->group(function () {
    Route::post('/register/patient', [RegisterController::class, 'registerPatient']);
    Route::post('/register/cabinet', [RegisterCabinetController::class, 'registerCabinet']);
    Route::post('/login', [LoginController::class, 'login'])->name('login');

});

Route::post('/rdv', [RdvController::class, 'prendreRdv']);

Route::middleware(['auth:sanctum', 'role:proprietaire,opticien,secretaire'])->prefix('cabinets/{cabinetId}')->group(function () {
    Route::post('/creneaux', [RdvController::class, 'storeCreneau']);
    Route::put('/creneaux/{creneauId}', [RdvController::class, 'updateCreneau']);
    Route::delete('/creneaux/{creneauId}', [RdvController::class, 'deleteCreneau']);
});

Route::middleware(['auth:sanctum', 'superadmin'])->prefix('admin')->group(function () {
    Route::get('/cabinets', [CabinetController::class, 'index']);
    Route::put('/cabinets/{id}/verify', [CabinetController::class, 'verify']);
    Route::put('/cabinets/{id}/reject', [CabinetController::class, 'reject']);
});