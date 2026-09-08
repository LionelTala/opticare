<?php

namespace App\Http\Requests\Commande;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCommandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numero_monture' => 'nullable|string|max:255',
            'type_verre' => 'nullable|string|max:255',
            'teinte' => 'nullable|string|max:100',
            'description_foyers' => 'nullable|string',
            'port' => 'nullable|string|max:100',
            'antireflet' => 'nullable|boolean',
            'diag_od_sphere' => 'nullable|string',
            'diag_od_cylindre' => 'nullable|string',
            'diag_od_axe' => 'nullable|string',
            'diag_od_addition' => 'nullable|string',
            'diag_og_sphere' => 'nullable|string',
            'diag_og_cylindre' => 'nullable|string',
            'diag_og_axe' => 'nullable|string',
            'diag_og_addition' => 'nullable|string',
            'statut' => 'nullable|in:initie,en_cours,en_verification,termine',
            'notes' => 'nullable|string',
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