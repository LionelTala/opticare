<?php

namespace App\Services\Patient;

use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PatientService
{
    /**
     * Liste des patients d'un cabinet
     * Un patient appartient au cabinet s'il a un RDV OU s'il est lié via la table pivot
     */
    public function getPatientsByCabinet(int $cabinetId, ?string $search = null): array
    {
        try {
            $query = Patient::query()
                ->where(function ($q) use ($cabinetId) {
                    // Patients qui ont eu un RDV dans ce cabinet
                    $q->whereHas('rdvs', function ($rdv) use ($cabinetId) {
                        $rdv->where('cabinet_id', $cabinetId);
                    })
                    // OU patients liés via la table pivot (patients internes)
                    ->orWhereHas('cabinets', function ($c) use ($cabinetId) {
                        $c->where('cabinet_id', $cabinetId);
                    });
                });

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('telephone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $patients = $query->orderBy('nom')->orderBy('prenom')->get();

            return ['patients' => $patients];
        } catch (\Exception $e) {
            Log::error('Erreur liste patients cabinet : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Créer un patient (patient interne au cabinet)
     */
    public function createPatient(array $data, int $cabinetId): array
    {
        try {
            DB::beginTransaction();

            $patient = Patient::create([
                'user_id' => null, // Patient interne, pas de compte
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'telephone' => $data['telephone'],
                'email' => $data['email'] ?? null,
                'ville' => $data['ville'] ?? 'Non renseignée',
                'date_naissance' => $data['date_naissance'] ?? null,
                'adresse' => $data['adresse'] ?? null,
                'notes' => null,
            ]);

            // Lier au cabinet
            $patient->cabinets()->attach($cabinetId, [
                'est_interne' => true,
                'date_inscription' => now(),
            ]);

            DB::commit();

            return ['patient' => $patient];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création patient : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Détail d'un patient (avec vérification d'appartenance au cabinet)
     */
    public function getPatient(int $patientId, int $cabinetId): array
    {
        try {
            $patient = Patient::where(function ($q) use ($cabinetId) {
                    $q->whereHas('rdvs', fn($rdv) => $rdv->where('cabinet_id', $cabinetId))
                      ->orWhereHas('cabinets', fn($c) => $c->where('cabinet_id', $cabinetId));
                })
                ->findOrFail($patientId);

            return ['patient' => $patient];
        } catch (\Exception $e) {
            Log::error('Erreur récupération patient : ' . $e->getMessage());
            throw $e;
        }
    }
}