<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Services\Admin\UserService;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\AdminUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponseTrait;

    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Liste de tous les utilisateurs
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $role = $request->query('role');
            $search = $request->query('search');
            
            $result = $this->userService->getUsers($role, $search);

            return $this->successResponse([
                'users' => AdminUserResource::collection($result['users']),
                'stats' => $result['stats'],
            ], 'Liste des utilisateurs récupérée avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la récupération des utilisateurs',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Récupérer un utilisateur
     */
    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->userService->getUser($id);

            return $this->successResponse([
                'user' => new AdminUserResource($result['user']),
            ], 'Utilisateur récupéré avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Utilisateur non trouvé',
                ['general' => $e->getMessage()],
                404
            );
        }
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $result = $this->userService->updateUser($request->validated(), $id);

            return $this->successResponse([
                'user' => new AdminUserResource($result['user']),
            ], 'Utilisateur mis à jour avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la mise à jour',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Activer/Désactiver un utilisateur
     */
    public function toggleActive(int $id): JsonResponse
    {
        try {
            $result = $this->userService->toggleActive($id);

            return $this->successResponse([
                'user' => new AdminUserResource($result['user']),
            ], $result['message']);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la modification du statut',
                ['general' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Réinitialiser le mot de passe d'un utilisateur
     */
    public function resetPassword(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string|min:8',
            ]);

            $result = $this->userService->resetPassword($id, $data['password']);

            return $this->successResponse(null, $result['message']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse(
                'Erreur de validation',
                $e->errors(),
                422
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Erreur lors de la réinitialisation du mot de passe',
                ['general' => $e->getMessage()],
                500
            );
        }
    }
}