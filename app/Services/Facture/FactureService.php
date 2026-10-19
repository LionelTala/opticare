<?php

namespace App\Services\Facture;

use App\Models\Facture;
use App\Models\Commande;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FactureService
{
    /**
     * Créer une facture pour une commande
     */
    public function createFromCommande(array $data, int $commandeId, int $userId): array
    {
        try {
            DB::beginTransaction();

            $commande = Commande::with(['consultation.patient'])->findOrFail($commandeId);

            // Générer un numéro unique
            $numero = 'FAC-' . date('Y') . '-' . str_pad(Facture::whereYear('created_at', date('Y'))->count() + 1, 5, '0', STR_PAD_LEFT);

            $facture = Facture::create([
                'commande_id' => $commande->id,
                'patient_id' => $commande->consultation->patient_id,
                'cabinet_id' => $commande->cabinet_id,
                'emise_par' => $userId,
                'numero' => $numero,
                'type' => $data['type'] ?? 'unique',
                'montant' => $data['montant'],
                'montant_paye' => $data['montant_paye'] ?? 0,
                'statut' => $data['statut'] ?? 'impaye',
                'date_emission' => $data['date_emission'] ?? now(),
                'date_echeance' => $data['date_echeance'] ?? null,
                'date_paiement' => $data['date_paiement'] ?? null,
                'description' => $data['description'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            DB::commit();

            return ['facture' => $facture];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création facture : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Liste des factures d'un cabinet
     */
    public function getByCabinet(int $cabinetId, ?string $statut = null, ?string $search = null): array
    {
        try {
            $query = Facture::with(['patient', 'commande'])
                ->where('cabinet_id', $cabinetId);

            if ($statut) {
                $query->where('statut', $statut);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', "%{$search}%")
                      ->orWhereHas('patient', function ($p) use ($search) {
                          $p->where('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%")
                            ->orWhere('telephone', 'like', "%{$search}%");
                      });
                });
            }

            $factures = $query->orderBy('date_emission', 'desc')->get();

            return ['factures' => $factures];
        } catch (\Exception $e) {
            Log::error('Erreur liste factures : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupérer une facture
     */
    public function getFacture(int $factureId): array
    {
        try {
            $facture = Facture::with(['patient', 'cabinet', 'commande', 'emetteur'])
                ->findOrFail($factureId);

            return ['facture' => $facture];
        } catch (\Exception $e) {
            Log::error('Erreur récupération facture : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Marquer une facture comme payée
     */
    public function marquerPayee(int $factureId, float $montantPaye): array
    {
        try {
            DB::beginTransaction();

            $facture = Facture::findOrFail($factureId);
            $facture->montant_paye += $montantPaye;

            if ($facture->montant_paye >= $facture->montant) {
                $facture->statut = 'paye';
                $facture->date_paiement = now();
            } else {
                $facture->statut = 'partiel';
            }

            $facture->save();

            DB::commit();

            return ['facture' => $facture];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur marquage facture : ' . $e->getMessage());
            throw $e;
        }
    }
}