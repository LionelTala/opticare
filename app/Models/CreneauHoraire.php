<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreneauHoraire extends Model
{
    use HasFactory;

    // Spécifier le nom exact de la table
    protected $table = 'creneaux_horaires';

    protected $fillable = [
        'cabinet_id',
        'jour_semaine',
        'heure_debut',
        'heure_fin',
        'duree_rdv',
        'pause_debut',
        'pause_fin',
        'est_actif',
    ];

    protected $casts = [
        'est_actif' => 'boolean',
    ];

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class);
    }

    public function scopeActif($query)
    {
        return $query->where('est_actif', true);
    }

    public function scopeParJour($query, $jour)
    {
        return $query->where('jour_semaine', $jour);
    }
}