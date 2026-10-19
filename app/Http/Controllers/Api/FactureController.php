<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facture\CreateFactureRequest;
use App\Services\Facture\FactureService;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\FactureResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FactureController extends Controller
{
    use ApiResponseTrait;

    protected FactureService $factureService;

    public function __construct(FactureService $factureService)
    {
        $this->factureService = $factureService;
    }

    public function store(CreateFactureRequest $request, int $commandeId): JsonResponse
    {
        try {
            $userId = auth('sanctum')->id();
            $result = $this->factureService->createFromCommande($request->validated(), $commandeId, $userId);

            return $this->successResponse([
                'facture' => new FactureResource($result['facture']),
            ], 'Facture créée avec succès.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur création facture', ['general' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request, int $cabinetId): JsonResponse
    {
        try {
            $statut = $request->query('statut');
            $search = $request->query('search');
            $result = $this->factureService->getByCabinet($cabinetId, $statut, $search);

            return $this->successResponse([
                'factures' => FactureResource::collection($result['factures']),
            ], 'Factures récupérées avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur récupération factures', ['general' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->factureService->getFacture($id);
            return $this->successResponse(['facture' => new FactureResource($result['facture'])], 'Facture récupérée avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse('Facture non trouvée', ['general' => $e->getMessage()], 404);
        }
    }

    public function marquerPayee(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'montant_paye' => 'required|numeric|min:0.01',
            ]);

            $result = $this->factureService->marquerPayee($id, $data['montant_paye']);
            return $this->successResponse(['facture' => new FactureResource($result['facture'])], 'Paiement enregistré avec succès.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Erreur de validation', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur marquage facture', ['general' => $e->getMessage()], 500);
        }
    }
}