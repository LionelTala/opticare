<?php

namespace App\Services\Ordonnance;

use App\Models\Ordonnance;
use App\Models\Consultation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrdonnanceService
{
    /**
     * Créer une ordonnance depuis une consultation
     */
    public function createFromConsultation(array $data, int $consultationId, int $opticienId): array
    {
        try {
            DB::beginTransaction();

            $consultation = Consultation::with(['patient', 'cabinet'])->findOrFail($consultationId);

            if ($consultation->statut !== 'terminee') {
                throw new \Exception('La consultation doit être terminée pour générer une ordonnance.');
            }

            if ($consultation->ordonnance) {
                throw new \Exception('Une ordonnance existe déjà pour cette consultation.');
            }

            // Générer un numéro unique
            $numero = 'ORD-' . date('Y') . '-' . str_pad(Ordonnance::whereYear('created_at', date('Y'))->count() + 1, 5, '0', STR_PAD_LEFT);

            $ordonnance = Ordonnance::create([
                'consultation_id' => $consultation->id,
                'patient_id' => $consultation->patient_id,
                'cabinet_id' => $consultation->cabinet_id,
                'opticien_id' => $opticienId,
                'od_sphere' => $data['od_sphere'] ?? $consultation->od_sphere,
                'od_cylindre' => $data['od_cylindre'] ?? $consultation->od_cylindre,
                'od_axe' => $data['od_axe'] ?? $consultation->od_axe,
                'od_addition' => $data['od_addition'] ?? $consultation->od_addition,
                'og_sphere' => $data['og_sphere'] ?? $consultation->og_sphere,
                'og_cylindre' => $data['og_cylindre'] ?? $consultation->og_cylindre,
                'og_axe' => $data['og_axe'] ?? $consultation->og_axe,
                'og_addition' => $data['og_addition'] ?? $consultation->og_addition,
                'ecart_pupillaire' => $data['ecart_pupillaire'] ?? $consultation->ecart_pupillaire,
                'recommandations' => $data['recommandations'] ?? null,
                'numero' => $numero,
                'date_emission' => $data['date_emission'] ?? now(),
                'date_validite' => $data['date_validite'] ?? now()->addYear(),
            ]);

            DB::commit();

            return ['ordonnance' => $ordonnance];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création ordonnance : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Liste des ordonnances d'un cabinet
     */
    public function getByCabinet(int $cabinetId, ?string $search = null): array
    {
        try {
            $query = Ordonnance::with(['patient', 'opticien'])
                ->where('cabinet_id', $cabinetId);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', "%{$search}%")
                      ->orWhereHas('patient', function ($p) use ($search) {
                          $p->where('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%")
                            ->orWhere('telephone', 'like', "%{$search}%");
                      });
                });
            }

            $ordonnances = $query->orderBy('date_emission', 'desc')->get();

            return ['ordonnances' => $ordonnances];
        } catch (\Exception $e) {
            Log::error('Erreur liste ordonnances : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupérer une ordonnance
     */
    public function getOrdonnance(int $ordonnanceId): array
    {
        try {
            $ordonnance = Ordonnance::with(['patient', 'cabinet', 'opticien', 'consultation'])
                ->findOrFail($ordonnanceId);

            return ['ordonnance' => $ordonnance];
        } catch (\Exception $e) {
            Log::error('Erreur récupération ordonnance : ' . $e->getMessage());
            throw $e;
        }
    }
}