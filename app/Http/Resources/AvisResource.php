<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'cabinet_id' => $this->cabinet_id,
            'consultation_id' => $this->consultation_id,
            'note' => $this->note,
            'commentaire' => $this->commentaire,
            'est_publie' => (bool) $this->est_publie,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'patient' => $this->whenLoaded('patient', function () {
                return [
                    'id' => $this->patient->id,
                    'nom' => $this->patient->nom,
                    'prenom' => $this->patient->prenom,
                ];
            }),
            'cabinet' => $this->whenLoaded('cabinet', function () {
                return [
                    'id' => $this->cabinet->id,
                    'nom' => $this->cabinet->nom,
                ];
            }),
        ];
    }
}