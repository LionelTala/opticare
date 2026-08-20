<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cabinet;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\CabinetPublicResource;
use Illuminate\Http\JsonResponse;

class CabinetPublicController extends Controller
{
    use ApiResponseTrait;

    public function index(): JsonResponse
    {
        try {
            $cabinets = Cabinet::with('proprietaire')
                ->where('status', 'valide')
                ->orderBy('nom')
                ->get();

            return $this->successResponse([
                'cabinets' => CabinetPublicResource::collection($cabinets),
            ], 'Liste des cabinets récupérée avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la récupération des cabinets',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $cabinet = Cabinet::with('proprietaire')
                ->where('status', 'valide')
                ->findOrFail($id);

            return $this->successResponse([
                'cabinet' => new CabinetPublicResource($cabinet),
            ], 'Détail du cabinet récupéré avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Cabinet non trouvé',
                ['general' => $e->getMessage()],
                404
            );
        }
    }
}