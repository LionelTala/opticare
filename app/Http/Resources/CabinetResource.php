<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CabinetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'adresse' => $this->adresse,
            'ville' => $this->ville,
            'quartier' => $this->quartier,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'whatsapp_numero' => $this->whatsapp_numero,
            'slogan' => $this->slogan,
            'description' => $this->description,
            'logo_url' => $this->logo_url,
            'photos' => $this->photos,
            'site_web' => $this->site_web,
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'tiktok' => $this->tiktok,
            'abonnement_premium' => $this->abonnement_premium,
            'status' => $this->status,
            'motif_refus' => $this->motif_refus,
            'is_verified' => $this->is_verified,
            'valide_le' => $this->valide_le,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'proprietaire' => $this->whenLoaded('proprietaire', function () {
                return [
                    'id' => $this->proprietaire->id,
                    'nom' => $this->proprietaire->nom,
                    'prenom' => $this->proprietaire->prenom,
                    'email' => $this->proprietaire->email,
                    'telephone' => $this->proprietaire->telephone,
                    'ville' => $this->proprietaire->ville,
                ];
            }),
        ];
    }
}