<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'commande_id',
        'patient_id',
        'cabinet_id',
        'emise_par',
        'numero',
        'type',
        'montant',
        'montant_paye',
        'statut',
        'date_emission',
        'date_echeance',
        'date_paiement',
        'description',
        'notes',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'date_emission' => 'date',
        'date_echeance' => 'date',
        'date_paiement' => 'datetime',
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class);
    }

    public function emetteur()
    {
        return $this->belongsTo(User::class, 'emise_par');
    }

    // Vérifications
    public function isPayee(): bool
    {
        return $this->statut === 'paye';
    }

    public function soldeRestant(): float
    {
        return max(0, $this->montant - $this->montant_paye);
    }
}