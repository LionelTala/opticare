<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonnelResource extends JsonResource
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
            'poste' => $this->poste,
            'statut_employe' => $this->statut_employe,
            'is_active' => (bool) $this->is_active,
            'est_actif' => $this->isEmployeActif(),
            'embauche_le' => $this->embauche_le,
            'depart_le' => $this->depart_le,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}