<?php

namespace App\Http\Requests\Ordonnance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateOrdonnanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'od_sphere' => 'nullable|string',
            'od_cylindre' => 'nullable|string',
            'od_axe' => 'nullable|string',
            'od_addition' => 'nullable|string',
            'og_sphere' => 'nullable|string',
            'og_cylindre' => 'nullable|string',
            'og_axe' => 'nullable|string',
            'og_addition' => 'nullable|string',
            'ecart_pupillaire' => 'nullable|string',
            'recommandations' => 'nullable|string|max:1000',
            'date_emission' => 'nullable|date',
            'date_validite' => 'nullable|date|after:date_emission',
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