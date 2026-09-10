<?php

namespace App\Services\Personnel;

use App\Models\User;
use App\Models\Cabinet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PersonnelService
{
    /**
     * Créer un employé
     */
    public function createEmploye(array $data, int $cabinetId): array
    {
        try {
            DB::beginTransaction();

            $cabinet = Cabinet::findOrFail($cabinetId);

            $user = auth('sanctum')->user();
            if ($cabinet->proprietaire_id !== $user->id) {
                throw new \Exception('Vous n\'êtes pas autorisé à ajouter un employé à ce cabinet.');
            }

            $employe = User::create([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'] ?? null,
                'telephone' => $data['telephone'],
                'ville' => $data['ville'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'cabinet_id' => $cabinetId,
                'is_active' => true,
                'statut_employe' => 'actif',
                'poste' => $data['poste'] ?? null,
                'embauche_le' => $data['embauche_le'] ?? now(),
                'depart_le' => null,
            ]);

            DB::commit();

            return [
                'employe' => $employe,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création employé : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupérer les employés d'un cabinet
     */
    public function getEmployesByCabinet(int $cabinetId): array
    {
        try {
            $employes = User::where('cabinet_id', $cabinetId)
                ->whereIn('role', ['opticien', 'secretaire'])
                ->orderBy('created_at', 'desc')
                ->get();

            return [
                'employes' => $employes,
            ];
        } catch (\Exception $e) {
            Log::error('Erreur récupération employés : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupérer un employé
     */
    public function getEmploye(int $personnelId, int $cabinetId): array
    {
        try {
            $employe = User::whereIn('role', ['opticien', 'secretaire'])
                ->where('cabinet_id', $cabinetId)
                ->findOrFail($personnelId);

            return [
                'employe' => $employe,
            ];
        } catch (\Exception $e) {
            Log::error('Erreur récupération employé : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mettre à jour un employé
     */
    public function updateEmploye(array $data, int $personnelId, int $cabinetId): array
    {
        try {
            DB::beginTransaction();

            $employe = User::whereIn('role', ['opticien', 'secretaire'])
                ->where('cabinet_id', $cabinetId)
                ->findOrFail($personnelId);

            $user = auth('sanctum')->user();
            $cabinet = Cabinet::findOrFail($employe->cabinet_id);
            if ($cabinet->proprietaire_id !== $user->id && !$user->isSuperAdmin()) {
                throw new \Exception('Vous n\'êtes pas autorisé à modifier cet employé.');
            }

            if (isset($data['is_active']) && $data['is_active'] === false) {
                $data['statut_employe'] = 'inactif';
                $data['depart_le'] = $data['depart_le'] ?? now();
            }

            if (isset($data['is_active']) && $data['is_active'] === true) {
                $data['statut_employe'] = 'actif';
                $data['depart_le'] = null;
            }

            $employe->update($data);

            DB::commit();

            return [
                'employe' => $employe,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour employé : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Supprimer un employé (désactiver)
     */
    public function deleteEmploye(int $personnelId, int $cabinetId): bool
    {
        try {
            DB::beginTransaction();

            $employe = User::whereIn('role', ['opticien', 'secretaire'])
                ->where('cabinet_id', $cabinetId)
                ->findOrFail($personnelId);

            $user = auth('sanctum')->user();
            $cabinet = Cabinet::findOrFail($employe->cabinet_id);
            if ($cabinet->proprietaire_id !== $user->id && !$user->isSuperAdmin()) {
                throw new \Exception('Vous n\'êtes pas autorisé à supprimer cet employé.');
            }

            $employe->update([
                'is_active' => false,
                'statut_employe' => 'inactif',
                'depart_le' => now(),
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression employé : ' . $e->getMessage());
            throw $e;
        }
    }
}