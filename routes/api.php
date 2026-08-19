<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\RegisterCabinetController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Admin\CabinetController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('/register/patient', [RegisterController::class, 'registerPatient']);
    Route::post('/register/cabinet', [RegisterCabinetController::class, 'registerCabinet']);
    Route::post('/login', [LoginController::class, 'login']);

});

Route::middleware(['auth:sanctum', 'superadmin'])->prefix('admin')->group(function () {
    Route::get('/cabinets', [CabinetController::class, 'index']);
    Route::put('/cabinets/{id}/verify', [CabinetController::class, 'verify']);
    Route::put('/cabinets/{id}/reject', [CabinetController::class, 'reject']);
});