<?php

namespace App\Services\Admin;

use App\Models\Cabinet;
use App\Models\User;
use App\Services\Email\EmailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Notification\NotificationService;


class AdminService
{
    protected EmailService $emailService;
        protected NotificationService $notificationService;


  

    public function __construct(EmailService $emailService, NotificationService $notificationService)
    {
        $this->emailService = $emailService;
        $this->notificationService = $notificationService;
    }


    public function getCabinets(): array
    {
        try {
            $cabinets = Cabinet::with('proprietaire')
                ->orderBy('created_at', 'desc')
                ->get();

            return [
                'cabinets' => $cabinets,
            ];
        } catch (\Exception $e) {
            Log::error('Erreur récupération cabinets : ' . $e->getMessage());
            throw $e;
        }
    }

    public function verifyCabinet(int $cabinetId, string $action, ?string $motif = null): array
    {
        try {
            DB::beginTransaction();

            $cabinet = Cabinet::with('proprietaire')->findOrFail($cabinetId);
            $proprietaire = $cabinet->proprietaire;

            if ($action === 'verify') {
                $cabinet->status = 'valide';
                $cabinet->motif_refus = null;
                $cabinet->is_verified = true;
                $cabinet->valide_le = now();
                $cabinet->valide_par = auth()->id();

                $cabinet->save();

                // Envoyer l'email de validation
                $this->emailService->sendCabinetVerified($proprietaire, $cabinet->nom);
            } else {
                $cabinet->status = 'refuse';
                $cabinet->motif_refus = $motif;
                $cabinet->is_verified = false;
                $cabinet->valide_le = null;
                $cabinet->valide_par = null;

                $cabinet->save();

                // Envoyer l'email de refus avec le motif
                $this->emailService->sendCabinetRejected($proprietaire, $cabinet->nom, $motif ?? 'Motif non spécifié');
            }

            DB::commit();

            return [
                'cabinet' => $cabinet,
                'action' => $action,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur vérification cabinet : ' . $e->getMessage());
            throw $e;
        }
    }
    /**
 * Mettre à jour le profil d'un cabinet
 */
public function updateProfile(array $data, int $cabinetId): array
{
    try {
        DB::beginTransaction();

        $cabinet = Cabinet::findOrFail($cabinetId);

        // Vérifier que l'utilisateur connecté est le propriétaire
        $user = auth('sanctum')->user();
        if ($cabinet->proprietaire_id !== $user->id && !$user->isSuperAdmin()) {
            throw new \Exception('Vous n\'êtes pas autorisé à modifier ce cabinet.');
        }

        $cabinet->update($data);

        DB::commit();

        $cabinet->load('proprietaire');

        return [
            'cabinet' => $cabinet,
        ];
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erreur mise à jour profil cabinet : ' . $e->getMessage());
        throw $e;
    }
}
}