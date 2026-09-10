<?php

namespace App\Services\Statistique;

use App\Models\RdvCreneau;
use App\Models\Consultation;
use App\Models\Commande;
use App\Models\Patient;
use App\Models\Cabinet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StatistiqueService
{
    /**
     * Récupérer toutes les statistiques d'un cabinet
     */
    public function getStatistiques(int $cabinetId): array
    {
        try {
            $cabinet = Cabinet::findOrFail($cabinetId);
            
            return [
                'cabinet' => [
                    'id' => $cabinet->id,
                    'nom' => $cabinet->nom,
                    'note_moyenne' => $cabinet->note_moyenne,
                    'nb_avis' => $cabinet->nb_avis,
                ],
                'rdv' => $this->getStatistiquesRdv($cabinetId),
                'consultations' => $this->getStatistiquesConsultations($cabinetId),
                'commandes' => $this->getStatistiquesCommandes($cabinetId),
                'patients' => $this->getStatistiquesPatients($cabinetId),
                'revenus' => $this->getStatistiquesRevenus($cabinetId),
                'tendances' => $this->getTendances($cabinetId),
            ];
        } catch (\Exception $e) {
            Log::error('Erreur statistiques : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Statistiques des RDV
     */
    private function getStatistiquesRdv(int $cabinetId): array
    {
        $total = RdvCreneau::where('cabinet_id', $cabinetId)->count();
        $confirme = RdvCreneau::where('cabinet_id', $cabinetId)->where('statut', 'confirme')->count();
        $termine = RdvCreneau::where('cabinet_id', $cabinetId)->where('statut', 'termine')->count();
        $annule = RdvCreneau::where('cabinet_id', $cabinetId)->where('statut', 'annule')->count();
        $nonHonore = RdvCreneau::where('cabinet_id', $cabinetId)->where('statut', 'non_honore')->count();
        $reserve = RdvCreneau::where('cabinet_id', $cabinetId)->where('statut', 'reserve')->count();

        // Ce mois-ci
        $debutMois = Carbon::now()->startOfMonth();
        $rdvMois = RdvCreneau::where('cabinet_id', $cabinetId)
            ->where('created_at', '>=', $debutMois)
            ->count();

        // Cette semaine
        $debutSemaine = Carbon::now()->startOfWeek();
        $rdvSemaine = RdvCreneau::where('cabinet_id', $cabinetId)
            ->where('created_at', '>=', $debutSemaine)
            ->count();

        // Aujourd'hui
        $rdvAujourdhui = RdvCreneau::where('cabinet_id', $cabinetId)
            ->where('date', Carbon::today())
            ->count();

        return [
            'total' => $total,
            'confirme' => $confirme,
            'termine' => $termine,
            'annule' => $annule,
            'non_honore' => $nonHonore,
            'reserve' => $reserve,
            'ce_mois' => $rdvMois,
            'cette_semaine' => $rdvSemaine,
            'aujourd_hui' => $rdvAujourdhui,
        ];
    }

    /**
     * Statistiques des consultations
     */
    private function getStatistiquesConsultations(int $cabinetId): array
    {
        $total = Consultation::where('cabinet_id', $cabinetId)->count();
        $terminees = Consultation::where('cabinet_id', $cabinetId)->where('statut', 'terminee')->count();
        $enCours = Consultation::where('cabinet_id', $cabinetId)->where('statut', 'en_cours')->count();

        // Ce mois-ci
        $debutMois = Carbon::now()->startOfMonth();
        $ceMois = Consultation::where('cabinet_id', $cabinetId)
            ->where('created_at', '>=', $debutMois)
            ->count();

        // Cette semaine
        $debutSemaine = Carbon::now()->startOfWeek();
        $cetteSemaine = Consultation::where('cabinet_id', $cabinetId)
            ->where('created_at', '>=', $debutSemaine)
            ->count();

        return [
            'total' => $total,
            'terminees' => $terminees,
            'en_cours' => $enCours,
            'ce_mois' => $ceMois,
            'cette_semaine' => $cetteSemaine,
        ];
    }

    /**
     * Statistiques des commandes
     */
    private function getStatistiquesCommandes(int $cabinetId): array
    {
        $total = Commande::where('cabinet_id', $cabinetId)->count();
        $initie = Commande::where('cabinet_id', $cabinetId)->where('statut', 'initie')->count();
        $enCours = Commande::where('cabinet_id', $cabinetId)->where('statut', 'en_cours')->count();
        $enVerification = Commande::where('cabinet_id', $cabinetId)->where('statut', 'en_verification')->count();
        $termine = Commande::where('cabinet_id', $cabinetId)->where('statut', 'termine')->count();

        return [
            'total' => $total,
            'initie' => $initie,
            'en_cours' => $enCours,
            'en_verification' => $enVerification,
            'termine' => $termine,
        ];
    }

    /**
     * Statistiques des patients
     */
    private function getStatistiquesPatients(int $cabinetId): array
    {
        // Patients du cabinet (via RDV et consultations)
        $patientsIds = RdvCreneau::where('cabinet_id', $cabinetId)
            ->whereNotNull('patient_id')
            ->pluck('patient_id')
            ->unique();

        $total = $patientsIds->count();

        // Nouveaux patients ce mois
        $debutMois = Carbon::now()->startOfMonth();
        $nouveauxMois = Patient::whereIn('id', $patientsIds)
            ->where('created_at', '>=', $debutMois)
            ->count();

        // Répartition par tranche d'âge
        $patients = Patient::whereIn('id', $patientsIds)->get();
        
        $tranches = [
            '0-18' => 0,
            '19-30' => 0,
            '31-45' => 0,
            '46-60' => 0,
            '60+' => 0,
            'non_renseigne' => 0,
        ];

        foreach ($patients as $patient) {
            if (!$patient->date_naissance) {
                $tranches['non_renseigne']++;
                continue;
            }
            
            $age = Carbon::parse($patient->date_naissance)->age;
            
            if ($age <= 18) $tranches['0-18']++;
            elseif ($age <= 30) $tranches['19-30']++;
            elseif ($age <= 45) $tranches['31-45']++;
            elseif ($age <= 60) $tranches['46-60']++;
            else $tranches['60+']++;
        }

        // Répartition par ville
        $villes = Patient::whereIn('id', $patientsIds)
            ->select('ville', DB::raw('count(*) as total'))
            ->groupBy('ville')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        return [
            'total' => $total,
            'nouveaux_ce_mois' => $nouveauxMois,
            'tranches_age' => $tranches,
            'top_villes' => $villes,
        ];
    }

    /**
     * Statistiques des revenus
     */
    private function getStatistiquesRevenus(int $cabinetId): array
    {
        // Revenus totaux
        $total = Commande::where('cabinet_id', $cabinetId)
            ->where('statut', 'termine')
            ->sum('prix_paye');

        // Revenus ce mois
        $debutMois = Carbon::now()->startOfMonth();
        $ceMois = Commande::where('cabinet_id', $cabinetId)
            ->where('statut', 'termine')
            ->where('paye_le', '>=', $debutMois)
            ->sum('prix_paye');

        // Revenus mois précédent
        $debutMoisPrecedent = Carbon::now()->subMonth()->startOfMonth();
        $finMoisPrecedent = Carbon::now()->subMonth()->endOfMonth();
        $moisPrecedent = Commande::where('cabinet_id', $cabinetId)
            ->where('statut', 'termine')
            ->whereBetween('paye_le', [$debutMoisPrecedent, $finMoisPrecedent])
            ->sum('prix_paye');

        // Revenus en attente
        $enAttente = Commande::where('cabinet_id', $cabinetId)
            ->where('statut', 'termine')
            ->whereNull('paye_le')
            ->sum('prix');

        // Évolution
        $evolution = $moisPrecedent > 0 
            ? round((($ceMois - $moisPrecedent) / $moisPrecedent) * 100, 1) 
            : 0;

        return [
            'total' => round($total ?? 0, 2),
            'ce_mois' => round($ceMois ?? 0, 2),
            'mois_precedent' => round($moisPrecedent ?? 0, 2),
            'en_attente' => round($enAttente ?? 0, 2),
            'evolution_pourcentage' => $evolution,
        ];
    }

    /**
     * Tendances sur les 6 derniers mois
     */
    private function getTendances(int $cabinetId): array
    {
        $tendances = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $debut = Carbon::now()->subMonths($i)->startOfMonth();
            $fin = Carbon::now()->subMonths($i)->endOfMonth();
            $mois = Carbon::now()->subMonths($i)->format('Y-m');
            $labelMois = Carbon::now()->subMonths($i)->translatedFormat('M Y');

            $rdv = RdvCreneau::where('cabinet_id', $cabinetId)
                ->whereBetween('created_at', [$debut, $fin])
                ->count();

            $consultations = Consultation::where('cabinet_id', $cabinetId)
                ->whereBetween('created_at', [$debut, $fin])
                ->count();

            $commandes = Commande::where('cabinet_id', $cabinetId)
                ->whereBetween('created_at', [$debut, $fin])
                ->count();

            $revenus = Commande::where('cabinet_id', $cabinetId)
                ->where('statut', 'termine')
                ->whereBetween('paye_le', [$debut, $fin])
                ->sum('prix_paye');

            $tendances[] = [
                'mois' => $mois,
                'label' => $labelMois,
                'rdv' => $rdv,
                'consultations' => $consultations,
                'commandes' => $commandes,
                'revenus' => round($revenus ?? 0, 2),
            ];
        }

        return $tendances;
    }
}