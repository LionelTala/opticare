<?php

namespace App\Http\Requests\Cabinet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'sometimes|string|max:255',
            'adresse' => 'sometimes|string|max:255',
            'ville' => 'sometimes|string|max:100',
            'quartier' => 'sometimes|string|max:100',
            'telephone' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|max:255',
            'whatsapp_numero' => 'nullable|string|max:20',
            'slogan' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'logo_url' => 'nullable|string|max:500',
            'photos' => 'nullable|array',
            'photos.*' => 'string|max:500',
            'site_web' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
            'abonnement_premium' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'L\'email doit être valide',
            'email.max' => 'L\'email ne doit pas dépasser 255 caractères',
            'whatsapp_numero.max' => 'Le numéro WhatsApp ne doit pas dépasser 20 caractères',
            'slogan.max' => 'Le slogan ne doit pas dépasser 255 caractères',
            'description.max' => 'La description ne doit pas dépasser 2000 caractères',
            'logo_url.max' => 'L\'URL du logo ne doit pas dépasser 500 caractères',
            'photos.array' => 'Les photos doivent être un tableau',
            'photos.*.max' => 'Chaque URL de photo ne doit pas dépasser 500 caractères',
            'site_web.max' => 'Le site web ne doit pas dépasser 255 caractères',
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