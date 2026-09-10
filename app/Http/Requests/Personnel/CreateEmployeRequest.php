<?php

namespace App\Http\Requests\Personnel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateEmployeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email',
            'telephone' => 'required|string|max:20|unique:users,telephone',
            'ville' => 'required|string|max:100',
            'role' => 'required|in:opticien,secretaire',
            'poste' => 'nullable|string|max:255',
            'embauche_le' => 'nullable|date',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire',
            'prenom.required' => 'Le prénom est obligatoire',
            'telephone.required' => 'Le numéro de téléphone est obligatoire',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé',
            'ville.required' => 'La ville est obligatoire',
            'role.required' => 'Le rôle est obligatoire',
            'role.in' => 'Le rôle doit être opticien ou secretaire',
            'password.required' => 'Le mot de passe est obligatoire',
            'password.min' => 'Le mot de passe doit faire au moins 8 caractères',
            'password.confirmed' => 'Les mots de passe ne correspondent pas',
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