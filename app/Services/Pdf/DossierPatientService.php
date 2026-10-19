<?php

namespace App\Services\Pdf;

use App\Models\Patient;
use App\Models\Cabinet;
use App\Models\Consultation;
use App\Models\Ordonnance;
use App\Models\Commande;
use App\Models\Facture;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class DossierPatientService
{
    /**
     * Générer le PDF du dossier visuel d'un patient
     */
    public function genererDossier(Patient $patient, Cabinet $cabinet): \Barryvdh\DomPDF\PDF
    {
        try {
            // Charger toutes les données du patient pour ce cabinet
            $consultations = Consultation::with(['opticien'])
                ->where('patient_id', $patient->id)
                ->where('cabinet_id', $cabinet->id)
                ->orderBy('date_consultation', 'desc')
                ->get();

            $ordonnances = Ordonnance::with(['opticien'])
                ->where('patient_id', $patient->id)
                ->where('cabinet_id', $cabinet->id)
                ->orderBy('date_emission', 'desc')
                ->get();

            $commandes = Commande::whereHas('consultation', function ($q) use ($patient, $cabinet) {
                    $q->where('patient_id', $patient->id)
                      ->where('cabinet_id', $cabinet->id);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            $factures = Facture::where('patient_id', $patient->id)
                ->where('cabinet_id', $cabinet->id)
                ->orderBy('date_emission', 'desc')
                ->get();

            $data = [
                'patient' => $patient,
                'cabinet' => $cabinet,
                'consultations' => $consultations,
                'ordonnances' => $ordonnances,
                'commandes' => $commandes,
                'factures' => $factures,
            ];

            $pdf = Pdf::loadView('pdf.dossier-patient', $data);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

            return $pdf;
        } catch (\Exception $e) {
            Log::error('Erreur génération PDF dossier patient : ' . $e->getMessage());
            throw $e;
        }
    }
}