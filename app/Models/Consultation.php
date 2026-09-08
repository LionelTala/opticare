<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'cabinet_id',
        'opticien_id',
        'rdv_id',
        'date_consultation',
        'motif',
        'comment_connu',
        'ancienne_prescription_date',
        'ancienne_prescription_od',
        'ancienne_prescription_og',
        'plainte_vision_flou_loin',
        'plainte_vision_flou_pres',
        'plainte_vision_double',
        'plainte_demangeaisons',
        'plainte_larmoiement',
        'plainte_autres',
        'ecart_pupillaire',
        'od_sphere',
        'od_cylindre',
        'od_axe',
        'od_addition',
        'od_acuite_loin',
        'od_acuite_pres',
        'og_sphere',
        'og_cylindre',
        'og_axe',
        'og_addition',
        'og_acuite_loin',
        'og_acuite_pres',
        'observations',
        'statut',
        'verrouillee',
    ];

    protected $casts = [
        'date_consultation' => 'datetime',
        'ancienne_prescription_date' => 'date',
        'plainte_vision_flou_loin' => 'boolean',
        'plainte_vision_flou_pres' => 'boolean',
        'plainte_vision_double' => 'boolean',
        'plainte_demangeaisons' => 'boolean',
        'plainte_larmoiement' => 'boolean',
        'verrouillee' => 'boolean',
    ];

    // Relations
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class);
    }

    public function opticien()
    {
        return $this->belongsTo(User::class, 'opticien_id');
    }

    public function rdv()
    {
        return $this->belongsTo(RdvCreneau::class, 'rdv_id');
    }

    // public function commandes()
    // {
    //     return $this->hasMany(Commande::class);
    // }

    // public function avis()
    // {
    //     return $this->hasOne(Avis::class);
    // }

    // Vérifications
    public function isModifiable(): bool
    {
        return !$this->verrouillee && $this->statut === 'en_cours';
    }

    public function isTerminee(): bool
    {
        return $this->statut === 'terminee';
    }

    // Scopes
    public function scopeEnCours($query)
    {
        return $query->where('statut', 'en_cours');
    }

    public function scopeTerminee($query)
    {
        return $query->where('statut', 'terminee');
    }

    public function scopeVerrouillee($query)
    {
        return $query->where('verrouillee', true);
    }

    public function scopeNonVerrouillee($query)
    {
        return $query->where('verrouillee', false);
    }
}