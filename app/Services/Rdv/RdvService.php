<?php

namespace App\Services\Rdv;

use App\Models\CreneauHoraire;
use App\Models\RdvCreneau;
use App\Models\Cabinet;
use App\Models\User;
use App\Models\Patient;
use App\Services\Notification\NotificationService;
use App\Services\Email\EmailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RdvService
{
    protected NotificationService $notificationService;
    protected EmailService $emailService;

    public function __construct(NotificationService $notificationService, EmailService $emailService)
    {
        $this->notificationService = $notificationService;
        $this->emailService = $emailService;
    }

    /**
     * Créer une plage horaire
     */
    public function createCreneauHoraire(array $data, int $cabinetId): array
    {
        try {
            DB::beginTransaction();

            $cabinet = Cabinet::findOrFail($cabinetId);

            $creneau = CreneauHoraire::create([
                'cabinet_id' => $cabinet->id,
                'jour_semaine' => $data['jour_semaine'],
                'heure_debut' => $data['heure_debut'],
                'heure_fin' => $data['heure_fin'],
                'duree_rdv' => $data['duree_rdv'] ?? 30,
                'pause_debut' => $data['pause_debut'] ?? null,
                'pause_fin' => $data['pause_fin'] ?? null,
                'est_actif' => $data['est_actif'] ?? true,
            ]);

            DB::commit();

            return ['creneau' => $creneau];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création plage horaire : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Modifier une plage horaire
     */
    public function updateCreneauHoraire(array $data, int $cabinetId, int $creneauId): array
    {
        try {
            DB::beginTransaction();

            $creneau = CreneauHoraire::where('cabinet_id', $cabinetId)->findOrFail($creneauId);

            $creneau->update([
                'jour_semaine' => $data['jour_semaine'] ?? $creneau->jour_semaine,
                'heure_debut' => $data['heure_debut'] ?? $creneau->heure_debut,
                'heure_fin' => $data['heure_fin'] ?? $creneau->heure_fin,
                'duree_rdv' => $data['duree_rdv'] ?? $creneau->duree_rdv,
                'pause_debut' => $data['pause_debut'] ?? $creneau->pause_debut,
                'pause_fin' => $data['pause_fin'] ?? $creneau->pause_fin,
                'est_actif' => $data['est_actif'] ?? $creneau->est_actif,
            ]);

            DB::commit();

            return ['creneau' => $creneau];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur modification plage horaire : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Supprimer une plage horaire
     */
    public function deleteCreneauHoraire(int $cabinetId, int $creneauId): bool
    {
        try {
            DB::beginTransaction();

            $creneau = CreneauHoraire::where('cabinet_id', $cabinetId)->findOrFail($creneauId);
            $creneau->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression plage horaire : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lister les plages horaires d'un cabinet
     */
    public function getCreneauxHoraires(int $cabinetId): array
    {
        try {
            $jourOrdre = [
                'lundi' => 1,
                'mardi' => 2,
                'mercredi' => 3,
                'jeudi' => 4,
                'vendredi' => 5,
                'samedi' => 6,
                'dimanche' => 7
            ];

            $creneaux = CreneauHoraire::where('cabinet_id', $cabinetId)
                ->get()
                ->sortBy(function ($item) use ($jourOrdre) {
                    return $jourOrdre[$item->jour_semaine] ?? 8;
                })
                ->values();

            return ['creneaux' => $creneaux];
        } catch (\Exception $e) {
            Log::error('Erreur récupération plages horaires : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupérer les créneaux disponibles
     */
    public function getAvailableSlots(int $cabinetId, string $date): array
    {
        try {
            $reservedSlots = RdvCreneau::where('cabinet_id', $cabinetId)
                ->where('date', $date)
                ->whereIn('statut', ['reserve', 'confirme'])
                ->get()
                ->map(function ($rdv) {
                    return [
                        'heure_debut' => $rdv->heure_debut,
                        'heure_fin' => $rdv->heure_fin,
                    ];
                })
                ->toArray();

            $joursMap = [
                'monday' => 'lundi',
                'tuesday' => 'mardi',
                'wednesday' => 'mercredi',
                'thursday' => 'jeudi',
                'friday' => 'vendredi',
                'saturday' => 'samedi',
                'sunday' => 'dimanche',
            ];
            
            $jourFr = $joursMap[strtolower(date('l', strtotime($date)))] ?? null;
            
            if (!$jourFr) {
                return ['slots' => []];
            }

            $creneauxHoraires = CreneauHoraire::where('cabinet_id', $cabinetId)
                ->where('jour_semaine', $jourFr)
                ->where('est_actif', true)
                ->get();

            if ($creneauxHoraires->isEmpty()) {
                return ['slots' => []];
            }

            $slots = [];

            foreach ($creneauxHoraires as $creneau) {
                $duree = $creneau->duree_rdv;
                $debut = strtotime($creneau->heure_debut);
                $fin = strtotime($creneau->heure_fin);
                
                $pauseDebut = $creneau->pause_debut ? strtotime($creneau->pause_debut) : null;
                $pauseFin = $creneau->pause_fin ? strtotime($creneau->pause_fin) : null;

                $current = $debut;

                while ($current + ($duree * 60) <= $fin) {
                    $slotDebut = date('H:i', $current);
                    $slotFin = date('H:i', $current + ($duree * 60));
                    
                    $estDansPause = false;
                    if ($pauseDebut && $pauseFin) {
                        if ($current >= $pauseDebut && $current < $pauseFin) {
                            $estDansPause = true;
                        }
                    }

                    $estReserve = false;
                    foreach ($reservedSlots as $reserved) {
                        if ($reserved['heure_debut'] === $slotDebut && $reserved['heure_fin'] === $slotFin) {
                            $estReserve = true;
                            break;
                        }
                    }

                    $slots[] = [
                        'heure_debut' => $slotDebut,
                        'heure_fin' => $slotFin,
                        'disponible' => !$estDansPause && !$estReserve,
                        'est_dans_pause' => $estDansPause,
                        'est_reserve' => $estReserve,
                    ];

                    $current += $duree * 60;
                }
            }

            return ['slots' => $slots];
        } catch (\Exception $e) {
            Log::error('Erreur récupération créneaux disponibles : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Prendre un RDV
     */
    public function prendreRdv(array $data, ?int $userId = null): array
    {
        try {
            DB::beginTransaction();

            $cabinetId = $data['cabinet_id'];
            $date = $data['date'];
            $heureDebut = $data['heure_debut'];

            // 1. Vérifier que le créneau est disponible
            $existingRdv = RdvCreneau::where('cabinet_id', $cabinetId)
                ->where('date', $date)
                ->where('heure_debut', $heureDebut)
                ->whereIn('statut', ['reserve', 'confirme'])
                ->first();

            if ($existingRdv) {
                throw new \Exception('Ce créneau est déjà réservé.');
            }

            // 2. Déterminer le patient et la source
            $patientId = null;
            $source = 'visiteur';
            $visiteurNom = null;
            $visiteurPrenom = null;
            $visiteurTelephone = null;
            $visiteurEmail = null;

            if ($userId) {
                $user = User::with('patient')->find($userId);
                
                if ($user) {
                    if ($user->patient) {
                        $patientId = $user->patient->id;
                        $source = 'en_ligne';
                    } else {
                        $patient = Patient::create([
                            'user_id' => $userId,
                            'nom' => $user->nom,
                            'prenom' => $user->prenom,
                            'telephone' => $user->telephone,
                            'email' => $user->email,
                            'ville' => $user->ville,
                            'date_naissance' => null,
                            'adresse' => null,
                            'notes' => null,
                        ]);
                        $patientId = $patient->id;
                        $source = 'en_ligne';
                    }
                } else {
                    throw new \Exception('Utilisateur non trouvé.');
                }
            } else {
                $visiteurNom = $data['visiteur_nom'] ?? null;
                $visiteurPrenom = $data['visiteur_prenom'] ?? null;
                $visiteurTelephone = $data['visiteur_telephone'] ?? null;
                $visiteurEmail = $data['visiteur_email'] ?? null;

                if ($visiteurTelephone) {
                    $existingPatient = Patient::where('telephone', $visiteurTelephone)->first();
                    if ($existingPatient) {
                        $patientId = $existingPatient->id;
                        $source = 'visiteur';
                    } else {
                        $patient = Patient::create([
                            'user_id' => null,
                            'nom' => $visiteurNom ?? 'Visiteur',
                            'prenom' => $visiteurPrenom ?? 'Inconnu',
                            'telephone' => $visiteurTelephone,
                            'email' => $visiteurEmail,
                            'ville' => 'Non renseignée',
                            'date_naissance' => null,
                            'adresse' => null,
                            'notes' => null,
                        ]);
                        $patientId = $patient->id;
                        $source = 'visiteur';
                    }
                } else {
                    $patient = Patient::create([
                        'user_id' => null,
                        'nom' => 'Visiteur',
                        'prenom' => 'Anonyme',
                        'telephone' => null,
                        'email' => null,
                        'ville' => 'Non renseignée',
                        'date_naissance' => null,
                        'adresse' => null,
                        'notes' => null,
                    ]);
                    $patientId = $patient->id;
                    $source = 'visiteur';
                }
            }

            // 3. Calculer l'heure de fin
            $duree = 30;
            $creneauHoraire = CreneauHoraire::where('cabinet_id', $cabinetId)
                ->where('jour_semaine', strtolower(date('l', strtotime($date))))
                ->first();
            if ($creneauHoraire) {
                $duree = $creneauHoraire->duree_rdv;
            }

            $heureFin = date('H:i', strtotime($heureDebut) + ($duree * 60));

            // 4. Créer le RDV
            $rdv = RdvCreneau::create([
                'cabinet_id' => $cabinetId,
                'patient_id' => $patientId,
                'date' => $date,
                'heure_debut' => $heureDebut,
                'heure_fin' => $heureFin,
                'motif' => $data['motif'] ?? null,
                'statut' => $userId ? 'confirme' : 'reserve',
                'source' => $source,
                'visiteur_nom' => $visiteurNom,
                'visiteur_prenom' => $visiteurPrenom,
                'visiteur_telephone' => $visiteurTelephone,
                'visiteur_email' => $visiteurEmail,
                'notes' => $data['notes'] ?? null,
            ]);

            DB::commit();

            $rdv->load('patient');

            return [
                'rdv' => $rdv,
                'patient' => $rdv->patient,
                'statut' => $rdv->statut,
                'message' => $userId ? 'RDV confirmé avec succès.' : 'RDV en attente de confirmation par le cabinet.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur prise de RDV : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Annuler un RDV
     */
    public function annulerRdv(int $rdvId): array
    {
        try {
            DB::beginTransaction();

            $rdv = RdvCreneau::with('patient')->findOrFail($rdvId);

            if ($rdv->statut === 'annule') {
                throw new \Exception('Ce RDV est déjà annulé.');
            }

            if ($rdv->statut === 'termine' || $rdv->statut === 'non_honore') {
                throw new \Exception('Ce RDV ne peut plus être annulé.');
            }

            $rdv->statut = 'annule';
            $rdv->save();

            DB::commit();

            return [
                'rdv' => $rdv,
                'message' => 'RDV annulé avec succès.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur annulation RDV : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Confirmer un RDV (par le cabinet)
     */
    public function confirmerRdv(int $rdvId): array
    {
        try {
            DB::beginTransaction();

            $rdv = RdvCreneau::with('patient')->findOrFail($rdvId);

            if ($rdv->statut === 'confirme') {
                throw new \Exception('Ce RDV est déjà confirmé.');
            }

            if ($rdv->statut === 'annule' || $rdv->statut === 'termine' || $rdv->statut === 'non_honore') {
                throw new \Exception('Ce RDV ne peut pas être confirmé.');
            }

            $rdv->statut = 'confirme';
            $rdv->save();

            DB::commit();

            return [
                'rdv' => $rdv,
                'message' => 'RDV confirmé avec succès.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur confirmation RDV : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Marquer un RDV comme non honoré (patient absent)
     */
    public function nonHonoreRdv(int $rdvId): array
    {
        try {
            DB::beginTransaction();

            $rdv = RdvCreneau::with('patient')->findOrFail($rdvId);

            if ($rdv->statut === 'non_honore') {
                throw new \Exception('Ce RDV est déjà marqué comme non honoré.');
            }

            if ($rdv->statut === 'annule' || $rdv->statut === 'termine') {
                throw new \Exception('Ce RDV ne peut pas être marqué comme non honoré.');
            }

            $rdv->statut = 'non_honore';
            $rdv->save();

            DB::commit();

            return [
                'rdv' => $rdv,
                'message' => 'RDV marqué comme non honoré.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur marquage RDV non honoré : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Terminer un RDV (consultation effectuée)
     */
    public function terminerRdv(int $rdvId): array
    {
        try {
            DB::beginTransaction();

            $rdv = RdvCreneau::with('patient')->findOrFail($rdvId);

            if ($rdv->statut === 'termine') {
                throw new \Exception('Ce RDV est déjà terminé.');
            }

            if ($rdv->statut === 'annule' || $rdv->statut === 'non_honore') {
                throw new \Exception('Ce RDV ne peut pas être terminé.');
            }

            $rdv->statut = 'termine';
            $rdv->save();

            DB::commit();

            return [
                'rdv' => $rdv,
                'message' => 'RDV terminé avec succès.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur terminaison RDV : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Modifier un RDV (date/heure) - AVEC NOTIFICATIONS ET EMAILS
     */
    public function modifierRdv(array $data, int $rdvId): array
    {
        try {
            DB::beginTransaction();

            $rdv = RdvCreneau::with('patient')->findOrFail($rdvId);

            if ($rdv->statut === 'annule' || $rdv->statut === 'termine' || $rdv->statut === 'non_honore') {
                throw new \Exception('Ce RDV ne peut plus être modifié.');
            }

            // Sauvegarder les anciennes valeurs
            $ancienneDate = $rdv->date;
            $ancienneHeure = $rdv->heure_debut;

            // Vérifier que le nouveau créneau est disponible
            if (isset($data['date']) && isset($data['heure_debut'])) {
                $existingRdv = RdvCreneau::where('cabinet_id', $rdv->cabinet_id)
                    ->where('date', $data['date'])
                    ->where('heure_debut', $data['heure_debut'])
                    ->where('id', '!=', $rdvId)
                    ->whereIn('statut', ['reserve', 'confirme'])
                    ->first();

                if ($existingRdv) {
                    throw new \Exception('Ce créneau est déjà réservé.');
                }
            }

            // Mettre à jour
            $rdv->update($data);

            // Si date ou heure a changé
            $dateModifiee = isset($data['date']) || isset($data['heure_debut']);
            
            if ($dateModifiee) {
                // ✅ Notification pour le patient
                if ($rdv->patient && $rdv->patient->user_id) {
                    $user = User::find($rdv->patient->user_id);
                    if ($user) {
                        $this->notificationService->rdvModifie($user, [
                            'rdv_id' => $rdv->id,
                            'date' => $rdv->date,
                            'heure' => $rdv->heure_debut,
                            'ancienne_date' => $ancienneDate,
                            'ancienne_heure' => $ancienneHeure,
                            'cabinet_id' => $rdv->cabinet_id,
                        ]);

                        // ✅ Email au patient
                        if ($user->email) {
                            $cabinet = Cabinet::find($rdv->cabinet_id);
                            $this->emailService->sendRdvModifie(
                                $user->email,
                                $user->prenom . ' ' . $user->nom,
                                $cabinet->nom ?? 'Cabinet',
                                $rdv->date,
                                $rdv->heure_debut,
                                $ancienneDate,
                                $ancienneHeure
                            );
                        }
                    }
                }

               // ✅ Notification pour le cabinet (propriétaire)
                $cabinet = Cabinet::find($rdv->cabinet_id);
                if ($cabinet && $cabinet->proprietaire) {
                    $proprietaire = $cabinet->proprietaire;
                    $patientNom = $rdv->patient ? $rdv->patient->nom . ' ' . $rdv->patient->prenom : 'Visiteur';
                    
                    $this->notificationService->create([
                        'user_id' => $proprietaire->id,
                        'type' => 'rdv_modifie',
                        'title' => '📅 RDV modifié',
                        'message' => "Le RDV de {$patientNom} a été modifié. Nouvelle date : {$rdv->date} à {$rdv->heure_debut}.",
                        'data' => [
                            'rdv_id' => $rdv->id,
                            'date' => $rdv->date,
                            'heure' => $rdv->heure_debut,
                            'ancienne_date' => $ancienneDate,
                            'ancienne_heure' => $ancienneHeure,
                            'patient_nom' => $patientNom,
                            'cabinet_id' => $rdv->cabinet_id,
                        ],
                    ]);
                }
            }

            DB::commit();

            $rdv->load('patient');

            return [
                'rdv' => $rdv,
                'message' => 'RDV modifié avec succès.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur modification RDV : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupérer les RDV d'un patient
     */
    public function getRdvByPatient(int $patientId): array
    {
        try {
            $rdvs = RdvCreneau::with(['cabinet'])
                ->where('patient_id', $patientId)
                ->orderBy('date', 'desc')
                ->orderBy('heure_debut', 'desc')
                ->get();

            return [
                'rdvs' => $rdvs,
            ];
        } catch (\Exception $e) {
            Log::error('Erreur récupération RDV patient : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupérer les RDV d'un cabinet
     */
    public function getRdvByCabinet(int $cabinetId, ?string $date = null, ?string $statut = null): array
    {
        try {
            $query = RdvCreneau::with(['patient'])
                ->where('cabinet_id', $cabinetId);

            if ($date) {
                $query->where('date', $date);
            }

            if ($statut) {
                $query->where('statut', $statut);
            }

            $rdvs = $query->orderBy('date', 'desc')
                ->orderBy('heure_debut', 'desc')
                ->get();

            return [
                'rdvs' => $rdvs,
            ];
        } catch (\Exception $e) {
            Log::error('Erreur récupération RDV cabinet : ' . $e->getMessage());
            throw $e;
        }
    }
}