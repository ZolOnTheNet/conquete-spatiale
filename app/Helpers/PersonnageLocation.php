<?php

namespace App\Helpers;

use App\Models\Personnage;

/**
 * Helper pour déterminer la localisation et le contexte d'un personnage
 */
class PersonnageLocation
{
    /**
     * Types de localisation possibles
     */
    public const TYPE_VAISSEAU = 'vaisseau';
    public const TYPE_STATION = 'station';
    public const TYPE_NAVETTE = 'navette';
    public const TYPE_PLANETE = 'planete';
    public const TYPE_INCONNU = 'inconnu';

    /**
     * États possibles
     */
    public const ETAT_AMARRE = 'amarre';
    public const ETAT_EN_ORBITE = 'en_orbite';
    public const ETAT_EN_DEPLACEMENT = 'en_deplacement';
    public const ETAT_A_LA_SURFACE = 'a_la_surface';
    public const ETAT_INCONNU = 'inconnu';

    protected Personnage $personnage;
    protected ?string $type = null;
    protected ?string $etat = null;
    protected ?object $objetSpatial = null;
    protected ?array $position = null;

    public function __construct(Personnage $personnage)
    {
        $this->personnage = $personnage;
        $this->determinerLocalisation();
    }

    /**
     * Détermine la localisation actuelle du personnage
     */
    protected function determinerLocalisation(): void
    {
        // Récupérer la position via le vaisseau actif
        if ($this->personnage->vaisseauActif) {
            $this->type = self::TYPE_VAISSEAU;
            $this->objetSpatial = $this->personnage->vaisseauActif->objetSpatial;

            if ($this->objetSpatial) {
                $this->position = [
                    'secteur_x' => $this->objetSpatial->secteur_x,
                    'secteur_y' => $this->objetSpatial->secteur_y,
                    'secteur_z' => $this->objetSpatial->secteur_z,
                    'position_x' => $this->objetSpatial->position_x,
                    'position_y' => $this->objetSpatial->position_y,
                    'position_z' => $this->objetSpatial->position_z,
                ];

                // Déterminer l'état (à développer selon logique de déplacement)
                $this->etat = self::ETAT_EN_ORBITE; // Par défaut
            }
        }
        // Dans une station
        elseif ($this->personnage->dans_station_id) {
            $this->type = self::TYPE_STATION;
            $this->etat = self::ETAT_AMARRE;
            // Charger la station
            $this->objetSpatial = \App\Models\Station::find($this->personnage->dans_station_id);

            if ($this->objetSpatial && $this->objetSpatial->objetSpatial) {
                $this->position = [
                    'secteur_x' => $this->objetSpatial->objetSpatial->secteur_x,
                    'secteur_y' => $this->objetSpatial->objetSpatial->secteur_y,
                    'secteur_z' => $this->objetSpatial->objetSpatial->secteur_z,
                    'position_x' => $this->objetSpatial->objetSpatial->position_x,
                    'position_y' => $this->objetSpatial->objetSpatial->position_y,
                    'position_z' => $this->objetSpatial->objetSpatial->position_z,
                ];
            }
        }
        else {
            $this->type = self::TYPE_INCONNU;
            $this->etat = self::ETAT_INCONNU;
        }
    }

    /**
     * Retourne le type de localisation
     */
    public function getType(): string
    {
        return $this->type ?? self::TYPE_INCONNU;
    }

    /**
     * Retourne l'état actuel
     */
    public function getEtat(): string
    {
        return $this->etat ?? self::ETAT_INCONNU;
    }

    /**
     * Retourne l'objet spatial associé
     */
    public function getObjetSpatial(): ?object
    {
        return $this->objetSpatial;
    }

    /**
     * Retourne la position actuelle
     */
    public function getPosition(): ?array
    {
        return $this->position;
    }

    /**
     * Retourne une description textuelle de la localisation
     */
    public function getDescription(): string
    {
        $descriptions = [
            self::TYPE_VAISSEAU => [
                self::ETAT_EN_ORBITE => "À bord du vaisseau {nom} (en orbite)",
                self::ETAT_EN_DEPLACEMENT => "À bord du vaisseau {nom} (en déplacement)",
                self::ETAT_AMARRE => "À bord du vaisseau {nom} (amarré)",
            ],
            self::TYPE_STATION => [
                self::ETAT_AMARRE => "Dans la station {nom}",
            ],
            self::TYPE_NAVETTE => [
                self::ETAT_EN_DEPLACEMENT => "Dans la navette {nom} (en déplacement)",
            ],
        ];

        $template = $descriptions[$this->type][$this->etat] ?? "Localisation inconnue";
        $nom = $this->objetSpatial ? ($this->objetSpatial->nom ?? 'Inconnu') : 'Inconnu';

        return str_replace('{nom}', $nom, $template);
    }

