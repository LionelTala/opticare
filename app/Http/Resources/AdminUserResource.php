<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
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
            'is_active' => (bool) $this->is_active,
            'statut_employe' => $this->statut_employe,
            'poste' => $this->poste,
            'embauche_le' => $this->embauche_le,
            'depart_le' => $this->depart_le,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'cabinet' => $this->whenLoaded('cabinet', function () {
                return [
                    'id' => $this->cabinet->id,
                    'nom' => $this->cabinet->nom,
                    'ville' => $this->cabinet->ville,
                ];
            }),
        ];
    }
}