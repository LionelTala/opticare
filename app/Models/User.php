<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'ville',
        'password',
        'role',
        'cabinet_id',
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class);
    }

    public function patient()
    {
        return $this->hasOne(Patient::class);
    }

    public function isPatient(): bool
    {
        return $this->role === 'patient';
    }

    public function isProprietaire(): bool
    {
        return $this->role === 'proprietaire';
    }

    public function isOpticien(): bool
    {
        return $this->role === 'opticien';
    }

    public function isSecretaire(): bool
    {
        return $this->role === 'secretaire';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function hasCabinetAccess(): bool
    {
        return in_array($this->role, ['proprietaire', 'opticien', 'secretaire']);
    }
}