<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrdonnanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'consultation_id' => $this->consultation_id,
            'patient_id' => $this->patient_id,
            'cabinet_id' => $this->cabinet_id,
            'opticien_id' => $this->opticien_id,
            'prescription' => [
                'od' => [
                    'sphere' => $this->od_sphere,
                    'cylindre' => $this->od_cylindre,
                    'axe' => $this->od_axe,
                    'addition' => $this->od_addition,
                ],
                'og' => [
                    'sphere' => $this->og_sphere,
                    'cylindre' => $this->og_cylindre,
                    'axe' => $this->og_axe,
                    'addition' => $this->og_addition,
                ],
                'ecart_pupillaire' => $this->ecart_pupillaire,
            ],
            'recommandations' => $this->recommandations,
            'date_emission' => $this->date_emission,
            'date_validite' => $this->date_validite,
            'created_at' => $this->created_at,
            'patient' => $this->whenLoaded('patient', fn() => [
                'id' => $this->patient->id,
                'nom' => $this->patient->nom,
                'prenom' => $this->patient->prenom,
                'telephone' => $this->patient->telephone,
            ]),
            'opticien' => $this->whenLoaded('opticien', fn() => [
                'id' => $this->opticien->id,
                'nom' => $this->opticien->nom,
                'prenom' => $this->opticien->prenom,
            ]),
        ];
    }
}