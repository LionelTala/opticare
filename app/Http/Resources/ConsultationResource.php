<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'cabinet_id' => $this->cabinet_id,
            'opticien_id' => $this->opticien_id,
            'rdv_id' => $this->rdv_id,
            'date_consultation' => $this->date_consultation,
            'motif' => $this->motif,
            'comment_connu' => $this->comment_connu,
            'ancienne_prescription_date' => $this->ancienne_prescription_date,
            'ancienne_prescription_od' => $this->ancienne_prescription_od,
            'ancienne_prescription_og' => $this->ancienne_prescription_og,
            'plaintes' => [
                'vision_flou_loin' => (bool) $this->plainte_vision_flou_loin,
                'vision_flou_pres' => (bool) $this->plainte_vision_flou_pres,
                'vision_double' => (bool) $this->plainte_vision_double,
                'demangeaisons' => (bool) $this->plainte_demangeaisons,
                'larmoiement' => (bool) $this->plainte_larmoiement,
                'autres' => $this->plainte_autres,
            ],
            'ecart_pupillaire' => $this->ecart_pupillaire,
            'prescription' => [
                'od' => [
                    'sphere' => $this->od_sphere,
                    'cylindre' => $this->od_cylindre,
                    'axe' => $this->od_axe,
                    'addition' => $this->od_addition,
                    'acuite_loin' => $this->od_acuite_loin,
                    'acuite_pres' => $this->od_acuite_pres,
                ],
                'og' => [
                    'sphere' => $this->og_sphere,
                    'cylindre' => $this->og_cylindre,
                    'axe' => $this->og_axe,
                    'addition' => $this->og_addition,
                    'acuite_loin' => $this->og_acuite_loin,
                    'acuite_pres' => $this->og_acuite_pres,
                ],
            ],
            'observations' => $this->observations,
            'statut' => $this->statut,
            'verrouillee' => (bool) $this->verrouillee,
            'is_modifiable' => $this->isModifiable(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'patient' => $this->whenLoaded('patient', function () {
                return [
                    'id' => $this->patient->id,
                    'nom' => $this->patient->nom,
                    'prenom' => $this->patient->prenom,
                    'telephone' => $this->patient->telephone,
                    'email' => $this->patient->email,
                    'ville' => $this->patient->ville,
                ];
            }),
            'opticien' => $this->whenLoaded('opticien', function () {
                return [
                    'id' => $this->opticien->id,
                    'nom' => $this->opticien->nom,
                    'prenom' => $this->opticien->prenom,
                ];
            }),
            'rdv' => $this->whenLoaded('rdv', function () {
                return [
                    'id' => $this->rdv->id,
                    'date' => $this->rdv->date,
                    'heure_debut' => $this->rdv->heure_debut,
                ];
            }),
        ];
    }
}