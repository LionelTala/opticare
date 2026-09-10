<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cabinet extends Model
{
    use HasFactory;

    protected $table = 'cabinet_optiques';

    protected $fillable = [
        'nom',
        'adresse',
        'ville',
        'quartier',
        'telephone',
        'email',
        'whatsapp_numero',
        'slogan',
        'description',
        'logo_url',
        'photos',
        'site_web',
        'facebook',
        'instagram',
        'tiktok',
        'abonnement_premium',
        'is_verified',
        'status',
        'motif_refus',
        'proprietaire_id',
        'valide_le',
        'valide_par'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'abonnement_premium' => 'boolean',
        'photos' => 'array',
        'valide_le' => 'datetime',
    ];

    public function proprietaire()
    {
        return $this->belongsTo(User::class, 'proprietaire_id');
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function employes()
    {
        return $this->hasMany(User::class, 'cabinet_id');
    }

    public function avis()
    {
        return $this->hasMany(Avis::class);
    }
    public function getNoteMoyenneAttribute()
    {
        return round($this->avis()->where('est_publie', true)->avg('note') ?? 0, 1);
    }

    // Nombre d'avis publiés
    public function getNbAvisAttribute()
    {
        return $this->avis()->where('est_publie', true)->count();
    }

  

    // Scopes
    public function scopeEnAttente($query)
    {
        return $query->where('status', 'en_attente');
    }

    public function scopeValide($query)
    {
        return $query->where('status', 'valide');
    }

    public function scopeRefuse($query)
    {
        return $query->where('status', 'refuse');
    }

    public function scopePremium($query)
    {
        return $query->where('abonnement_premium', true);
    }

    public function scopeRecherche($query, $search)
    {
        return $query->where('nom', 'like', "%{$search}%")
            ->orWhere('ville', 'like', "%{$search}%")
            ->orWhere('quartier', 'like', "%{$search}%");
    }
}