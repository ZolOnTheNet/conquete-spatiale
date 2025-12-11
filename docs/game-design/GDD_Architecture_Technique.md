# üíª ARCHITECTURE TECHNIQUE ET CLASSES
## Jeu de Conquête Galactique

---

## ⚠️📋 DISCLAIMER
Approche Orientée Objet - Peut nécessiter modifications lors de l'implémentation.

---

## 📋 Pattern de Développement

### MVC (Modèle-Vue-Contr¥leur)

```
UTILISATEUR
    œÜì (actions)
CONTRîLEUR (index.php)
    œîúœÜí MODLE (données, logique métier)
    œîîœÜí VUE (affichage, interface)
    œÜì
RENDU HTML
```

---

## üìÅ Structure Fichiers

```
/
œîúœîÄ index.php (point d'entrée, contr¥leur principal)
œîÇ
œîúœîÄ /controllers/
œîÇ   œîúœîÄ PersonnageController.php
œîÇ   œîúœîÄ VaisseauController.php
œîÇ   œîúœîÄ BaseController.php
œîÇ   œîîœîÄ ...
œîÇ
œîúœîÄ /models/
œîÇ   œîúœîÄ Compte.php
œîÇ   œîúœîÄ Personnage.php
œîÇ   œîúœîÄ ObjetSpatial.php
œîÇ   œîúœîÄ Vaisseau.php
œîÇ   œîúœîÄ Base.php
œîÇ   œîîœîÄ ...
œîÇ
œîúœîÄ /views/
œîÇ   œîúœîÄ layout.php (template principal)
œîÇ   œîúœîÄ personnage/
œîÇ   œîúœîÄ vaisseau/
œîÇ   œîúœîÄ base/
œîÇ   œîîœîÄ ...
œîÇ
œîúœîÄ /assets/
œîÇ   œîúœîÄ css/
œîÇ   œîúœîÄ js/
œîÇ   œîîœîÄ img/
œîÇ
œîîœîÄ /config/
    œîúœîÄ database.php
    œîîœîÄ config.php
```

---

## üë§ Classe Compte

### Description

**Représente :** Un joueur  
**Correspond  :** Table `CS_comptes` en base de données

---

### Attributs (Privés)

```php
class Compte {
    // Identification
    private int $idCompte;           // 0 si pas encore défini
    private string $NomLogin;        // ChaÆne connexion, BD compatible
    private string $MotDePasse;      // Crypté dans la BD
    private string $AdresseMail;     // Adresse email
    
    // Personnages
    private int $PersoPrincipal;     // ID personnage principal
    private array $PersoSecondaires; // IDs personnages secondaires
                                     // (actuellement non gérés)
    
    // êtat
    private bool $EstVerifie;        // TRUE si compte vérifié
    
    // Logs
    private array $dateLog;          // Différentes dates de gestion
}
```

---

### Méthodes (Publiques)

```php
class Compte {
    // Construction
    public function __construct(array $tableauHydra);
    public function Hydrater(array $tableauHydra): void;
    
    // Getters
    public function getIdCompte(): int;
    public function getNomLogin(): string;
    public function getAdresseMail(): string;
    public function getPersoPrincipal(): int;
    public function getPersoSecondaires(): array;
    public function isEstVerifie(): bool;
    
    // Setters
    public function setNomLogin(string $login): void;
    public function setMotDePasse(string $password): void;
    public function setAdresseMail(string $email): void;
    public function setPersoPrincipal(int $id): void;
    public function setEstVerifie(bool $verifie): void;
}
```

---

### Procédure de Création

**Sur page de création :**
1. Demande nom et login
2. Adresse mail
3. Mot de passe (crypté avant stockage)
4. Informations associées  classe Personnages

**Validation :**
- Email de vérification envoyé
- Lien d'activation
- `EstVerifie` passe  TRUE après validation

---

## üåå Classe ObjetSpatial (Parent)

### Description

**Classe parente** pour tous objets dans l'espace.

**Hérite :** Vaisseau, Base, AstéroØde, etc.

---

### Attributs (Privés)

```php
class ObjetSpatial {
    // Identification
    private int $IdOS;              // Identifiant unique
    private string $NomOS;          // Nom de l'objet
    private int $ClasseOS;          // Type d'objet (enum)
    
    // Position
    private int $positionX;         // Coordonnée X (entier relatif)
    private int $positionY;         // Coordonnée Y
    private int $positionZ;         // Coordonnée Z
    private float $distanceOS;      // Distance du centre secteur
    
    // Hiérarchie
    private int $contenuDans;       // 0 = libre, sinon ID conteneur
    private int $secteurOS;         // Secteur de localisation
    
    // Propriété
    private int $propriétaire;      // ID joueur (négatif = guilde)
    private ?ObjetSpatial $remorquerPar; // Vaisseau remorqueur
    
    // Physique
    private float $Volume;          // Taille
    private float $masse;           // Masse
    private int $resistance;        // En US (Unités Structure)
    private int $coefdommages;      // En %
    
    // Logs
    private array $dateLogs;        // Différentes dates
}
```

