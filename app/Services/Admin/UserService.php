<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserService
{
    /**
     * Récupérer tous les utilisateurs avec filtres
     */
    public function getUsers(?string $role = null, ?string $search = null): array
    {
        try {
            $query = User::with('cabinet')->orderBy('created_at', 'desc');

            if ($role) {
                $query->where('role', $role);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('telephone', 'like', "%{$search}%");
                });
            }

            $users = $query->get();

            // Statistiques par rôle
            $stats = [
                'total' => User::count(),
                'super_admin' => User::where('role', 'super_admin')->count(),
                'proprietaire' => User::where('role', 'proprietaire')->count(),
                'opticien' => User::where('role', 'opticien')->count(),
                'secretaire' => User::where('role', 'secretaire')->count(),
                'patient' => User::where('role', 'patient')->count(),
                'actifs' => User::where('is_active', true)->count(),
                'inactifs' => User::where('is_active', false)->count(),
            ];

            return [
                'users' => $users,
                'stats' => $stats,
            ];
        } catch (\Exception $e) {
            Log::error('Erreur récupération utilisateurs : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupérer un utilisateur
     */
    public function getUser(int $id): array
    {
        try {
            $user = User::with('cabinet')->findOrFail($id);

            return [
                'user' => $user,
            ];
        } catch (\Exception $e) {
            Log::error('Erreur récupération utilisateur : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function updateUser(array $data, int $id): array
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);

            // Empêcher la modification d'un super_admin par un autre super_admin
            // (sauf par lui-même)
            $currentUser = auth('sanctum')->user();
            if ($user->isSuperAdmin() && $currentUser->id !== $user->id) {
                throw new \Exception('Vous ne pouvez pas modifier un autre super administrateur.');
            }

            // Empêcher de se désactiver soi-même
            if ($user->id === $currentUser->id && isset($data['is_active']) && $data['is_active'] === false) {
                throw new \Exception('Vous ne pouvez pas désactiver votre propre compte.');
            }

            $user->update($data);

            DB::commit();

            $user->load('cabinet');

            return [
                'user' => $user,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour utilisateur : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Activer/Désactiver un utilisateur
     */
    public function toggleActive(int $id): array
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);
            $currentUser = auth('sanctum')->user();

            if ($user->id === $currentUser->id) {
                throw new \Exception('Vous ne pouvez pas modifier votre propre statut.');
            }

            $user->is_active = !$user->is_active;

            // Si c'est un employé, synchroniser le statut_employe
            if (in_array($user->role, ['opticien', 'secretaire'])) {
                $user->statut_employe = $user->is_active ? 'actif' : 'inactif';
                if (!$user->is_active) {
                    $user->depart_le = now();
                } else {
                    $user->depart_le = null;
                }
            }

            $user->save();

            DB::commit();

            return [
                'user' => $user,
                'message' => $user->is_active ? 'Compte activé avec succès.' : 'Compte désactivé avec succès.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur toggle utilisateur : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Réinitialiser le mot de passe d'un utilisateur
     */
    public function resetPassword(int $id, string $newPassword): array
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);

            $user->password = \Illuminate\Support\Facades\Hash::make($newPassword);
            $user->save();

            // Révoquer tous les tokens existants
            $user->tokens()->delete();

            DB::commit();

            return [
                'user' => $user,
                'message' => 'Mot de passe réinitialisé avec succès.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur reset password : ' . $e->getMessage());
            throw $e;
        }
    }
}