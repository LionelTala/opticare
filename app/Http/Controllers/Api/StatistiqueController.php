<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Statistique\StatistiqueService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class StatistiqueController extends Controller
{
    use ApiResponseTrait;

    protected StatistiqueService $statistiqueService;

    public function __construct(StatistiqueService $statistiqueService)
    {
        $this->statistiqueService = $statistiqueService;
    }

    /**
     * Récupérer toutes les statistiques d'un cabinet
     */
    public function getStatistiques(int $cabinetId): JsonResponse
    {
        try {
            $result = $this->statistiqueService->getStatistiques($cabinetId);

            return $this->successResponse($result, 'Statistiques récupérées avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la récupération des statistiques',
                ['general' => $e->getMessage()],
                500
            );
        }
    }
}