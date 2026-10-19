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

    // Relations ajoutées
    public function cabinets()
    {
        return $this->belongsToMany(Cabinet::class, 'cabinet_patient')
            ->withPivot('est_interne', 'date_inscription')
            ->withTimestamps();
    }

    public function rdvs()
    {
        return $this->hasMany(RdvCreneau::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function avis()
    {
        return $this->hasMany(Avis::class);
    }

    // Vérifications
    public function hasAccount(): bool
    {
        return $this->user_id !== null;
    }

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