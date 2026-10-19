<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Champs stricts minimum comme demandé
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'telephone' => 'required|string|max:20|unique:patients,telephone',
            // Facultatifs mais utiles pour l'ERP
            'email' => 'nullable|email|max:255|unique:patients,email',
            'ville' => 'nullable|string|max:100',
            'date_naissance' => 'nullable|date|before:today',
            'adresse' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire',
            'prenom.required' => 'Le prénom est obligatoire',
            'telephone.required' => 'Le numéro de téléphone est obligatoire',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé',
            'email.unique' => 'Cet email est déjà utilisé',
            'date_naissance.before' => 'La date de naissance doit être dans le passé',
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