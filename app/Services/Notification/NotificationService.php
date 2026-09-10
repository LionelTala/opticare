<?php

namespace App\Services\Notification;

use App\Models\Notification;
use App\Models\User;
use App\Services\Email\EmailService;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Créer une notification en base de données
     */
    public function create(array $data): Notification
    {
        try {
            return Notification::create([
                'user_id' => $data['user_id'],
                'type' => $data['type'],
                'title' => $data['title'],
                'message' => $data['message'],
                'data' => $data['data'] ?? null,
                'is_read' => false,
                'read_at' => null,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur création notification : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        try {
            $notification = Notification::where('user_id', $userId)
                ->findOrFail($notificationId);
            
            $notification->markAsRead();
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur marquage notification comme lue : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(int $userId): bool
    {
        try {
            Notification::where('user_id', $userId)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur marquage toutes les notifications comme lues : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupérer les notifications d'un utilisateur
     */
    public function getUserNotifications(int $userId, int $limit = 50): array
    {
        try {
            $notifications = Notification::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            $nonLuCount = Notification::where('user_id', $userId)
                ->where('is_read', false)
                ->count();

            return [
                'notifications' => $notifications,
                'non_lu_count' => $nonLuCount,
            ];
        } catch (\Exception $e) {
            Log::error('Erreur récupération notifications : ' . $e->getMessage());
            throw $e;
        }
    }

    // ============================================
    // DÉCLENCHEURS SPÉCIFIQUES (garder ces 2)
    // ============================================

    /**
     * Notification : Cabinet validé
     */
    public function cabinetValide(User $user, string $cabinetNom): void
    {
        $this->create([
            'user_id' => $user->id,
            'type' => 'cabinet_valide',
            'title' => '✅ Cabinet validé !',
            'message' => "Votre cabinet \"{$cabinetNom}\" a été validé avec succès.",
            'data' => ['cabinet_nom' => $cabinetNom],
        ]);

        $this->emailService->sendCabinetVerified($user, $cabinetNom);
    }

    /**
     * Notification : Cabinet refusé
     */
    public function cabinetRefuse(User $user, string $cabinetNom, string $motif): void
    {
        $this->create([
            'user_id' => $user->id,
            'type' => 'cabinet_refuse',
            'title' => '❌ Cabinet refusé',
            'message' => "Votre cabinet \"{$cabinetNom}\" a été refusé. Motif : {$motif}",
            'data' => ['cabinet_nom' => $cabinetNom, 'motif' => $motif],
        ]);

        $this->emailService->sendCabinetRejected($user, $cabinetNom, $motif);
    }

    /**
     * Notification : RDV modifié
     */
    public function rdvModifie(User $user, array $data): void
    {
        $this->create([
            'user_id' => $user->id,
            'type' => 'rdv_modifie',
            'title' => '📅 RDV modifié',
            'message' => "Votre RDV du {$data['date']} à {$data['heure']} a été modifié.",
            'data' => $data,
        ]);
    }

    /**
     * Notification : Commande terminée
     */
    public function commandeTerminee(User $user, array $data): void
    {
        $this->create([
            'user_id' => $user->id,
            'type' => 'commande_terminee',
            'title' => '👓 Commande terminée',
            'message' => "Votre commande #{$data['commande_id']} est terminée.",
            'data' => $data,
        ]);
    }
}