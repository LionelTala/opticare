<?php

namespace App\Services\Consultation;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Cabinet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ConsultationService
{
    /**
     * Créer une consultation
     */
    public function createConsultation(array $data, int $opticienId): array
    {
        try {
            DB::beginTransaction();

            // Vérifier que le patient existe
            $patient = Patient::findOrFail($data['patient_id']);
            
            // Vérifier que le cabinet existe
            $cabinet = Cabinet::findOrFail($data['cabinet_id']);

            $consultation = Consultation::create([
                'patient_id' => $data['patient_id'],
                'cabinet_id' => $data['cabinet_id'],
                'opticien_id' => $opticienId,
                'rdv_id' => $data['rdv_id'] ?? null,
                'date_consultation' => $data['date_consultation'],
                'motif' => $data['motif'],
                'comment_connu' => $data['comment_connu'] ?? null,
                'ancienne_prescription_date' => $data['ancienne_prescription_date'] ?? null,
                'ancienne_prescription_od' => $data['ancienne_prescription_od'] ?? null,
                'ancienne_prescription_og' => $data['ancienne_prescription_og'] ?? null,
                'plainte_vision_flou_loin' => $data['plainte_vision_flou_loin'] ?? false,
                'plainte_vision_flou_pres' => $data['plainte_vision_flou_pres'] ?? false,
                'plainte_vision_double' => $data['plainte_vision_double'] ?? false,
                'plainte_demangeaisons' => $data['plainte_demangeaisons'] ?? false,
                'plainte_larmoiement' => $data['plainte_larmoiement'] ?? false,
                'plainte_autres' => $data['plainte_autres'] ?? null,
                'ecart_pupillaire' => $data['ecart_pupillaire'] ?? null,
                'od_sphere' => $data['od_sphere'] ?? null,
                'od_cylindre' => $data['od_cylindre'] ?? null,
                'od_axe' => $data['od_axe'] ?? null,
                'od_addition' => $data['od_addition'] ?? null,
                'od_acuite_loin' => $data['od_acuite_loin'] ?? null,
                'od_acuite_pres' => $data['od_acuite_pres'] ?? null,
                'og_sphere' => $data['og_sphere'] ?? null,
                'og_cylindre' => $data['og_cylindre'] ?? null,
                'og_axe' => $data['og_axe'] ?? null,
                'og_addition' => $data['og_addition'] ?? null,
                'og_acuite_loin' => $data['og_acuite_loin'] ?? null,
                'og_acuite_pres' => $data['og_acuite_pres'] ?? null,
                'observations' => $data['observations'] ?? null,
                'statut' => 'en_cours',
                'verrouillee' => false,
            ]);

            // Si un RDV est associé, mettre à jour son statut
            if ($consultation->rdv_id) {
                $rdv = $consultation->rdv;
                if ($rdv) {
                    $rdv->statut = 'termine';
                    $rdv->save();
                }
            }

            DB::commit();

            $consultation->load(['patient', 'opticien', 'rdv']);

            return [
                'consultation' => $consultation,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création consultation : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupérer une consultation
     */
    public function getConsultation(int $id): array
    {
        try {
            $consultation = Consultation::with(['patient', 'opticien', 'rdv'])->findOrFail($id);

            return [
                'consultation' => $consultation,
            ];
        } catch (\Exception $e) {
            Log::error('Erreur récupération consultation : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mettre à jour une consultation
     */
    public function updateConsultation(array $data, int $id): array
    {
        try {
            DB::beginTransaction();

            $consultation = Consultation::findOrFail($id);

            // Vérifier si la consultation est modifiable
            if (!$consultation->isModifiable()) {
                throw ValidationException::withMessages([
                    'consultation' => ['Cette consultation n\'est plus modifiable car elle est verrouillée ou terminée.'],
                ]);
            }

            $consultation->update($data);

            DB::commit();

            $consultation->load(['patient', 'opticien', 'rdv']);

            return [
                'consultation' => $consultation,
            ];
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour consultation : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Terminer une consultation (verrouiller)
     */
    public function terminerConsultation(int $id): array
    {
        try {
            DB::beginTransaction();

            $consultation = Consultation::findOrFail($id);

            $consultation->statut = 'terminee';
            $consultation->verrouillee = true;
            $consultation->save();

            DB::commit();

            return [
                'consultation' => $consultation,
                'message' => 'Consultation terminée et verrouillée avec succès.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur terminaison consultation : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupérer les consultations d'un patient
     */
    public function getConsultationsByPatient(int $patientId): array
    {
        try {
            $consultations = Consultation::with(['cabinet', 'opticien'])
                ->where('patient_id', $patientId)
                ->orderBy('date_consultation', 'desc')
                ->get();

            return [
                'consultations' => $consultations,
            ];
        } catch (\Exception $e) {
            Log::error('Erreur récupération consultations patient : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupérer les consultations d'un cabinet
     */
    public function getConsultationsByCabinet(int $cabinetId): array
    {
        try {
            $consultations = Consultation::with(['patient', 'opticien'])
                ->where('cabinet_id', $cabinetId)
                ->orderBy('date_consultation', 'desc')
                ->get();

            return [
                'consultations' => $consultations,
            ];
        } catch (\Exception $e) {
            Log::error('Erreur récupération consultations cabinet : ' . $e->getMessage());
            throw $e;
        }
    }
}