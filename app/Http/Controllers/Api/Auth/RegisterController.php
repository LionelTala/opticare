<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterPatientRequest;
use App\Services\Auth\AuthService;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    use ApiResponseTrait;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function registerPatient(RegisterPatientRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->registerPatient($request->validated());

            return $this->successResponse([
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'role' => $result['role'],
            ], 'Inscription réussie ! Bienvenue sur Opticare.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de l\'inscription',
                ['general' => $e->getMessage()],
                500
            );
        }
    }
}