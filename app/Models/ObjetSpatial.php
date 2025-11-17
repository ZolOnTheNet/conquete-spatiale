<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ObjetSpatial extends Model
{
    protected $table = 'objets_spatiaux';

    protected $fillable = [
        'nom',
        'type',
        'secteur_x',
        'secteur_y',
        'secteur_z',
        'position_x',
        'position_y',
        'position_z',
        'contenu_dans',
        'secteur_id',
        'proprietaire_id',
        'remorque_par',
        'volume',
        'masse',
        'resistance',
        'coef_dommages',
        'date_logs',
    ];

    protected $casts = [
        'date_logs' => 'array',
    ];

    // Relations
    public function proprietaire(): BelongsTo
    {
        return $this->belongsTo(Personnage::class, 'proprietaire_id');
    }

    public function vaisseau(): HasOne
    {
        return $this->hasOne(Vaisseau::class, 'objet_spatial_id');
    }

    public function base(): HasOne
    {
        return $this->hasOne(Base::class, 'objet_spatial_id');
    }

    // Méthodes spatiales (selon GDD)
    public function getPosition(): array
    {
        return [
            'secteur' => [
                'x' => $this->secteur_x,
                'y' => $this->secteur_y,
                'z' => $this->secteur_z,
            ],
            'position' => [
                'x' => $this->position_x,
                'y' => $this->position_y,
                'z' => $this->position_z,
            ],
        ];
    }

    public function setPosition(int $secteurX, int $secteurY, int $secteurZ, float $posX, float $posY, float $posZ): void
    {
        $this->secteur_x = $secteurX;
        $this->secteur_y = $secteurY;
        $this->secteur_z = $secteurZ;
        $this->position_x = $posX;
        $this->position_y = $posY;
        $this->position_z = $posZ;
    }

    public function calculerDistance(ObjetSpatial $autre): float
    {
        $dx = $this->position_x - $autre->position_x;
        $dy = $this->position_y - $autre->position_y;
        $dz = $this->position_z - $autre->position_z;

        return sqrt($dx * $dx + $dy * $dy + $dz * $dz);
    }

    public function memeSecteur(ObjetSpatial $autre): bool
    {
        return $this->secteur_x === $autre->secteur_x
            && $this->secteur_y === $autre->secteur_y
            && $this->secteur_z === $autre->secteur_z;
    }

    public function subirDommages(int $dommages): void
    {
        $this->resistance = max(0, $this->resistance - $dommages);
    }

    public function reparer(int $montant): void
    {
        $this->resistance = min(100, $this->resistance + $montant);
    }
}
