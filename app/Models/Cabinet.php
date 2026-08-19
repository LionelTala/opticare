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
        'telephone',
        'email',
        'is_verified',
        'proprietaire_id',
        'status',
        'motif_refus',
        'valide_le',
        'valide_par'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
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
}