---

### Méthodes (Ä définir)

```php
class ObjetSpatial {
    // Construction
    public function __construct(array $data);
    
    // Position
    public function getPosition(): array;
    public function setPosition(int $x, int $y, int $z): void;
    public function deplacer(int $dx, int $dy, int $dz): void;
    
    // êtat
    public function getResistance(): int;
    public function subirDommages(int $dommages): void;
    public function reparer(int $montant): void;
    
    // Hiérarchie
    public function estContenu(): bool;
    public function estRemorque(): bool;
}
```

---

## üöÄ Classe Vaisseau (Hérite ObjetSpatial)

### Description

**Classe la plus importante au niveau jeu.**

**Hérite de :** ObjetSpatial

---

### Attributs Supplémentaires (Privés)

```php
class Vaisseau extends ObjetSpatial {
    // === PROPULSION ===
    private int $TypePropulsion;           // Type général
    private string $Mode;                  // 'combustible' | 'énergétique'
    private float $Réserve;               // Quantité UE stockable
    
    // Vitesses
    private float $VitesseConventionnelle; // Mode normal
    private float $VitesseSaut;           // Mode HE
    
    // Pannes
    private int $PartPanne;               // % moteur dans pannes
    
    // Combustible (si applicable)
    private float $Combustible;           // Réserve combustible
    private float $Efficacité;            // Transform. combustœÜíénergie/PA
    private string $TypeCombustible;      // Type minerai
    private float $Récupération;          // Points combust. dans 1 cargo
    
    // Coefficients
    private float $InitConventionnel;     // Coªt initial mode normal (0)
    private float $InitHyperespace;       // Coªt initial HE (200)
    private float $CoefConventionnel;     // Mult.ó100 dépense énergie normal
    private float $CoefHyperespace;       // Mult.ó100 dépense énergie HE
    private float $CoefPAMN;              // Mult.ó100 PA mode normal (100)
    private float $CoefPAHE;              // Mult.ó100 PA mode HE (20)
    
    // === SOUTE ===
    private int $MaxSoutes;               // Nombre cargos max
    private int $PlaceSoute;              // Places restantes
    private float $MasseVariable;         // Masse soutes (variable)
    private array $Soutes;                // Tableau objets cargo
    
    // === ARMEMENT ===
    private array $EmplacementArmes;      // Emplacements armes
    private int $nbArmes;                 // Nombre armes montées
    
    // === MAINTENANCE ===
    private int $Vétusté;                 // Augmente pannes
    private int $ComplexitéFct;           // Difficulté réparation
    private int $ScorePanne;              // Augmente  chaque panne
    private int $ScoreEntretien;          // Augmente  chaque entretien
    private array $PannesActuelles;       // Pannes  réparer
    
    // === INFORMATIQUE ===
    private int $SystemInformatique;      // Niveau système
    private array $Programmes;            // Programmes et niveaux
    
    // === LOGS ===
    private array $dateLogs;              // Dates de log
}
```

---

### Méthodes Spécifiques

```php
class Vaisseau extends ObjetSpatial {
    // === PROPULSION ===
    public function calculerConsommationConventionnelle(
        float $distance
    ): float;
    
    public function calculerConsommationHE(
        float $distance
    ): float;
    
    public function calculerNbPA(
        float $distance, 
        string $mode
    ): int;
    
    public function rechargerEnergie(float $quantité): void;
    public function consommerEnergie(float $quantité): bool;
    
    // === SOUTE ===
    public function ajouterCargo(Objet $cargo): bool;
    public function retirerCargo(int $index): ?Objet;
    public function getCapacitéDisponible(): int;
    public function larguerToutCargo(): void; // Saut urgence
    
    // === ARMEMENT ===
    public function monterArme(Arme $arme, int $emplacement): bool;
    public function démonterArme(int $emplacement): ?Arme;
    public function tirerArme(int $emplacement, Cible $cible): bool;
    
    // === MAINTENANCE ===
    public function calculerTauxPanne(): float;
    public function effectuerPanne(): ?Panne;
    public function reparer(Panne $panne): bool;
    public function entretien(): void; // Augmente ScoreEntretien
    
    // === INFORMATIQUE ===
    public function installerProgramme(Programme $prog): bool;
    public function désinstallerProgramme(int $id): bool;
    public function getProgrammes(): array;
    
    // === COMBAT ===
    public function calculerSeuilêvasion(): int;
    public function subirAttaque(int $dég¢ts): void;
    public function getHP(): int;
}
```

