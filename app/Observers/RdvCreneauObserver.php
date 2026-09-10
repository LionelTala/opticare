<?php

namespace App\Observers;

use App\Models\RdvCreneau;
use App\Models\User;
use App\Models\Cabinet;
use App\Services\Notification\NotificationService;
use App\Services\Email\EmailService;
use Illuminate\Support\Facades\Log;

class RdvCreneauObserver
{
    protected NotificationService $notificationService;
    protected EmailService $emailService;

    public function __construct(NotificationService $notificationService, EmailService $emailService)
    {
        $this->notificationService = $notificationService;
        $this->emailService = $emailService;
    }

    /**
     * Après création d'un RDV
     */
    public function created(RdvCreneau $rdv): void
    {
        try {
            // ===== 1. NOTIFICATIONS BDD =====

            // Notification pour le patient (si connecté)
            if ($rdv->patient && $rdv->patient->user_id) {
                $user = User::find($rdv->patient->user_id);
                if ($user) {
                    $statut = $rdv->statut === 'confirme' ? 'confirmé' : 'en attente';
                    $this->notificationService->create([
                        'user_id' => $user->id,
                        'type' => $rdv->statut === 'confirme' ? 'rdv_confirme' : 'rdv_attente',
                        'title' => $rdv->statut === 'confirme' ? '📅 RDV confirmé' : '📅 RDV en attente',
                        'message' => "Votre RDV du {$rdv->date} à {$rdv->heure_debut} est {$statut}.",
                        'data' => [
                            'rdv_id' => $rdv->id,
                            'date' => $rdv->date,
                            'heure' => $rdv->heure_debut,
                            'cabinet_id' => $rdv->cabinet_id,
                            'statut' => $rdv->statut,
                        ],
                    ]);
                }
            }

            // Notification pour le cabinet
            $cabinet = $rdv->cabinet;
            if ($cabinet && $cabinet->proprietaire) {
                $proprietaire = $cabinet->proprietaire;
                $patientNom = $rdv->patient ? $rdv->patient->nom . ' ' . $rdv->patient->prenom : 'Visiteur';

                $this->notificationService->create([
                    'user_id' => $proprietaire->id,
                    'type' => 'rdv_attente',
                    'title' => '📅 Nouveau RDV',
                    'message' => "Nouveau RDV de {$patientNom} le {$rdv->date} à {$rdv->heure_debut}.",
                    'data' => [
                        'rdv_id' => $rdv->id,
                        'date' => $rdv->date,
                        'heure' => $rdv->heure_debut,
                        'patient_nom' => $patientNom,
                        'cabinet_id' => $rdv->cabinet_id,
                    ],
                ]);
            }

            // ===== 2. EMAILS =====

            // Email au patient (si connecté et a un email)
            if ($rdv->patient && $rdv->patient->user_id) {
                $user = User::find($rdv->patient->user_id);
                if ($user && $user->email) {
                    $cabinet = Cabinet::find($rdv->cabinet_id);
                    $this->emailService->sendRdvConfirmation(
                        $user->email,
                        $user->prenom . ' ' . $user->nom,
                        $cabinet->nom ?? 'Cabinet',
                        $rdv->date,
                        $rdv->heure_debut
                    );
                }
            }

            // Email au visiteur (si non connecté et a un email)
            if (!$rdv->patient_id && $rdv->visiteur_email) {
                $cabinet = Cabinet::find($rdv->cabinet_id);
                $this->emailService->sendRdvConfirmation(
                    $rdv->visiteur_email,
                    $rdv->visiteur_prenom . ' ' . $rdv->visiteur_nom,
                    $cabinet->nom ?? 'Cabinet',
                    $rdv->date,
                    $rdv->heure_debut
                );
            }

            // ✅ Email au propriétaire (nouveau RDV)
            $cabinet = Cabinet::find($rdv->cabinet_id);
            if ($cabinet && $cabinet->proprietaire && $cabinet->proprietaire->email) {
                $proprietaire = $cabinet->proprietaire;
                $patientNom = $rdv->patient ? $rdv->patient->nom . ' ' . $rdv->patient->prenom : 'Visiteur';
                $this->emailService->sendNouveauRdvCabinet(
                    $proprietaire->email,
                    $proprietaire->prenom . ' ' . $proprietaire->nom,
                    $patientNom,
                    $rdv->date,
                    $rdv->heure_debut
                );
            }

        } catch (\Exception $e) {
            Log::error('Erreur notification/email création RDV : ' . $e->getMessage());
        }
    }

