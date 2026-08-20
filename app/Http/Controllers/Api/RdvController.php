<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cabinet\CreneauHoraireRequest;
use App\Http\Requests\Cabinet\CreneauHoraireUpdateRequest;
use App\Services\Rdv\RdvService;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\CreneauHoraireResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Rdv\PrendreRdvRequest;

class RdvController extends Controller
{
    use ApiResponseTrait;

    protected RdvService $rdvService;

    public function __construct(RdvService $rdvService)
    {
        $this->rdvService = $rdvService;
    }

    /**
     * Créer une plage horaire
     */
    public function storeCreneau(CreneauHoraireRequest $request, int $cabinetId): JsonResponse
    {
        try {
            $result = $this->rdvService->createCreneauHoraire($request->validated(), $cabinetId);

            return $this->successResponse([
                'creneau' => new CreneauHoraireResource($result['creneau']),
            ], 'Plage horaire créée avec succès.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la création de la plage horaire',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Modifier une plage horaire
     */
    public function updateCreneau(CreneauHoraireUpdateRequest $request, int $cabinetId, int $creneauId): JsonResponse
    {
        try {
            $result = $this->rdvService->updateCreneauHoraire($request->validated(), $cabinetId, $creneauId);

            return $this->successResponse([
                'creneau' => new CreneauHoraireResource($result['creneau']),
            ], 'Plage horaire modifiée avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la modification de la plage horaire',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Supprimer une plage horaire
     */
    public function deleteCreneau(int $cabinetId, int $creneauId): JsonResponse
    {
        try {
            $this->rdvService->deleteCreneauHoraire($cabinetId, $creneauId);

            return $this->successResponse(null, 'Plage horaire supprimée avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la suppression de la plage horaire',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Lister les plages horaires d'un cabinet
     */
    public function indexCreneaux(int $cabinetId): JsonResponse
    {
        try {
            $result = $this->rdvService->getCreneauxHoraires($cabinetId);

            return $this->successResponse([
                'creneaux' => CreneauHoraireResource::collection($result['creneaux']),
            ], 'Plages horaires récupérées avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la récupération des plages horaires',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Récupérer les créneaux disponibles
     */
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
            return $this->errorResponse(
                'Erreur lors de la récupération des créneaux',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
 * Prendre un RDV
 */
public function prendreRdv(PrendreRdvRequest $request): JsonResponse
{
    try {
        $userId = auth()->id();
        
        $result = $this->rdvService->prendreRdv($request->validated(), $userId);

        return $this->successResponse([
            'rdv' => $result['rdv'],
            'patient' => $result['patient'],
            'statut' => $result['statut'],
        ], $result['message'], 201);
    } catch (\Exception $e) {
        return $this->errorResponse(
            'Erreur lors de la prise de RDV',
            ['general' => $e->getMessage()],
            500
        );
    }
}
}