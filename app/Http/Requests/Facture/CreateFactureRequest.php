<?php

namespace App\Http\Requests\Facture;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateFactureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:acompte,solde,unique',
            'montant' => 'required|numeric|min:0',
            'montant_paye' => 'nullable|numeric|min:0',
            'statut' => 'nullable|in:impaye,partiel,paye,annule',
            'date_emission' => 'nullable|date',
            'date_echeance' => 'nullable|date|after:date_emission',
            'date_paiement' => 'nullable|date',
            'description' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
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