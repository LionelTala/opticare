<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FactureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'commande_id' => $this->commande_id,
            'patient_id' => $this->patient_id,
            'cabinet_id' => $this->cabinet_id,
            'type' => $this->type,
            'montant' => $this->montant,
            'montant_paye' => $this->montant_paye,
            'solde_restant' => $this->soldeRestant(),
            'statut' => $this->statut,
            'date_emission' => $this->date_emission,
            'date_echeance' => $this->date_echeance,
            'date_paiement' => $this->date_paiement,
            'description' => $this->description,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'patient' => $this->whenLoaded('patient', fn() => [
                'id' => $this->patient->id,
                'nom' => $this->patient->nom,
                'prenom' => $this->patient->prenom,
                'telephone' => $this->patient->telephone,
            ]),
        ];
    }
}