<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    use ApiResponseTrait;

    /**
     * Déconnexion (supprime le token courant)
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $token = $request->user()->currentAccessToken();

            if ($token) {
                $token->delete();
            }

            return $this->successResponse(null, 'Déconnexion réussie');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la déconnexion',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Déconnexion de tous les appareils (supprime tous les tokens)
     */
    public function logoutAllDevices(Request $request): JsonResponse
    {
        try {
            $request->user()->tokens()->delete();

            return $this->successResponse(null, 'Déconnexion de tous les appareils réussie');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la déconnexion',
                ['general' => $e->getMessage()],
                500
            );
        }
    }
}