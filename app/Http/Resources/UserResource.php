<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'ville' => $this->ville,
            'role' => $this->role,
            'cabinet_id' => $this->cabinet_id,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'patient' => $this->whenLoaded('patient', function () {
                if (!$this->patient) {
                    return null;
                }
                return [
                    'id' => $this->patient->id,
                    'date_naissance' => $this->patient->date_naissance,
                    'adresse' => $this->patient->adresse,
                ];
            }),
        ];
    }
}