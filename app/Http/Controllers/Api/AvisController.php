<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Avis\CreateAvisRequest;
use App\Services\Avis\AvisService;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\AvisResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvisController extends Controller
{
    use ApiResponseTrait;

    protected AvisService $avisService;

    public function __construct(AvisService $avisService)
    {
        $this->avisService = $avisService;
    }

    /**
     * Créer un avis
     */
    public function store(CreateAvisRequest $request): JsonResponse
    {
        try {
            $userId = auth('sanctum')->id();
            $result = $this->avisService->createAvis($request->validated(), $userId);

            return $this->successResponse([
                'avis' => new AvisResource($result['avis']),
            ], 'Merci pour votre avis !', 201);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la création de l\'avis',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Liste des avis d'un cabinet (public)
     */
    public function getByCabinet(int $cabinetId): JsonResponse
    {
        try {
            $result = $this->avisService->getAvisByCabinet($cabinetId);

            return $this->successResponse([
                'avis' => AvisResource::collection($result['avis']),
                'moyenne' => $result['moyenne'],
                'total' => $result['total'],
                'repartition' => $result['repartition'],
            ], 'Avis du cabinet récupérés avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la récupération des avis',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Liste des avis du patient connecté
     */
    public function getByPatient(): JsonResponse
    {
        try {
            $userId = auth('sanctum')->id();
            $user = \App\Models\User::with('patient')->find($userId);

            if (!$user || !$user->patient) {
                throw new \Exception('Patient non trouvé.');
            }

            $result = $this->avisService->getAvisByPatient($user->patient->id);

            return $this->successResponse([
                'avis' => AvisResource::collection($result['avis']),
            ], 'Vos avis récupérés avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la récupération des avis',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Modifier un avis
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'note' => 'sometimes|integer|min:1|max:5',
                'commentaire' => 'nullable|string|max:1000',
            ]);

            $userId = auth('sanctum')->id();
            $result = $this->avisService->updateAvis($data, $id, $userId);

            return $this->successResponse([
                'avis' => new AvisResource($result['avis']),
            ], 'Avis modifié avec succès.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse(
                'Erreur de validation',
                $e->errors(),
                422
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la modification de l\'avis',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Supprimer un avis
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $userId = auth('sanctum')->id();
            $this->avisService->deleteAvis($id, $userId);

            return $this->successResponse(null, 'Avis supprimé avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la suppression de l\'avis',
                ['general' => $e->getMessage()],
                500
            );
        }
    }
}