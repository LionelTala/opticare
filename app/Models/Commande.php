<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'cabinet_id',
        'opticien_id',
        'numero_monture',
        'type_verre',
        'teinte',
        'description_foyers',
        'port',
        'antireflet',
        'diag_od_sphere',
        'diag_od_cylindre',
        'diag_od_axe',
        'diag_od_addition',
        'diag_og_sphere',
        'diag_og_cylindre',
        'diag_og_axe',
        'diag_og_addition',
        'statut',
        'notes',
    ];

    protected $casts = [
        'antireflet' => 'boolean',
    ];

    // Relations
    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class);
    }

    public function opticien()
    {
        return $this->belongsTo(User::class, 'opticien_id');
    }

    // Vérifications
    public function isTerminee(): bool
    {
        return $this->statut === 'termine';
    }

    public function isEnCours(): bool
    {
        return $this->statut === 'en_cours';
    }

    // Scopes
    public function scopeInitie($query)
    {
        return $query->where('statut', 'initie');
    }

    public function scopeEnCours($query)
    {
        return $query->where('statut', 'en_cours');
    }

    public function scopeEnVerification($query)
    {
        return $query->where('statut', 'en_verification');
    }

    public function scopeTermine($query)
    {
        return $query->where('statut', 'termine');
    }
}