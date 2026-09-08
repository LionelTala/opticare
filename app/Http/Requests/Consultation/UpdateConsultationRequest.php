<?php

namespace App\Http\Requests\Consultation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motif' => 'sometimes|string|max:255',
            'comment_connu' => 'nullable|string|max:255',
            'ancienne_prescription_date' => 'nullable|date',
            'ancienne_prescription_od' => 'nullable|string',
            'ancienne_prescription_og' => 'nullable|string',
            'plainte_vision_flou_loin' => 'nullable|boolean',
            'plainte_vision_flou_pres' => 'nullable|boolean',
            'plainte_vision_double' => 'nullable|boolean',
            'plainte_demangeaisons' => 'nullable|boolean',
            'plainte_larmoiement' => 'nullable|boolean',
            'plainte_autres' => 'nullable|string',
            'ecart_pupillaire' => 'nullable|string',
            'od_sphere' => 'nullable|string',
            'od_cylindre' => 'nullable|string',
            'od_axe' => 'nullable|string',
            'od_addition' => 'nullable|string',
            'od_acuite_loin' => 'nullable|string',
            'od_acuite_pres' => 'nullable|string',
            'og_sphere' => 'nullable|string',
            'og_cylindre' => 'nullable|string',
            'og_axe' => 'nullable|string',
            'og_addition' => 'nullable|string',
            'og_acuite_loin' => 'nullable|string',
            'og_acuite_pres' => 'nullable|string',
            'observations' => 'nullable|string',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Erreur de validation',
            'errors' => $validator->errors()
        ], 422));
    }
}