<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ordonnance\CreateOrdonnanceRequest;
use App\Services\Ordonnance\OrdonnanceService;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\OrdonnanceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdonnanceController extends Controller
{
    use ApiResponseTrait;

    protected OrdonnanceService $ordonnanceService;

    public function __construct(OrdonnanceService $ordonnanceService)
    {
        $this->ordonnanceService = $ordonnanceService;
    }

    public function store(CreateOrdonnanceRequest $request, int $consultationId): JsonResponse
    {
        try {
            $opticienId = auth('sanctum')->id();
            $result = $this->ordonnanceService->createFromConsultation($request->validated(), $consultationId, $opticienId);

            return $this->successResponse([
                'ordonnance' => new OrdonnanceResource($result['ordonnance']),
            ], 'Ordonnance créée avec succès.', 201);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $isBusiness = str_contains($message, 'terminée') || str_contains($message, 'existe déjà');
            return $this->errorResponse('Erreur création ordonnance', ['general' => $message], $isBusiness ? 422 : 500);
        }
    }

    public function index(Request $request, int $cabinetId): JsonResponse
    {
        try {
            $search = $request->query('search');
            $result = $this->ordonnanceService->getByCabinet($cabinetId, $search);

            return $this->successResponse([
                'ordonnances' => OrdonnanceResource::collection($result['ordonnances']),
            ], 'Ordonnances récupérées avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur récupération ordonnances', ['general' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->ordonnanceService->getOrdonnance($id);
            return $this->successResponse(['ordonnance' => new OrdonnanceResource($result['ordonnance'])], 'Ordonnance récupérée avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse('Ordonnance non trouvée', ['general' => $e->getMessage()], 404);
        }
    }
}