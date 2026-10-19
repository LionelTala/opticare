<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\CreatePatientRequest;
use App\Services\Patient\PatientService;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\PatientCabinetResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    use ApiResponseTrait;

    protected PatientService $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    /**
     * Liste des patients du cabinet
     */
    public function index(Request $request, int $cabinetId): JsonResponse
    {
        try {
            $search = $request->query('search');
            $result = $this->patientService->getPatientsByCabinet($cabinetId, $search);

            return $this->successResponse([
                'patients' => PatientCabinetResource::collection($result['patients']),
            ], 'Liste des patients récupérée avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la récupération des patients',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Créer un patient (patient interne)
     */
    public function store(CreatePatientRequest $request, int $cabinetId): JsonResponse
    {
        try {
            $result = $this->patientService->createPatient($request->validated(), $cabinetId);

            return $this->successResponse([
                'patient' => new PatientCabinetResource($result['patient']),
            ], 'Patient créé avec succès.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la création du patient',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Détail d'un patient
     */
    public function show(int $cabinetId, int $patientId): JsonResponse
    {
        try {
            $result = $this->patientService->getPatient($patientId, $cabinetId);

            return $this->successResponse([
                'patient' => new PatientCabinetResource($result['patient']),
            ], 'Patient récupéré avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Patient non trouvé dans ce cabinet',
                ['general' => $e->getMessage()],
                404
            );
        }
    }
}