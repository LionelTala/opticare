<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Personnel\CreateEmployeRequest;
use App\Http\Requests\Personnel\UpdateEmployeRequest;
use App\Services\Personnel\PersonnelService;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\PersonnelResource;
use Illuminate\Http\JsonResponse;

class PersonnelController extends Controller
{
    use ApiResponseTrait;

    protected PersonnelService $personnelService;

    public function __construct(PersonnelService $personnelService)
    {
        $this->personnelService = $personnelService;
    }

    public function store(CreateEmployeRequest $request, int $cabinetId): JsonResponse
    {
        try {
            $result = $this->personnelService->createEmploye($request->validated(), $cabinetId);

            return $this->successResponse([
                'employe' => new PersonnelResource($result['employe']),
            ], 'Employé ajouté avec succès.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de l\'ajout de l\'employé',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    public function index(int $cabinetId): JsonResponse
    {
        try {
            $result = $this->personnelService->getEmployesByCabinet($cabinetId);

            return $this->successResponse([
                'employes' => PersonnelResource::collection($result['employes']),
            ], 'Liste des employés récupérée avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la récupération des employés',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    public function show(int $cabinetId, int $personnelId): JsonResponse
    {
        try {
            $result = $this->personnelService->getEmploye($personnelId, $cabinetId);

            return $this->successResponse([
                'employe' => new PersonnelResource($result['employe']),
            ], 'Employé récupéré avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Employé non trouvé',
                ['general' => $e->getMessage()],
                404
            );
        }
    }

    public function update(UpdateEmployeRequest $request, int $cabinetId, int $personnelId): JsonResponse
    {
        try {
            $result = $this->personnelService->updateEmploye($request->validated(), $personnelId, $cabinetId);

            return $this->successResponse([
                'employe' => new PersonnelResource($result['employe']),
            ], 'Employé mis à jour avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la mise à jour de l\'employé',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    public function destroy(int $cabinetId, int $personnelId): JsonResponse
    {
        try {
            $this->personnelService->deleteEmploye($personnelId, $cabinetId);

            return $this->successResponse(null, 'Employé supprimé avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la suppression de l\'employé',
                ['general' => $e->getMessage()],
                500
            );
        }
    }
}