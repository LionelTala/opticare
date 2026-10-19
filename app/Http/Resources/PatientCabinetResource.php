<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientCabinetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'ville' => $this->ville,
            'date_naissance' => $this->date_naissance,
            'adresse' => $this->adresse,
            'user_id' => $this->user_id,
            'est_interne' => $this->user_id === null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}