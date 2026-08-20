<?php

namespace App\Http\Requests\Cabinet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreneauHoraireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jour_semaine' => 'required|in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i|after:heure_debut',
            'duree_rdv' => 'nullable|integer|min:15|max:120',
            'pause_debut' => 'nullable|date_format:H:i|after:heure_debut|before:heure_fin',
            'pause_fin' => 'nullable|date_format:H:i|after:pause_debut|before:heure_fin',
            'est_actif' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'jour_semaine.required' => 'Le jour de la semaine est obligatoire',
            'jour_semaine.in' => 'Le jour doit être lundi, mardi, mercredi, jeudi, vendredi, samedi ou dimanche',
            'heure_debut.required' => 'L\'heure de début est obligatoire',
            'heure_fin.required' => 'L\'heure de fin est obligatoire',
            'heure_fin.after' => 'L\'heure de fin doit être après l\'heure de début',
            'duree_rdv.integer' => 'La durée doit être un nombre',
            'duree_rdv.min' => 'La durée minimum est de 15 minutes',
            'duree_rdv.max' => 'La durée maximum est de 120 minutes',
            'pause_debut.after' => 'La pause doit commencer après l\'heure de début',
            'pause_debut.before' => 'La pause doit commencer avant l\'heure de fin',
            'pause_fin.after' => 'La pause doit finir après l\'heure de début de pause',
            'pause_fin.before' => 'La pause doit finir avant l\'heure de fin',
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