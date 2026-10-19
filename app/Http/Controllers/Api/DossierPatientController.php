<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Cabinet;
use App\Models\User;
use App\Services\Pdf\DossierPatientService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DossierPatientController extends Controller
{
    use ApiResponseTrait;

    protected DossierPatientService $dossierService;

    public function __construct(DossierPatientService $dossierService)
    {
        $this->dossierService = $dossierService;
    }

    /**
     * Télécharger le dossier visuel PDF d'un patient
     *
     * Accès autorisé :
     * - Le patient lui-même (via son user_id)
     * - Le personnel du cabinet qui suit le patient
     * - Le super_admin
     *
     * Paramètres :
     * - cabinet_id (query, requis pour le personnel) : ID du cabinet du demandeur
     */
    public function download(Request $request, int $patientId)
    {
        try {
            $user = auth('sanctum')->user();
            $patient = Patient::findOrFail($patientId);

            $cabinet = null;

            // Cas 1 : Patient lui-même
            if ($user->isPatient()) {
                if ($patient->user_id !== $user->id) {
                    return $this->errorResponse('Accès refusé', ['general' => 'Vous ne pouvez pas accéder au dossier d\'un autre patient.'], 403);
                }
                // Charger le cabinet depuis le query param (obligatoire pour un patient)
                $cabinetId = $request->query('cabinet_id');
                if (!$cabinetId) {
                    return $this->errorResponse('Erreur', ['general' => 'Paramètre cabinet_id requis.'], 422);
                }
                $cabinet = Cabinet::findOrFail($cabinetId);

                // Vérifier que le patient a bien un lien avec ce cabinet
                $aRdv = $patient->rdvs()->where('cabinet_id', $cabinetId)->exists();
                if (!$aRdv) {
                    return $this->errorResponse('Accès refusé', ['general' => 'Aucune donnée pour ce cabinet.'], 403);
                }
            }
            // Cas 2 : Personnel du cabinet
            elseif ($user->hasCabinetAccess()) {
                $cabinet = Cabinet::findOrFail($user->cabinet_id);

                // Vérifier que le patient appartient au cabinet
                $aRdv = $patient->rdvs()->where('cabinet_id', $user->cabinet_id)->exists();
                $aLienPivot = $patient->cabinets()->where('cabinet_id', $user->cabinet_id)->exists();

                if (!$aRdv && !$aLienPivot) {
                    return $this->errorResponse('Accès refusé', ['general' => 'Ce patient n\'appartient pas à votre cabinet.'], 403);
                }
            }
            // Cas 3 : Super admin
            elseif ($user->isSuperAdmin()) {
                $cabinetId = $request->query('cabinet_id');
                if (!$cabinetId) {
                    return $this->errorResponse('Erreur', ['general' => 'Paramètre cabinet_id requis pour le super_admin.'], 422);
                }
                $cabinet = Cabinet::findOrFail($cabinetId);
            }
            else {
                return $this->errorResponse('Accès refusé', ['general' => 'Rôle non autorisé.'], 403);
            }

            // Générer le PDF
            $pdf = $this->dossierService->genererDossier($patient, $cabinet);

            $filename = 'dossier-' . strtolower($patient->nom) . '-' . strtolower($patient->prenom) . '-' . now()->format('Y-m-d') . '.pdf';

            return $pdf->download($filename);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Non trouvé', ['general' => 'Patient ou cabinet introuvable.'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur téléchargement dossier PDF : ' . $e->getMessage());
            return $this->errorResponse('Erreur génération PDF', ['general' => $e->getMessage()], 500);
        }
    }
}