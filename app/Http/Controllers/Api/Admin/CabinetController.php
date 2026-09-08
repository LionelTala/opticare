<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VerifyCabinetRequest;
use App\Http\Requests\Cabinet\UpdateProfileRequest;
use App\Services\Admin\AdminService;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\CabinetResource;
use Illuminate\Http\JsonResponse;

class CabinetController extends Controller
{
    use ApiResponseTrait;

    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * Liste de tous les cabinets
     */
    public function index(): JsonResponse
    {
        try {
            $result = $this->adminService->getCabinets();

            return $this->successResponse([
                'cabinets' => CabinetResource::collection($result['cabinets']),
            ], 'Liste des cabinets récupérée avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la récupération des cabinets',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Valider un cabinet
     */
    public function verify(int $id, VerifyCabinetRequest $request): JsonResponse
    {
        try {
            $result = $this->adminService->verifyCabinet(
                $id,
                'verify',
                null
            );

            return $this->successResponse([
                'cabinet' => new CabinetResource($result['cabinet']),
            ], 'Cabinet validé avec succès. Le propriétaire va recevoir une notification.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la validation du cabinet',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Refuser un cabinet
     */
    public function reject(int $id, VerifyCabinetRequest $request): JsonResponse
    {
        try {
            $result = $this->adminService->verifyCabinet(
                $id,
                'reject',
                $request->input('motif_refus')
            );

            return $this->successResponse([
                'cabinet' => new CabinetResource($result['cabinet']),
            ], 'Cabinet refusé. Le propriétaire va recevoir une notification.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors du refus du cabinet',
                ['general' => $e->getMessage()],
                500
            );
        }
    }
    /**
 * Mettre à jour le profil d'un cabinet
 */
public function updateProfile(UpdateProfileRequest $request, int $id): JsonResponse
{
    try {
        $result = $this->adminService->updateProfile($request->validated(), $id);

        return $this->successResponse([
            'cabinet' => new CabinetResource($result['cabinet']),
        ], 'Profil du cabinet mis à jour avec succès.');
    } catch (\Exception $e) {
        return $this->errorResponse(
            'Erreur lors de la mise à jour du profil',
            ['general' => $e->getMessage()],
            500
        );
    }
}
}