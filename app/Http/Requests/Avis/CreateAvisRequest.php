<?php

namespace App\Http\Requests\Avis;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateAvisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'consultation_id' => 'required|exists:consultations,id',
            'note' => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'consultation_id.required' => 'La consultation est obligatoire',
            'consultation_id.exists' => 'La consultation n\'existe pas',
            'note.required' => 'La note est obligatoire',
            'note.integer' => 'La note doit être un nombre',
            'note.min' => 'La note minimum est de 1',
            'note.max' => 'La note maximum est de 5',
            'commentaire.max' => 'Le commentaire ne doit pas dépasser 1000 caractères',
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