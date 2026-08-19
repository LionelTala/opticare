<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\Patient;
use App\Models\Cabinet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function registerPatient(array $data): array
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'] ?? null,
                'telephone' => $data['telephone'],
                'ville' => $data['ville'],
                'password' => Hash::make($data['password']),
                'role' => 'patient',
                'is_active' => true,
            ]);

            $patient = Patient::create([
                'user_id' => $user->id,
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'telephone' => $data['telephone'],
                'email' => $data['email'] ?? null,
                'ville' => $data['ville'],
                'date_naissance' => $data['date_naissance'] ?? null,
                'adresse' => $data['adresse'] ?? null,
                'notes' => null,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return [
                'user' => $user,
                'patient' => $patient,
                'token' => $token,
                'role' => $user->role,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur inscription patient : ' . $e->getMessage());
            throw $e;
        }
    }

    public function registerCabinet(array $data): array
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'] ?? null,
                'telephone' => $data['telephone'],
                'ville' => $data['ville'],
                'password' => Hash::make($data['password']),
                'role' => 'proprietaire',
                'is_active' => true,
            ]);

            $cabinet = Cabinet::create([
                'nom' => $data['cabinet_nom'],
                'adresse' => $data['cabinet_adresse'],
                'ville' => $data['cabinet_ville'],
                'telephone' => $data['cabinet_telephone'],
                'email' => $data['cabinet_email'],
                'status' => 'en_attente',
                'proprietaire_id' => $user->id,
            ]);

            $user->cabinet_id = $cabinet->id;
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return [
                'user' => $user,
                'cabinet' => $cabinet,
                'token' => $token,
                'role' => $user->role,
                'requires_verification' => true,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur inscription cabinet : ' . $e->getMessage());
            throw $e;
        }
    }

    public function login(array $credentials): array
    {
        try {
            $user = User::with('cabinet')->where('telephone', $credentials['telephone'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'telephone' => ['Les identifiants sont incorrects.'],
                ]);
            }

            if (!$user->is_active) {
                throw ValidationException::withMessages([
                    'telephone' => ['Votre compte a été désactivé.'],
                ]);
            }

            // Vérification du statut du cabinet pour les propriétaires
            if ($user->isProprietaire() && $user->cabinet) {
                if ($user->cabinet->status === 'en_attente') {
                    throw ValidationException::withMessages([
                        'telephone' => ['Votre cabinet est en attente de validation par un administrateur.'],
                    ]);
                }

                if ($user->cabinet->status === 'refuse') {
                    throw ValidationException::withMessages([
                        'telephone' => ['Votre demande a été refusée. Motif : ' . ($user->cabinet->motif_refus ?? 'Non spécifié')],
                    ]);
                }
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            if ($user->isPatient()) {
                $user->load('patient');
            } elseif ($user->hasCabinetAccess()) {
                $user->load('cabinet');
            }

            return [
                'user' => $user,
                'token' => $token,
                'role' => $user->role,
            ];
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erreur login : ' . $e->getMessage());
            throw $e;
        }
    }
}