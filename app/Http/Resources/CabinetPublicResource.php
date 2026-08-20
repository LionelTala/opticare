<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CabinetPublicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'adresse' => $this->adresse,
            'ville' => $this->ville,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'proprietaire' => $this->whenLoaded('proprietaire', function () {
                return [
                    'nom' => $this->proprietaire->nom,
                    'prenom' => $this->proprietaire->prenom,
                ];
            }),
        ];
    }
}