    /**
     * Vérifie si le personnage est dans un vaisseau
     */
    public function estDansVaisseau(): bool
    {
        return $this->type === self::TYPE_VAISSEAU;
    }

    /**
     * Vérifie si le personnage est dans une station
     */
    public function estDansStation(): bool
    {
        return $this->type === self::TYPE_STATION;
    }

    /**
     * Vérifie si le personnage peut accéder au marché physique
     */
    public function peutAccederMarchePhysique(): bool
    {
        return $this->estDansStation();
    }

    /**
     * Vérifie si le personnage peut accéder aux données des marchés via COM
     */
    public function peutAccederDonneesMarche(): bool
    {
        // Dans un vaisseau avec système COM, ou dans une station
        return $this->estDansVaisseau() || $this->estDansStation();
    }

    /**
     * Vérifie si le personnage peut accéder aux descriptions d'armes/combat
     */
    public function peutAccederCombat(): bool
    {
        // Seulement dans une station/ville/planète
        return $this->estDansStation();
    }

    /**
     * Retourne les sections de menu disponibles selon la localisation
     */
    public function getMenuSections(): array
    {
        if ($this->estDansVaisseau()) {
            return [
                'timonerie' => [
                    'label' => 'Timonerie',
                    'icon' => '🎯',
                    'items' => [
                        ['label' => 'Position', 'route' => 'vaisseau.position'],
                        ['label' => 'Carte', 'route' => 'carte'],
                        ['label' => 'Scanner', 'route' => 'vaisseau.scanner'],
                    ],
                ],
                'ingenierie' => [
                    'label' => 'Ingénierie',
                    'icon' => '🔧',
                    'items' => [
                        ['label' => 'État du vaisseau', 'route' => 'vaisseau.etat'],
                        ['label' => 'Réparations', 'route' => 'vaisseau.reparations'],
                    ],
                ],
                'soute' => [
                    'label' => 'Soute',
                    'icon' => '📦',
                    'items' => [
                        ['label' => 'Inventaire', 'route' => 'inventaire'],
                        ['label' => 'Cargaison', 'route' => 'vaisseau.cargaison'],
                    ],
                ],
                'armement' => [
                    'label' => 'Armement',
                    'icon' => '⚔️',
                    'items' => [
                        ['label' => 'Armes embarquées', 'route' => 'vaisseau.armes'],
                    ],
                ],
                'com' => [
                    'label' => 'COM',
                    'icon' => '📡',
                    'items' => [
                        ['label' => 'Bases de données', 'route' => 'com.databases'],
                        ['label' => 'Prix des marchés', 'route' => 'com.prix'],
                        ['label' => 'Demandes stations', 'route' => 'com.demandes'],
                        ['label' => 'Messages', 'route' => 'com.messages'],
                    ],
                ],
            ];
        }

        if ($this->estDansStation()) {
            return [
                'station' => [
                    'label' => 'Station',
                    'icon' => '🏢',
                    'items' => [
                        ['label' => 'Tableau de bord', 'route' => 'dashboard'],
                        ['label' => 'Quitter station', 'route' => 'station.quitter'],
                    ],
                ],
                'commerce' => [
                    'label' => 'Commerce',
                    'icon' => '💰',
                    'items' => [
                        ['label' => 'Marché', 'route' => 'marche'],
                        ['label' => 'Missions', 'route' => 'missions'],
                    ],
                ],
                'combat' => [
                    'label' => 'Combat',
                    'icon' => '⚔️',
                    'items' => [
                        ['label' => 'Armes', 'route' => 'combat.armes'],
                        ['label' => 'Équipement', 'route' => 'combat.equipement'],
                    ],
                ],
                'navigation' => [
                    'label' => 'Navigation',
                    'icon' => '🎯',
                    'items' => [
                        ['label' => 'Carte', 'route' => 'carte'],
                        ['label' => 'Scanner', 'route' => 'scanner'],
                    ],
                ],
            ];
        }

        return [];
    }

    /**
     * Retourne les coordonnées formatées
     */
    public function getCoordonneesFormatees(): string
    {
        if (!$this->position) {
            return 'Position inconnue';
        }

        return sprintf(
            'Secteur (%d, %d, %d) | Position (%.2f, %.2f, %.2f) AL',
            $this->position['secteur_x'],
            $this->position['secteur_y'],
            $this->position['secteur_z'],
            $this->position['secteur_x'] * 10 + $this->position['position_x'],
            $this->position['secteur_y'] * 10 + $this->position['position_y'],
            $this->position['secteur_z'] * 10 + $this->position['position_z']
        );
    }
}
