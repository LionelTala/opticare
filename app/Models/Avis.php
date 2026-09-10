<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avis extends Model
{
    use HasFactory;

    protected $table = 'avis';

    protected $fillable = [
        'patient_id',
        'cabinet_id',
        'consultation_id',
        'note',
        'commentaire',
        'est_publie',
    ];

    protected $casts = [
        'note' => 'integer',
        'est_publie' => 'boolean',
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

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    // Scopes
    public function scopePublie($query)
    {
        return $query->where('est_publie', true);
    }

    public function scopeNonPublie($query)
    {
        return $query->where('est_publie', false);
    }

    public function scopeParCabinet($query, $cabinetId)
    {
        return $query->where('cabinet_id', $cabinetId);
    }
}