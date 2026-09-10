<?php

namespace App\Services\Avis;

use App\Models\Avis;
use App\Models\Consultation;
use App\Models\Cabinet;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AvisService
{
    /**
     * Créer un avis
     */
    public function createAvis(array $data, int $userId): array
    {
        try {
            DB::beginTransaction();

            $consultation = Consultation::with('patient')->findOrFail($data['consultation_id']);

            // Vérifier que la consultation est terminée
            if ($consultation->statut !== 'terminee') {
                throw new \Exception('La consultation doit être terminée pour laisser un avis.');
            }

            // Vérifier que l'avis n'existe pas déjà
            if ($consultation->avis) {
                throw new \Exception('Un avis a déjà été donné pour cette consultation.');
            }

            // Vérifier que le patient connecté est bien le propriétaire de la consultation
            $user = \App\Models\User::with('patient')->find($userId);
            if (!$user || !$user->patient) {
                throw new \Exception('Patient non trouvé.');
            }

            if ($consultation->patient_id !== $user->patient->id) {
                throw new \Exception('Vous ne pouvez pas donner un avis sur cette consultation.');
            }

            $avis = Avis::create([
                'patient_id' => $user->patient->id,
                'cabinet_id' => $consultation->cabinet_id,
                'consultation_id' => $consultation->id,
                'note' => $data['note'],
                'commentaire' => $data['commentaire'] ?? null,
                'est_publie' => true,
            ]);

            DB::commit();

            $avis->load(['patient', 'cabinet']);

            return [
                'avis' => $avis,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création avis : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupérer les avis d'un cabinet
     */
    public function getAvisByCabinet(int $cabinetId): array
    {
        try {
            $avis = Avis::with('patient')
                ->where('cabinet_id', $cabinetId)
                ->where('est_publie', true)
                ->orderBy('created_at', 'desc')
                ->get();

            $moyenne = round($avis->avg('note') ?? 0, 1);
            $total = $avis->count();

            // Répartition par note (1 à 5)
            $repartition = [];
            for ($i = 1; $i <= 5; $i++) {
                $repartition[$i] = $avis->where('note', $i)->count();
            }

            return [
                'avis' => $avis,
                'moyenne' => $moyenne,
                'total' => $total,
                'repartition' => $repartition,
            ];
        } catch (\Exception $e) {
            Log::error('Erreur récupération avis cabinet : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupérer les avis d'un patient
     */
    public function getAvisByPatient(int $patientId): array
    {
        try {
            $avis = Avis::with('cabinet')
                ->where('patient_id', $patientId)
                ->orderBy('created_at', 'desc')
                ->get();

            return [
                'avis' => $avis,
            ];
        } catch (\Exception $e) {
            Log::error('Erreur récupération avis patient : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Modifier un avis
     */
    public function updateAvis(array $data, int $avisId, int $userId): array
    {
        try {
            DB::beginTransaction();

            $avis = Avis::findOrFail($avisId);

            // Vérifier que l'avis appartient au patient connecté
            $user = \App\Models\User::with('patient')->find($userId);
            if (!$user || !$user->patient || $avis->patient_id !== $user->patient->id) {
                throw new \Exception('Vous ne pouvez pas modifier cet avis.');
            }

            $avis->update([
                'note' => $data['note'] ?? $avis->note,
                'commentaire' => $data['commentaire'] ?? $avis->commentaire,
            ]);

            DB::commit();

            $avis->load(['patient', 'cabinet']);

            return [
                'avis' => $avis,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur modification avis : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Supprimer un avis (patient ou super_admin)
     */
    public function deleteAvis(int $avisId, int $userId): bool
    {
        try {
            DB::beginTransaction();

            $avis = Avis::findOrFail($avisId);

            $user = \App\Models\User::with('patient')->find($userId);
            
            // Vérifier que c'est bien le patient ou un super_admin
            if ($user->isSuperAdmin()) {
                // Super admin peut tout supprimer
            } elseif ($user->patient && $avis->patient_id === $user->patient->id) {
                // Patient peut supprimer son propre avis
            } else {
                throw new \Exception('Vous ne pouvez pas supprimer cet avis.');
            }

            $avis->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression avis : ' . $e->getMessage());
            throw $e;
        }
    }
}