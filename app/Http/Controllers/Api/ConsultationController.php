<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Consultation\CreateConsultationRequest;
use App\Http\Requests\Consultation\UpdateConsultationRequest;
use App\Services\Consultation\ConsultationService;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\ConsultationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ConsultationController extends Controller
{
    use ApiResponseTrait;

    protected ConsultationService $consultationService;

    public function __construct(ConsultationService $consultationService)
    {
        $this->consultationService = $consultationService;
    }

    /**
     * Créer une consultation
     */
    public function store(CreateConsultationRequest $request): JsonResponse
    {
        try {
            $opticienId = auth('sanctum')->id();
            $result = $this->consultationService->createConsultation($request->validated(), $opticienId);

            return $this->successResponse([
                'consultation' => new ConsultationResource($result['consultation']),
            ], 'Consultation créée avec succès.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la création de la consultation',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Récupérer une consultation
     */
    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->consultationService->getConsultation($id);

            return $this->successResponse([
                'consultation' => new ConsultationResource($result['consultation']),
            ], 'Consultation récupérée avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Consultation non trouvée',
                ['general' => $e->getMessage()],
                404
            );
        }
    }

    /**
     * Mettre à jour une consultation
     */
    public function update(UpdateConsultationRequest $request, int $id): JsonResponse
    {
        try {
            $result = $this->consultationService->updateConsultation($request->validated(), $id);

            return $this->successResponse([
                'consultation' => new ConsultationResource($result['consultation']),
            ], 'Consultation mise à jour avec succès.');
        } catch (ValidationException $e) {
            return $this->errorResponse(
                'Erreur de validation',
                $e->errors(),
                422
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la mise à jour de la consultation',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Terminer une consultation (verrouiller)
     */
    public function terminer(int $id): JsonResponse
    {
        try {
            $result = $this->consultationService->terminerConsultation($id);

            return $this->successResponse([
                'consultation' => new ConsultationResource($result['consultation']),
            ], $result['message']);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la terminaison de la consultation',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Récupérer les consultations d'un patient
     */
    public function getByPatient(int $patientId): JsonResponse
    {
        try {
            $result = $this->consultationService->getConsultationsByPatient($patientId);

            return $this->successResponse([
                'consultations' => ConsultationResource::collection($result['consultations']),
            ], 'Historique des consultations récupéré avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la récupération des consultations',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Récupérer les consultations d'un cabinet
     */
    public function getByCabinet(int $cabinetId): JsonResponse
    {
        try {
            $result = $this->consultationService->getConsultationsByCabinet($cabinetId);

            return $this->successResponse([
                'consultations' => ConsultationResource::collection($result['consultations']),
            ], 'Consultations du cabinet récupérées avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la récupération des consultations',
                ['general' => $e->getMessage()],
                500
            );
        }
    }
}