---

## üèóÔ∏è Classe Base (Hérite ObjetSpatial)

### Description

**Représente :** Base spatiale / Station

**Hérite de :** ObjetSpatial

---

### Attributs (Privés)

```php
class Base extends ObjetSpatial {
    // Gestion
    private int $IdGestionnaire;        // Joueur gestionnaire
    private bool $EstArche;             // TRUE si arche maÆtre
    
    // Structure
    private int $PointsAncrageMax;      // Max modules/arches
    private int $PointsAncrageLibres;   // Disponibles
    private array $ModulesAttachés;     // Liste modules
    private array $ArchesRattachées;    // Liste arches
    
    // Ressources
    private float $Productionênergie;   // UE/tour
    private float $Consommationênergie; // UE/tour
    private int $CapacitéStockage;      // Cargos stockables
    
    // Population
    private int $Population;            // Nombre habitants
    private float $Moral;               // 0-100%
    
    // êconomie
    private array $ProductionsActives;  // Usines/mines actives
    private array $MarchéLocal;         // Stocks marchandises
    
    // Défense
    private array $Défenses;            // Modules défense
    private int $NiveauDéfense;        // Score total
}
```

---

### Méthodes

```php
class Base extends ObjetSpatial {
    // Structure
    public function attacherModule(Module $module): bool;
    public function détacherModule(int $id): bool;
    public function rattacherArche(Base $arche): bool;
    
    // ênergie
    public function calculerBilanênergétique(): float;
    public function ajouterProduction(float $quantité): void;
    
    // Population
    public function ajouterHabitants(int $nombre): void;
    public function calculerMoral(): float;
    
    // êconomie
    public function produire(string $ressource): int;
    public function vendre(string $ressource, int $quantité): float;
    public function acheter(string $ressource, int $quantité): float;
    
    // Gestion
    public function changerGestionnaire(int $idJoueur): void;
    public function effectuerEntretien(): float; // Retourne coªt
}
```

---

## üì¶ Classes Auxiliaires

### Classe Cargo

```php
class Cargo {
    private int $id;
    private string $type;          // 'marchandise' | 'module' | 'personnel'
    private string $contenu;       // Type précis
    private int $quantité;         // Si marchandise
    private float $masse;          // Masse unitaire
    
    public function getMasse(): float;
    public function getValeur(): float;
}
```

---

### Classe Module

```php
class Module {
    private int $id;
    private string $type;          // 'antenne' | 'bar' | 'mine' | etc.
    private int $niveau;           // Niveau du module
    private float $consommationênergie;
    private array $production;     // Ce que produit le module
    
    public function fonctionner(): void;
    public function consommer(): array; // Ressources nécessaires
    public function produire(): array;  // Ressources produites
}
```

---

### Classe Programme

```php
class Programme {
    private int $id;
    private string $nom;           // 'pilotage' | 'visée' | etc.
    private int $niveau;           // 1-10
    private bool $obligatoire;     // TRUE si requis
    private float $bonus;          // Bonus apporté
    
    public function appliquerBonus(Vaisseau $v): void;
}
```

---

### Classe Panne

```php
class Panne {
    private int $id;
    private string $systèmeAffecté; // 'moteur' | 'bouclier' | etc.
    private int $gravité;           // 1-10
    private int $difficulté;        // Difficulté réparation
    private array $effets;          // Malus appliqués
    
    public function appliquerEffets(Vaisseau $v): void;
    public function coªtRéparation(): float;
}
```

---

## üóÑÔ∏è Tables Base de Données

### Table comptes

```sql
CREATE TABLE CS_comptes (
    idCompte INT PRIMARY KEY AUTO_INCREMENT,
    NomLogin VARCHAR(50) UNIQUE NOT NULL,
    MotDePasse VARCHAR(255) NOT NULL,
    AdresseMail VARCHAR(100) NOT NULL,
    PersoPrincipal INT,
    EstVerifie BOOLEAN DEFAULT FALSE,
    dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP,
    dateDerniereConnexion DATETIME,
    
    FOREIGN KEY (PersoPrincipal) REFERENCES personnages(id)
);
```

---

### Table objets_spatiaux

