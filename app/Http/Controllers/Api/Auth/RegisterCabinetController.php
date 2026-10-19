<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterCabinetRequest;
use App\Services\Auth\AuthService;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class RegisterCabinetController extends Controller
{
    use ApiResponseTrait;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

   public function registerCabinet(RegisterCabinetRequest $request): JsonResponse
{
    try {
        $result = $this->authService->registerCabinet($request->validated());

        return $this->successResponse([
            'user' => new UserResource($result['user']),
            'cabinet' => $result['cabinet'],
            'role' => $result['role'],
            'requires_verification' => true,
            'message' => 'Votre cabinet est en attente de validation. Vous recevrez un email dès qu\'un administrateur aura traité votre demande.',
        ], 'Cabinet enregistré avec succès. En attente de validation par un administrateur.', 201);
    } catch (\Exception $e) {
        return $this->errorResponse('Erreur lors de l\'inscription du cabinet', ['general' => $e->getMessage()], 500);
    }
}
}