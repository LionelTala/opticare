<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nom',
        'prenom',
        'telephone',
        'email',
        'ville',
        'date_naissance',
        'adresse',
        'notes'
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Vérifier si le patient a un compte (patient connecté)
    public function hasAccount(): bool
    {
        return $this->user_id !== null;
    }

    // Vérifier si c'est un patient interne (ERP)
    public function isInterne(): bool
    {
        return $this->user_id === null;
    }

    // Scopes
    public function scopeInterne($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeConnecte($query)
    {
        return $query->whereNotNull('user_id');
    }
}