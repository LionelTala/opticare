<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Commande\CreateCommandeRequest;
use App\Http\Requests\Commande\UpdateCommandeRequest;
use App\Services\Commande\CommandeService;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\CommandeResource;
use Illuminate\Http\JsonResponse;

class CommandeController extends Controller
{
    use ApiResponseTrait;

    protected CommandeService $commandeService;

    public function __construct(CommandeService $commandeService)
    {
        $this->commandeService = $commandeService;
    }

    /**
     * Créer une commande
     */
    public function store(CreateCommandeRequest $request): JsonResponse
    {
        try {
            $opticienId = auth('sanctum')->id();
            $result = $this->commandeService->createCommande($request->validated(), $opticienId);

            return $this->successResponse([
                'commande' => new CommandeResource($result['commande']),
            ], 'Commande créée avec succès.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la création de la commande',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Récupérer une commande
     */
    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->commandeService->getCommande($id);

            return $this->successResponse([
                'commande' => new CommandeResource($result['commande']),
            ], 'Commande récupérée avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Commande non trouvée',
                ['general' => $e->getMessage()],
                404
            );
        }
    }

    /**
     * Mettre à jour une commande
     */
    public function update(UpdateCommandeRequest $request, int $id): JsonResponse
    {
        try {
            $result = $this->commandeService->updateCommande($request->validated(), $id);

            return $this->successResponse([
                'commande' => new CommandeResource($result['commande']),
            ], 'Commande mise à jour avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la mise à jour de la commande',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Récupérer les commandes d'une consultation
     */
    public function getByConsultation(int $consultationId): JsonResponse
    {
        try {
            $result = $this->commandeService->getCommandesByConsultation($consultationId);

            return $this->successResponse([
                'commandes' => CommandeResource::collection($result['commandes']),
            ], 'Commandes récupérées avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la récupération des commandes',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Récupérer les commandes d'un cabinet
     */
    public function getByCabinet(int $cabinetId): JsonResponse
    {
        try {
            $result = $this->commandeService->getCommandesByCabinet($cabinetId);

            return $this->successResponse([
                'commandes' => CommandeResource::collection($result['commandes']),
            ], 'Commandes du cabinet récupérées avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la récupération des commandes',
                ['general' => $e->getMessage()],
                500
            );
        }
    }
}