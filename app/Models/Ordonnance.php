<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ordonnance extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'patient_id',
        'cabinet_id',
        'opticien_id',
        'od_sphere', 'od_cylindre', 'od_axe', 'od_addition',
        'og_sphere', 'og_cylindre', 'og_axe', 'og_addition',
        'ecart_pupillaire',
        'recommandations',
        'numero',
        'date_emission',
        'date_validite',
    ];

    protected $casts = [
        'date_emission' => 'date',
        'date_validite' => 'date',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

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
}