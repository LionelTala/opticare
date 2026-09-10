<?php

namespace App\Services\Commande;

use App\Models\Commande;
use App\Models\Consultation;
use App\Models\User;
use App\Models\Cabinet;
use App\Services\Notification\NotificationService;
use App\Services\Email\EmailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommandeService
{
    protected NotificationService $notificationService;
    protected EmailService $emailService;

    public function __construct(NotificationService $notificationService, EmailService $emailService)
    {
        $this->notificationService = $notificationService;
        $this->emailService = $emailService;
    }

    /**
     * Créer une commande
     */
    public function createCommande(array $data, int $opticienId): array
    {
        try {
            DB::beginTransaction();

            $consultation = Consultation::findOrFail($data['consultation_id']);
            $cabinetId = $consultation->cabinet_id;

            $commande = Commande::create([
                'consultation_id' => $data['consultation_id'],
                'cabinet_id' => $cabinetId,
                'opticien_id' => $opticienId,
                'numero_monture' => $data['numero_monture'] ?? null,
                'type_verre' => $data['type_verre'] ?? null,
                'teinte' => $data['teinte'] ?? null,
                'description_foyers' => $data['description_foyers'] ?? null,
                'port' => $data['port'] ?? null,
                'antireflet' => $data['antireflet'] ?? false,
                'diag_od_sphere' => $data['diag_od_sphere'] ?? null,
                'diag_od_cylindre' => $data['diag_od_cylindre'] ?? null,
                'diag_od_axe' => $data['diag_od_axe'] ?? null,
                'diag_od_addition' => $data['diag_od_addition'] ?? null,
                'diag_og_sphere' => $data['diag_og_sphere'] ?? null,
                'diag_og_cylindre' => $data['diag_og_cylindre'] ?? null,
                'diag_og_axe' => $data['diag_og_axe'] ?? null,
                'diag_og_addition' => $data['diag_og_addition'] ?? null,
                'statut' => 'initie',
                'notes' => $data['notes'] ?? null,
            ]);

            DB::commit();

            $commande->load(['consultation', 'opticien']);

            return [
                'commande' => $commande,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création commande : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupérer une commande
     */
    public function getCommande(int $id): array
    {
        try {
            $commande = Commande::with(['consultation', 'opticien'])->findOrFail($id);

            return [
                'commande' => $commande,
            ];
        } catch (\Exception $e) {
            Log::error('Erreur récupération commande : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mettre à jour une commande - AVEC NOTIFICATIONS ET EMAILS
     */
    public function updateCommande(array $data, int $id): array
    {
        try {
            DB::beginTransaction();

            $commande = Commande::findOrFail($id);

            if ($commande->isTerminee()) {
                throw new \Exception('Cette commande est terminée et ne peut plus être modifiée.');
            }

            // Vérifier si le statut devient 'termine'
            $statutDevientTermine = isset($data['statut']) && $data['statut'] === 'termine';

            $commande->update($data);

            // Si la commande est terminée, envoyer notifications et email
            if ($statutDevientTermine) {
                $patient = $commande->consultation->patient;
                if ($patient && $patient->user_id) {
                    $user = User::find($patient->user_id);
                    if ($user) {
                        // ✅ Notification BDD
                        $this->notificationService->commandeTerminee($user, [
                            'commande_id' => $commande->id,
                            'numero_monture' => $commande->numero_monture,
                            'cabinet_id' => $commande->cabinet_id,
                        ]);

                        // ✅ Email
                        if ($user->email) {
                            $cabinet = Cabinet::find($commande->cabinet_id);
                            $this->emailService->sendCommandeTerminee(
                                $user->email,
                                $user->prenom . ' ' . $user->nom,
                                $commande->id,
                                $cabinet->nom ?? 'Cabinet'
                            );
                        }
                    }
                }
            }

            DB::commit();

            $commande->load(['consultation', 'opticien']);

            return [
                'commande' => $commande,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour commande : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupérer les commandes d'une consultation
     */
    public function getCommandesByConsultation(int $consultationId): array
    {
        try {
            $commandes = Commande::with(['opticien'])
                ->where('consultation_id', $consultationId)
                ->orderBy('created_at', 'desc')
                ->get();

            return [
                'commandes' => $commandes,
            ];
        } catch (\Exception $e) {
            Log::error('Erreur récupération commandes consultation : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupérer les commandes d'un cabinet
     */
    public function getCommandesByCabinet(int $cabinetId): array
    {
        try {
            $commandes = Commande::with(['consultation', 'opticien'])
                ->where('cabinet_id', $cabinetId)
                ->orderBy('created_at', 'desc')
                ->get();

            return [
                'commandes' => $commandes,
            ];
        } catch (\Exception $e) {
            Log::error('Erreur récupération commandes cabinet : ' . $e->getMessage());
            throw $e;
        }
    }
}