```sql
CREATE TABLE objets_spatiaux (
    IdOS INT PRIMARY KEY AUTO_INCREMENT,
    NomOS VARCHAR(100) NOT NULL,
    ClasseOS INT NOT NULL,
    
    positionX INT NOT NULL,
    positionY INT NOT NULL,
    positionZ INT NOT NULL,
    distanceOS FLOAT,
    
    contenuDans INT DEFAULT 0,
    secteurOS INT NOT NULL,
    
    proprietaire INT,
    remorquerPar INT NULL,
    
    Volume FLOAT NOT NULL,
    masse FLOAT NOT NULL,
    resistance INT NOT NULL,
    coefdommages INT DEFAULT 0,
    
    dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP,
    dateModification DATETIME,
    
    FOREIGN KEY (remorquerPar) REFERENCES objets_spatiaux(IdOS),
    INDEX idx_position (positionX, positionY, positionZ),
    INDEX idx_secteur (secteurOS)
);
```

---

### Table vaisseaux

```sql
CREATE TABLE vaisseaux (
    IdVaisseau INT PRIMARY KEY,
    
    -- Propulsion
    TypePropulsion INT NOT NULL,
    Mode ENUM('combustible', 'énergétique') NOT NULL,
    Réserve FLOAT NOT NULL,
    VitesseConventionnelle FLOAT NOT NULL,
    VitesseSaut FLOAT NOT NULL,
    PartPanne INT DEFAULT 10,
    
    Combustible FLOAT DEFAULT 0,
    Efficacité FLOAT DEFAULT 1.0,
    TypeCombustible VARCHAR(50),
    Récupération FLOAT DEFAULT 0,
    
    InitConventionnel FLOAT DEFAULT 0,
    InitHyperespace FLOAT DEFAULT 200,
    CoefConventionnel FLOAT DEFAULT 1.0,
    CoefHyperespace FLOAT DEFAULT 0.5,
    CoefPAMN FLOAT DEFAULT 1.0,
    CoefPAHE FLOAT DEFAULT 0.2,
    
    -- Soute
    MaxSoutes INT NOT NULL,
    PlaceSoute INT NOT NULL,
    MasseVariable FLOAT DEFAULT 0,
    
    -- Armement
    nbArmes INT DEFAULT 0,
    
    -- Maintenance
    Vétusté INT DEFAULT 0,
    ComplexitéFct INT DEFAULT 5,
    ScorePanne INT DEFAULT 0,
    ScoreEntretien INT DEFAULT 0,
    
    -- Informatique
    SystemInformatique INT NOT NULL,
    
    FOREIGN KEY (IdVaisseau) REFERENCES objets_spatiaux(IdOS)
);
```

---

### Table bases

```sql
CREATE TABLE bases (
    IdBase INT PRIMARY KEY,
    
    IdGestionnaire INT NOT NULL,
    EstArche BOOLEAN DEFAULT FALSE,
    
    PointsAncrageMax INT NOT NULL,
    PointsAncrageLibres INT NOT NULL,
    
    Productionênergie FLOAT DEFAULT 0,
    Consommationênergie FLOAT DEFAULT 0,
    CapacitéStockage INT DEFAULT 0,
    
    Population INT DEFAULT 0,
    Moral FLOAT DEFAULT 50.0,
    
    NiveauDéfense INT DEFAULT 0,
    
    FOREIGN KEY (IdBase) REFERENCES objets_spatiaux(IdOS),
    FOREIGN KEY (IdGestionnaire) REFERENCES CS_comptes(idCompte)
);
```

---

## üîÑ Workflow Exemple

### Création Vaisseau

```
1. Joueur achète vaisseau
2. Création entrée objets_spatiaux
3. Création entrée vaisseaux
4. Hydratation objet Vaisseau (PHP)
5. Association au joueur
6. Calcul caractéristiques initiales
7. Sauvegarde BDD
```

---

### Déplacement Vaisseau

```
1. Joueur saisit destination
2. Calcul distance
3. Calcul consommation (méthode Vaisseau)
4. Vérification réserve
5. Si OK : déplacement + consommation
6. Mise  jour position BDD
7. Génération secteur si nouveau
8. Détection objets secteur
```

---

## üí° Bonnes Pratiques

**POO :**
- Classes bien séparées (responsabilité unique)
- Héritage pour objets similaires
- Encapsulation (attributs privés)

**Base de Données :**
- Index sur colonnes recherchées (position, secteur)
- Clés étrangères pour intégrité
- Transactions pour opérations critiques

**Performance :**
- Cache pour objets fréquemment accédés
- Requêtes optimisées
- Lazy loading si possible

---

**Document vivant - Dernière mise  jour : 2025-11-01**
