<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use ApiResponseTrait;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            return $this->successResponse([
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'role' => $result['role'],
            ], 'Connexion réussie. Bonjour ' . $result['user']->prenom . ' !');
        } catch (ValidationException $e) {
            return $this->errorResponse(
                'Erreur de connexion',
                $e->errors(),
                401
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la connexion',
                ['general' => $e->getMessage()],
                500
            );
        }
    }
}