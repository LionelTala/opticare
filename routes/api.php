<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\RegisterCabinetController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Admin\CabinetController;
use App\Http\Controllers\Api\CabinetPublicController;
use App\Http\Controllers\Api\CommandeController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\PersonnelController;
use App\Http\Controllers\Api\RdvController;
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Route utilisateur connecté
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// =============================================
// ROUTES PUBLIQUES
// =============================================

// Authentification
Route::prefix('auth')->group(function () {
    Route::post('/register/patient', [RegisterController::class, 'registerPatient']);
    Route::post('/register/cabinet', [RegisterCabinetController::class, 'registerCabinet']);
    Route::post('/login', [LoginController::class, 'login'])->name('login');
});

// Cabinets publics
Route::prefix('cabinets')->group(function () {
    Route::get('/', [CabinetPublicController::class, 'index']);
    Route::get('/{id}', [CabinetPublicController::class, 'show']);
    Route::get('/{cabinetId}/creneaux', [RdvController::class, 'indexCreneaux']);
    Route::get('/{cabinetId}/slots', [RdvController::class, 'getAvailableSlots']);
});

// Prise de RDV (public)
Route::post('/rdv', [RdvController::class, 'prendreRdv']);

// =============================================
// ROUTES PROTÉGÉES (AUTHENTIFICATION REQUISE)
// =============================================

Route::middleware(['auth:sanctum'])->group(function () {

    // ----- Gestion du profil cabinet (propriétaire ou super_admin) -----
    Route::patch('/cabinets/{id}/profile', [CabinetController::class, 'updateProfile'])
        ->middleware('role:proprietaire,super_admin');

    // ----- Gestion des plages horaires (proprietaire, opticien, secretaire) -----
    Route::middleware(['role:proprietaire,opticien,secretaire'])->prefix('cabinets/{cabinetId}')->group(function () {
        Route::post('/creneaux', [RdvController::class, 'storeCreneau']);
        Route::put('/creneaux/{creneauId}', [RdvController::class, 'updateCreneau']);
        Route::delete('/creneaux/{creneauId}', [RdvController::class, 'deleteCreneau']);
    });

    // ----- Gestion des consultations (proprietaire, opticien) -----
    Route::prefix('consultations')->middleware(['role:proprietaire,opticien'])->group(function () {
        Route::post('/', [ConsultationController::class, 'store']);
        Route::get('/{id}', [ConsultationController::class, 'show']);
        Route::patch('/{id}', [ConsultationController::class, 'update']);
        Route::put('/{id}/terminer', [ConsultationController::class, 'terminer']);
    });

    // ----- Gestion des commandes (proprietaire, opticien) -----
    Route::prefix('commandes')->middleware(['role:proprietaire,opticien'])->group(function () {
        Route::post('/', [CommandeController::class, 'store']);
        Route::get('/{id}', [CommandeController::class, 'show']);
        Route::patch('/{id}', [CommandeController::class, 'update']);
    });

    // ----- Commandes par consultation -----
    Route::get('/consultations/{consultationId}/commandes', [CommandeController::class, 'getByConsultation']);

    // ----- Commandes par cabinet (proprietaire, opticien, secretaire) -----
    Route::get('/cabinets/{cabinetId}/commandes', [CommandeController::class, 'getByCabinet'])
        ->middleware('role:proprietaire,opticien,secretaire');

    // ----- Consultations par patient -----
    Route::get('/patients/{patientId}/consultations', [ConsultationController::class, 'getByPatient'])
        ->middleware('role:proprietaire,opticien,secretaire,patient');

    // ----- Consultations par cabinet (proprietaire, opticien, secretaire) -----
    Route::get('/cabinets/{cabinetId}/consultations', [ConsultationController::class, 'getByCabinet'])
        ->middleware('role:proprietaire,opticien,secretaire');

    // ----- Gestion du personnel (proprietaire uniquement) -----
    Route::prefix('cabinets/{cabinetId}/personnel')->middleware(['role:proprietaire'])->group(function () {
        Route::post('/', [PersonnelController::class, 'store']);
        Route::get('/', [PersonnelController::class, 'index']);
        Route::get('/{personnelId}', [PersonnelController::class, 'show']);
        Route::put('/{personnelId}', [PersonnelController::class, 'update']);
        Route::delete('/{personnelId}', [PersonnelController::class, 'destroy']);
    });

    // =============================================
    // ROUTES NOTIFICATIONS
    // =============================================

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
    });

    // =============================================
    // ROUTES RDV (Gestion complète)
    // =============================================

    // ----- Actions du patient sur ses RDV -----
    Route::prefix('rdv')->group(function () {
        Route::put('/{id}/annuler', [RdvController::class, 'annulerRdv']);
        Route::put('/{id}/modifier', [RdvController::class, 'modifierRdv']);
    });

    // ----- Actions du cabinet sur les RDV (proprietaire, opticien, secretaire) -----
    Route::prefix('rdv')->middleware(['role:proprietaire,opticien,secretaire'])->group(function () {
        Route::put('/{id}/confirmer', [RdvController::class, 'confirmerRdv']);
        Route::put('/{id}/terminer', [RdvController::class, 'terminerRdv']);
        Route::put('/{id}/non-honore', [RdvController::class, 'nonHonoreRdv']);
    });

    // ----- Historique des RDV -----
    Route::get('/patients/{patientId}/rdv', [RdvController::class, 'getRdvByPatient']);
    Route::get('/cabinets/{cabinetId}/rdv', [RdvController::class, 'getRdvByCabinet'])
        ->middleware('role:proprietaire,opticien,secretaire');
});

// =============================================
// ROUTES SUPER ADMIN
// =============================================

Route::middleware(['auth:sanctum', 'superadmin'])->prefix('admin')->group(function () {
    Route::get('/cabinets', [CabinetController::class, 'index']);
    Route::put('/cabinets/{id}/verify', [CabinetController::class, 'verify']);
    Route::put('/cabinets/{id}/reject', [CabinetController::class, 'reject']);
});