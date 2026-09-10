<?php

namespace App\Http\Requests\Personnel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEmployeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'sometimes|string|max:255',
            'prenom' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $this->route('id'),
            'telephone' => 'sometimes|string|max:20|unique:users,telephone,' . $this->route('id'),
            'ville' => 'sometimes|string|max:100',
            'poste' => 'nullable|string|max:255',
            'statut_employe' => 'sometimes|in:actif,inactif',
            'is_active' => 'sometimes|boolean',
            'depart_le' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'statut_employe.in' => 'Le statut doit être actif ou inactif',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé',
            'email.unique' => 'Cet email est déjà utilisé',
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