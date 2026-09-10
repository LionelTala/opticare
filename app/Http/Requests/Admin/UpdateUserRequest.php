<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserRequest extends FormRequest
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
            'role' => 'sometimes|in:super_admin,proprietaire,opticien,secretaire,patient',
            'cabinet_id' => 'nullable|exists:cabinet_optiques,id',
            'is_active' => 'sometimes|boolean',
            'statut_employe' => 'sometimes|in:actif,inactif',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Cet email est déjà utilisé',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé',
            'role.in' => 'Le rôle doit être super_admin, proprietaire, opticien, secretaire ou patient',
            'cabinet_id.exists' => 'Le cabinet n\'existe pas',
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