    /**
     * Après mise à jour d'un RDV
     */
    public function updated(RdvCreneau $rdv): void
    {
        try {
            if (!$rdv->isDirty('statut')) {
                return;
            }

            $ancienStatut = $rdv->getOriginal('statut');
            $nouveauStatut = $rdv->statut;

            $patientNom = $rdv->patient ? $rdv->patient->nom . ' ' . $rdv->patient->prenom : 'Visiteur';
            $cabinet = Cabinet::find($rdv->cabinet_id);
            $proprietaire = $cabinet ? $cabinet->proprietaire : null;

            // ===== RDV ANNULÉ =====
            if ($nouveauStatut === 'annule') {
                // --- Notification + Email pour le patient ---
                if ($rdv->patient && $rdv->patient->user_id) {
                    $user = User::find($rdv->patient->user_id);
                    if ($user) {
                        $this->notificationService->create([
                            'user_id' => $user->id,
                            'type' => 'rdv_annule',
                            'title' => '❌ RDV annulé',
                            'message' => "Votre RDV du {$rdv->date} à {$rdv->heure_debut} a été annulé.",
                            'data' => [
                                'rdv_id' => $rdv->id,
                                'date' => $rdv->date,
                                'heure' => $rdv->heure_debut,
                                'cabinet_id' => $rdv->cabinet_id,
                            ],
                        ]);

                        if ($user->email && $cabinet) {
                            $this->emailService->sendRdvAnnule(
                                $user->email,
                                $user->prenom . ' ' . $user->nom,
                                $cabinet->nom ?? 'Cabinet',
                                $rdv->date,
                                $rdv->heure_debut
                            );
                        }
                    }
                }

                // --- Notification + Email pour le propriétaire ---
                if ($proprietaire) {
                    $this->notificationService->create([
                        'user_id' => $proprietaire->id,
                        'type' => 'rdv_annule',
                        'title' => '❌ RDV annulé',
                        'message' => "Le RDV de {$patientNom} du {$rdv->date} à {$rdv->heure_debut} a été annulé.",
                        'data' => [
                            'rdv_id' => $rdv->id,
                            'date' => $rdv->date,
                            'heure' => $rdv->heure_debut,
                            'patient_nom' => $patientNom,
                            'cabinet_id' => $rdv->cabinet_id,
                        ],
                    ]);

                    if ($proprietaire->email) {
                        $this->emailService->sendRdvAnnuleCabinet(
                            $proprietaire->email,
                            $proprietaire->prenom . ' ' . $proprietaire->nom,
                            $patientNom,
                            $rdv->date,
                            $rdv->heure_debut
                        );
                    }
                }
            }

            // ===== RDV CONFIRMÉ (reserve → confirme) =====
            if ($ancienStatut === 'reserve' && $nouveauStatut === 'confirme') {
                if ($rdv->patient && $rdv->patient->user_id) {
                    $user = User::find($rdv->patient->user_id);
                    if ($user) {
                        $this->notificationService->create([
                            'user_id' => $user->id,
                            'type' => 'rdv_confirme',
                            'title' => '✅ RDV confirmé',
                            'message' => "Votre RDV du {$rdv->date} à {$rdv->heure_debut} a été confirmé par le cabinet.",
                            'data' => [
                                'rdv_id' => $rdv->id,
                                'date' => $rdv->date,
                                'heure' => $rdv->heure_debut,
                                'cabinet_id' => $rdv->cabinet_id,
                            ],
                        ]);
                    }
                }
            }

            // ===== RDV NON HONORÉ =====
            if ($nouveauStatut === 'non_honore') {
                if ($rdv->patient && $rdv->patient->user_id) {
                    $user = User::find($rdv->patient->user_id);
                    if ($user) {
                        $this->notificationService->create([
                            'user_id' => $user->id,
                            'type' => 'rdv_annule',
                            'title' => '⚠️ RDV non honoré',
                            'message' => "Vous ne vous êtes pas présenté à votre RDV du {$rdv->date} à {$rdv->heure_debut}.",
                            'data' => [
                                'rdv_id' => $rdv->id,
                                'date' => $rdv->date,
                                'heure' => $rdv->heure_debut,
                                'cabinet_id' => $rdv->cabinet_id,
                            ],
                        ]);
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('Erreur notification/email mise à jour RDV : ' . $e->getMessage());
        }
    }

    /**
     * Après suppression d'un RDV
     */
    public function deleted(RdvCreneau $rdv): void
    {
        try {
            $patientNom = $rdv->patient ? $rdv->patient->nom . ' ' . $rdv->patient->prenom : 'Visiteur';
            $cabinet = Cabinet::find($rdv->cabinet_id);
            $proprietaire = $cabinet ? $cabinet->proprietaire : null;

            // Notification pour le patient
            if ($rdv->patient && $rdv->patient->user_id) {
                $user = User::find($rdv->patient->user_id);
                if ($user) {
                    $this->notificationService->create([
                        'user_id' => $user->id,
                        'type' => 'rdv_annule',
                        'title' => '❌ RDV supprimé',
                        'message' => "Votre RDV du {$rdv->date} à {$rdv->heure_debut} a été supprimé.",
                        'data' => [
                            'rdv_id' => $rdv->id,
                            'date' => $rdv->date,
                            'heure' => $rdv->heure_debut,
                            'cabinet_id' => $rdv->cabinet_id,
                        ],
                    ]);
                }
            }

            // Notification pour le propriétaire
            if ($proprietaire) {
                $this->notificationService->create([
                    'user_id' => $proprietaire->id,
                    'type' => 'rdv_annule',
                    'title' => '❌ RDV supprimé',
                    'message' => "Le RDV de {$patientNom} du {$rdv->date} à {$rdv->heure_debut} a été supprimé.",
                    'data' => [
                        'rdv_id' => $rdv->id,
                        'date' => $rdv->date,
                        'heure' => $rdv->heure_debut,
                        'patient_nom' => $patientNom,
                        'cabinet_id' => $rdv->cabinet_id,
                    ],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur notification/email suppression RDV : ' . $e->getMessage());
        }
    }
}