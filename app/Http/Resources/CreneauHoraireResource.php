<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreneauHoraireResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cabinet_id' => $this->cabinet_id,
            'jour_semaine' => $this->jour_semaine,
            'heure_debut' => $this->heure_debut,
            'heure_fin' => $this->heure_fin,
            'duree_rdv' => $this->duree_rdv,
            'pause_debut' => $this->pause_debut,
            'pause_fin' => $this->pause_fin,
            'est_actif' => $this->est_actif,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}