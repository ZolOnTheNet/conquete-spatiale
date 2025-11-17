<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Compte extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'comptes';

    protected $fillable = [
        'nom_login',
        'mot_de_passe',
        'adresse_mail',
        'perso_principal',
        'perso_secondaires',
        'est_verifie',
        'date_logs',
    ];

    protected $hidden = [
        'mot_de_passe',
        'remember_token',
    ];

    protected $casts = [
        'perso_secondaires' => 'array',
        'est_verifie' => 'boolean',
        'date_logs' => 'array',
        'email_verified_at' => 'datetime',
    ];

    // Méthodes pour l'authentification Laravel
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    public function getEmailForVerification()
    {
        return $this->adresse_mail;
    }

    // Relations
    public function personnages(): HasMany
    {
        return $this->hasMany(Personnage::class, 'compte_id');
    }

    public function personnagePrincipal(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'perso_principal');
    }

    // Méthodes du GDD
    public function setMotDePasse(string $password): void
    {
        $this->mot_de_passe = bcrypt($password);
    }

    public function verifierMotDePasse(string $password): bool
    {
        return password_verify($password, $this->mot_de_passe);
    }
}
