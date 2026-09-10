<?php

namespace App\Console\Commands;

use App\Models\RdvCreneau;
use App\Models\User;
use App\Models\Cabinet;
use App\Services\Email\EmailService;
use App\Services\Notification\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EnvoyerRappelsRdv extends Command
{
    protected $signature = 'rdv:envoyer-rappels';
    protected $description = 'Envoie les rappels de RDV pour demain';

    protected EmailService $emailService;
    protected NotificationService $notificationService;

    public function __construct(EmailService $emailService, NotificationService $notificationService)
    {
        parent::__construct();
        $this->emailService = $emailService;
        $this->notificationService = $notificationService;
    }

    public function handle(): int
    {
        $this->info('🔔 Début de l\'envoi des rappels de RDV...');

        try {
            $demain = now()->addDay()->toDateString();
            $this->info("📅 Date de demain : {$demain}");

            // Debug : tous les RDV
            $tousLesRdv = RdvCreneau::select('id', 'date', 'statut', 'rappel_envoye')->get();
            $this->info("📋 Tous les RDV en base :");
            foreach ($tousLesRdv as $r) {
                $this->line("  - ID {$r->id} | Date: '{$r->date}' | Statut: {$r->statut} | Rappel: " . ($r->rappel_envoye ? 'oui' : 'non'));
            }

            // Récupérer les RDV de demain (confirmés ou réservés, sans rappel envoyé)
            $rdvs = RdvCreneau::with(['patient', 'cabinet'])
    ->whereDate('date', $demain)
    ->whereIn('statut', ['confirme', 'reserve'])
    ->where('rappel_envoye', false)
    ->get();

            if ($rdvs->isEmpty()) {
                $this->info('Aucun RDV à rappeler pour demain.');
                return Command::SUCCESS;
            }

            $this->info("📅 {$rdvs->count()} RDV trouvé(s) pour demain.");

            $successCount = 0;
            $errorCount = 0;

            foreach ($rdvs as $rdv) {
                try {
                    $this->envoyerRappel($rdv);

                    $rdv->rappel_envoye = true;
                    $rdv->rappel_envoye_le = now();
                    $rdv->save();

                    $successCount++;
                    $this->line("  ✅ Rappel envoyé pour le RDV #{$rdv->id}");
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->error("  ❌ Erreur pour le RDV #{$rdv->id} : " . $e->getMessage());
                    Log::error("Erreur rappel RDV #{$rdv->id} : " . $e->getMessage());
                }
            }

            $this->info("✅ Rappels envoyés : {$successCount}");
            if ($errorCount > 0) {
                $this->error("❌ Erreurs : {$errorCount}");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Erreur générale : ' . $e->getMessage());
            Log::error('Erreur commande rappels RDV : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Envoyer le rappel pour un RDV
     */
    private function envoyerRappel(RdvCreneau $rdv): void
    {
        $cabinet = $rdv->cabinet;

        // 1. Rappel au patient (si connecté et a un email)
        if ($rdv->patient && $rdv->patient->user_id) {
            $user = User::find($rdv->patient->user_id);
            if ($user) {
                // Notification BDD
                $this->notificationService->create([
                    'user_id' => $user->id,
                    'type' => 'rdv_rappel',
                    'title' => '⏰ Rappel de RDV',
                    'message' => "Rappel : vous avez un RDV demain à {$rdv->heure_debut} chez {$cabinet->nom}.",
                    'data' => [
                        'rdv_id' => $rdv->id,
                        'date' => $rdv->date,
                        'heure' => $rdv->heure_debut,
                        'cabinet_id' => $rdv->cabinet_id,
                    ],
                ]);

                // Email
                if ($user->email) {
                    $this->emailService->sendRdvRappel(
                        $user->email,
                        $user->prenom . ' ' . $user->nom,
                        $cabinet->nom ?? 'Cabinet',
                        $cabinet->adresse ?? '',
                        $rdv->date,
                        $rdv->heure_debut
                    );
                }
            }
        }

        // 2. Rappel au visiteur (si non connecté et a un email)
        if (!$rdv->patient_id && $rdv->visiteur_email) {
            $this->emailService->sendRdvRappel(
                $rdv->visiteur_email,
                $rdv->visiteur_prenom . ' ' . $rdv->visiteur_nom,
                $cabinet->nom ?? 'Cabinet',
                $cabinet->adresse ?? '',
                $rdv->date,
                $rdv->heure_debut
            );
        }
    }
}