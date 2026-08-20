<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RdvCreneau extends Model
{
    use HasFactory;
    protected $table = 'rdv_creneaux';

    protected $fillable = [
        'cabinet_id',
        'patient_id',
        'date',
        'heure_debut',
        'heure_fin',
        'motif',
        'statut',
        'source',
        'visiteur_nom',
        'visiteur_prenom',
        'visiteur_telephone',
        'visiteur_email',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function isDisponible(): bool
    {
        return $this->statut === 'libre';
    }

    public function isReserve(): bool
    {
        return $this->statut === 'reserve';
    }

    public function isConfirme(): bool
    {
        return $this->statut === 'confirme';
    }

    public function scopeDisponible($query)
    {
        return $query->where('statut', 'libre');
    }

    public function scopeReserve($query)
    {
        return $query->where('statut', 'reserve');
    }

    public function scopeConfirme($query)
    {
        return $query->where('statut', 'confirme');
    }
}