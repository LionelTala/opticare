<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cabinet\CreneauHoraireRequest;
use App\Http\Requests\Cabinet\CreneauHoraireUpdateRequest;
use App\Http\Requests\Rdv\PrendreRdvRequest;
use App\Services\Rdv\RdvService;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\CreneauHoraireResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RdvController extends Controller
{
    use ApiResponseTrait;

    protected RdvService $rdvService;

    public function __construct(RdvService $rdvService)
    {
        $this->rdvService = $rdvService;
    }

    // ==========================================================
    // PLAGES HORAIRES
    // ==========================================================

    public function storeCreneau(CreneauHoraireRequest $request, int $cabinetId): JsonResponse
    {
        try {
            $result = $this->rdvService->createCreneauHoraire($request->validated(), $cabinetId);

            return $this->successResponse([
                'creneau' => new CreneauHoraireResource($result['creneau']),
            ], 'Plage horaire créée avec succès.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la création de la plage horaire', ['general' => $e->getMessage()], 500);
        }
    }

    public function updateCreneau(CreneauHoraireUpdateRequest $request, int $cabinetId, int $creneauId): JsonResponse
    {
        try {
            $result = $this->rdvService->updateCreneauHoraire($request->validated(), $cabinetId, $creneauId);

            return $this->successResponse([
                'creneau' => new CreneauHoraireResource($result['creneau']),
            ], 'Plage horaire modifiée avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la modification de la plage horaire', ['general' => $e->getMessage()], 500);
        }
    }

    public function deleteCreneau(int $cabinetId, int $creneauId): JsonResponse
    {
        try {
            $this->rdvService->deleteCreneauHoraire($cabinetId, $creneauId);

            return $this->successResponse(null, 'Plage horaire supprimée avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la suppression de la plage horaire', ['general' => $e->getMessage()], 500);
        }
    }

    public function indexCreneaux(int $cabinetId): JsonResponse
    {
        try {
            $result = $this->rdvService->getCreneauxHoraires($cabinetId);

            return $this->successResponse([
                'creneaux' => CreneauHoraireResource::collection($result['creneaux']),
            ], 'Plages horaires récupérées avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la récupération des plages horaires', ['general' => $e->getMessage()], 500);
        }
    }

    public function getAvailableSlots(Request $request, int $cabinetId): JsonResponse
    {
        try {
            $date = $request->query('date', date('Y-m-d'));
            $result = $this->rdvService->getAvailableSlots($cabinetId, $date);

            return $this->successResponse([
                'date' => $date,
                'slots' => $result['slots'],
            ], 'Créneaux disponibles récupérés avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la récupération des créneaux', ['general' => $e->getMessage()], 500);
        }
    }

    // ==========================================================
    // PRISE DE RDV
    // ==========================================================

    public function prendreRdv(PrendreRdvRequest $request): JsonResponse
    {
        try {
            $userId = auth('sanctum')->id();
            $result = $this->rdvService->prendreRdv($request->validated(), $userId);

            return $this->successResponse([
                'rdv' => $result['rdv'],
                'patient' => $result['patient'],
                'statut' => $result['statut'],
            ], $result['message'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Erreur de validation', $e->errors(), 422);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $isBusinessError = str_contains($message, 'créneau')
                || str_contains($message, 'plage horaire')
                || str_contains($message, 'téléphone')
                || str_contains($message, 'Utilisateur');

            return $this->errorResponse(
                'Erreur lors de la prise de RDV',
                ['general' => $message],
                $isBusinessError ? 422 : 500
            );
        }
    }

    // ==========================================================
    // ACTIONS SUR UN RDV
    // ==========================================================

    public function annulerRdv(int $id): JsonResponse
    {
        try {
            $userId = auth('sanctum')->id();
            $result = $this->rdvService->annulerRdv($id, $userId);

            return $this->successResponse(['rdv' => $result['rdv']], $result['message']);
        } catch (\Exception $e) {
            $message = $e->getMessage();

            if (str_contains($message, 'déjà annulé')
                || str_contains($message, 'ne peut plus')
                || str_contains($message, 'non autorisé')
                || str_contains($message, 'pas autorisé')
                || str_contains($message, 'Accès refusé')) {
                return $this->errorResponse('Action impossible', ['rdv' => [$message]], 422);
            }

            return $this->errorResponse('Erreur lors de l\'annulation du RDV', ['general' => $message], 500);
        }
    }

    public function modifierRdv(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'date' => 'sometimes|date|after_or_equal:today',
                'heure_debut' => 'sometimes|date_format:H:i',
                'motif' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:500',
            ]);

            $userId = auth('sanctum')->id();
            $result = $this->rdvService->modifierRdv($data, $id, $userId);

            return $this->successResponse(['rdv' => $result['rdv']], $result['message']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Erreur de validation', $e->errors(), 422);
        } catch (\Exception $e) {
            $message = $e->getMessage();

            if (str_contains($message, 'ne peut plus')
                || str_contains($message, 'déjà réservé')
                || str_contains($message, 'non autorisé')
                || str_contains($message, 'pas autorisé')
                || str_contains($message, 'Accès refusé')) {
                return $this->errorResponse('Modification impossible', ['rdv' => [$message]], 422);
            }

            return $this->errorResponse('Erreur lors de la modification du RDV', ['general' => $message], 500);
        }
    }

    public function confirmerRdv(int $id): JsonResponse
    {
        try {
            $result = $this->rdvService->confirmerRdv($id);
            return $this->successResponse(['rdv' => $result['rdv']], $result['message']);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $isBusiness = str_contains($message, 'déjà confirmé') || str_contains($message, 'ne peut pas');
            return $this->errorResponse('Erreur confirmation RDV', ['rdv' => [$message]], $isBusiness ? 422 : 500);
        }
    }

    public function nonHonoreRdv(int $id): JsonResponse
    {
        try {
            $result = $this->rdvService->nonHonoreRdv($id);
            return $this->successResponse(['rdv' => $result['rdv']], $result['message']);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $isBusiness = str_contains($message, 'déjà') || str_contains($message, 'ne peut pas');
            return $this->errorResponse('Erreur marquage RDV', ['rdv' => [$message]], $isBusiness ? 422 : 500);
        }
    }

    public function terminerRdv(int $id): JsonResponse
    {
        try {
            $result = $this->rdvService->terminerRdv($id);
            return $this->successResponse(['rdv' => $result['rdv']], $result['message']);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $isBusiness = str_contains($message, 'déjà') || str_contains($message, 'ne peut pas');
            return $this->errorResponse('Erreur terminaison RDV', ['rdv' => [$message]], $isBusiness ? 422 : 500);
        }
    }

    // ==========================================================
    // LECTURE
    // ==========================================================

    public function getRdvByPatient(int $patientId): JsonResponse
    {
        try {
            $userId = auth('sanctum')->id();
            $result = $this->rdvService->getRdvByPatient($patientId, $userId);

            return $this->successResponse(['rdvs' => $result['rdvs']], 'RDV du patient récupérés avec succès.');
        } catch (\Exception $e) {
            $message = $e->getMessage();

            if (str_contains($message, 'non autorisé')
                || str_contains($message, 'pas autorisé')
                || str_contains($message, 'Accès refusé')
                || str_contains($message, 'appartient pas')) {
                return $this->errorResponse('Accès refusé', ['rdv' => [$message]], 403);
            }

            return $this->errorResponse('Erreur récupération RDV', ['general' => $message], 500);
        }
    }

    public function getRdvByCabinet(Request $request, int $cabinetId): JsonResponse
    {
        try {
            $date = $request->query('date');
            $statut = $request->query('statut');
            $result = $this->rdvService->getRdvByCabinet($cabinetId, $date, $statut);

            return $this->successResponse(['rdvs' => $result['rdvs']], 'RDV du cabinet récupérés avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur récupération RDV', ['general' => $e->getMessage()], 500);
        }
    }
}