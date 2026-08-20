<?php

namespace App\Http\Requests\Rdv;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PrendreRdvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cabinet_id' => 'required|exists:users,cabinet_id',
            'date' => 'required|date|after_or_equal:today',
            'heure_debut' => 'required|date_format:H:i',
            'motif' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',

            // Si patient non connecté (visiteur)
            'visiteur_nom' => 'nullable|string|max:255',
            'visiteur_prenom' => 'nullable|string|max:255',
            'visiteur_telephone' => 'nullable|string|max:20',
            'visiteur_email' => 'nullable|email|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'cabinet_id.required' => 'Le cabinet est obligatoire',
            'date.required' => 'La date est obligatoire',
            'date.after_or_equal' => 'La date doit être aujourd\'hui ou dans le futur',
            'heure_debut.required' => 'L\'heure de début est obligatoire',
            'heure_debut.date_format' => 'L\'heure doit être au format HH:MM',
            'visiteur_email.email' => 'L\'email du visiteur doit être valide',
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