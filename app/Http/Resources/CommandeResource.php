<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommandeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'consultation_id' => $this->consultation_id,
            'cabinet_id' => $this->cabinet_id,
            'opticien_id' => $this->opticien_id,
            'numero_monture' => $this->numero_monture,
            'type_verre' => $this->type_verre,
            'teinte' => $this->teinte,
            'description_foyers' => $this->description_foyers,
            'port' => $this->port,
            'antireflet' => (bool) $this->antireflet,
            'diagnostic' => [
                'od' => [
                    'sphere' => $this->diag_od_sphere,
                    'cylindre' => $this->diag_od_cylindre,
                    'axe' => $this->diag_od_axe,
                    'addition' => $this->diag_od_addition,
                ],
                'og' => [
                    'sphere' => $this->diag_og_sphere,
                    'cylindre' => $this->diag_og_cylindre,
                    'axe' => $this->diag_og_axe,
                    'addition' => $this->diag_og_addition,
                ],
            ],
            'statut' => $this->statut,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'consultation' => $this->whenLoaded('consultation', function () {
                return [
                    'id' => $this->consultation->id,
                    'date_consultation' => $this->consultation->date_consultation,
                    'motif' => $this->consultation->motif,
                ];
            }),
            'opticien' => $this->whenLoaded('opticien', function () {
                return [
                    'id' => $this->opticien->id,
                    'nom' => $this->opticien->nom,
                    'prenom' => $this->opticien->prenom,
                ];
            }),
        ];
    }
}