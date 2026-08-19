<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterCabinetRequest extends FormRequest
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
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',

            'cabinet_nom' => 'required|string|max:255',
            'cabinet_adresse' => 'required|string|max:255',
            'cabinet_ville' => 'required|string|max:100',
            'cabinet_telephone' => 'required|string|max:20',
            'cabinet_email' => 'required|email|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom du propriétaire est obligatoire',
            'prenom.required' => 'Le prénom du propriétaire est obligatoire',
            'email.unique' => 'Cet email est déjà utilisé',
            'telephone.required' => 'Le numéro de téléphone est obligatoire',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé',
            'ville.required' => 'La ville est obligatoire',
            'password.required' => 'Le mot de passe est obligatoire',
            'password.min' => 'Le mot de passe doit faire au moins 8 caractères',
            'password.confirmed' => 'Les mots de passe ne correspondent pas',

            'cabinet_nom.required' => 'Le nom du cabinet est obligatoire',
            'cabinet_adresse.required' => 'L\'adresse du cabinet est obligatoire',
            'cabinet_ville.required' => 'La ville du cabinet est obligatoire',
            'cabinet_telephone.required' => 'Le téléphone du cabinet est obligatoire',
            'cabinet_email.required' => 'L\'email du cabinet est obligatoire',
            'cabinet_email.email' => 'L\'email du cabinet doit être valide',
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