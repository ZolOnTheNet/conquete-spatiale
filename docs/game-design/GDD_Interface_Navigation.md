# 🎮 INTERFACE ET NAVIGATION
## Jeu de Conquête Galactique - Console Web

---

## 📋 Table des Matières

1. [Vue d'Ensemble](#vue-densemble)
2. [Structure du Menu Principal](#structure-du-menu-principal)
3. [Menu Personnage](#menu-personnage)
4. [Menu Navire/Station](#menu-navirestation)
5. [Menu Jeu](#menu-jeu)
6. [Menu Admin](#menu-admin)
7. [Comparaison avec Lunastar](#comparaison-avec-lunastar)
8. [Notes d'Implémentation](#notes-dimplémentation)

---

## 🎯 Vue d'Ensemble

### Inspiration

Le système de navigation s'inspire de **Lunastar** avec une structure de menu à trois niveaux :
- **Niveau 1** : Grandes catégories (non cliquables)
- **Niveau 2** : Sous-menus cliquables
- **Niveau 3** : Contenus et actions

### Principes

- **Navigation claire** : Menu toujours visible et structuré
- **Contextuel** : Le menu "Navire" devient "Station" selon le contexte
- **Cohérent** : Même structure que Lunastar mais avec notre terminologie
- **Extensible** : Ajout facile du menu Admin

---

## 🗂️ Structure du Menu Principal

```
┌─────────────────────────────────────────┐
│  CONQUÊTE GALACTIQUE                    │
├─────────────────────────────────────────┤
│                                         │
│  📊 PERSONNAGE (non cliquable)          │
│     └─ Dossier                         │
│     └─ Spatiocarte                     │
│     └─ Gestion                         │
│                                         │
│  🚀 NAVIRE/STATION (non cliquable)      │
│     └─ Timonerie                       │
│     └─ Ingénierie                      │
│     └─ COM                             │
│     └─ Soute                           │
│     └─ Équipage                        │
│                                         │
│  ⚙️ JEU (non cliquable)                 │
│     └─ Profil                          │
│     └─ Quitter                         │
│                                         │
│  🔧 ADMIN (non cliquable) [si admin]   │
│     └─ Dashboard                       │
│     └─ Carte Univers                   │
│     └─ Gestion Joueurs                 │
│     └─ Logs Système                    │
│     └─ Outils Debug                    │
│                                         │
└─────────────────────────────────────────┘
```

---

## 📊 Menu PERSONNAGE

Menu dédié à la gestion du personnage et de sa position dans l'univers.

### 1. Dossier
**Route :** `/personnage/dossier`

**Contenu :**
- Informations du personnage
  - Nom, faction, niveau
  - Crédits disponibles
  - Réputation
  - Compétences
- Historique d'actions récentes
- Statistiques de jeu
- Journal de bord

**Exemple d'affichage :**
```
┌─────────────────────────────────────┐
│ DOSSIER PERSONNEL                   │
├─────────────────────────────────────┤
│ Commandant : John Doe               │
│ Faction : Fédération Terrienne      │
│ Niveau : 15                         │
│ Crédits : 125,450 CR                │
│                                     │
│ Réputation :                        │
│  - Fédération : +75 (Respecté)      │
│  - Pirates : -30 (Hostile)          │
│                                     │
│ Compétences :                       │
│  - Navigation : ████████░░ 8/10     │
│  - Combat : ██████░░░░ 6/10         │
│  - Commerce : █████████░ 9/10       │
└─────────────────────────────────────┘
```

### 2. Spatiocarte
**Route :** `/personnage/spatiocarte`

**Contenu :**
- Carte de l'univers connu
- Position actuelle (secteur, système, coordonnées)
- Systèmes découverts
- Routes d'hyperespace connues
- Points d'intérêt (stations, planètes, etc.)
- Filtres et zoom

**Fonctionnalités :**
- 🔍 Zoom/Dézoom
- 🎯 Centrer sur position actuelle
- 📍 Définir destination
- 🗺️ Afficher/Masquer couches (routes, stations, etc.)
- 📊 Statistiques de découverte

### 3. Gestion
**Route :** `/personnage/gestion`

**Contenu :**
- Gestion des biens immobiliers
  - Habitations sur planètes
  - Hangars loués
  - Contrats de stockage
- Gestion des vaisseaux possédés
  - Liste des navires
  - Statut (actif, entreposé)
  - Localisation
- Gestion financière
  - Comptes bancaires
  - Investissements
  - Prêts en cours
- Contrats et missions en cours

---

## 🚀 Menu NAVIRE/STATION

Menu contextuel qui change selon la localisation du joueur.

### Contexte : À bord d'un Navire

Titre du menu : **🚀 NAVIRE**

#### 1. Timonerie
**Route :** `/navire/timonerie`

**Contenu :**
- **Navigation & Déplacement**
  - Position actuelle (x, y, z)
  - Destination sélectionnée
  - Distance restante
  - Temps de trajet estimé
  - Commandes de vol (avancer, tourner, arrêter)
  - Saut hyperespace

- **Radar & Scan** (intégré)
  - Objets détectés à proximité
    - Vaisseaux
    - Stations
    - Planètes
    - Astéroïdes
    - Anomalies
  - Distance et direction
  - Niveau de détail selon capacité radar
  - Scan détaillé (action)

**Interface :**
```
┌───────────────────────────────────────────┐
│ TIMONERIE                                 │
├───────────────────────────────────────────┤
│ Position : Secteur A-12                  │
│ Coords : X:1245.3 Y:987.6 Z:234.1        │
│ Destination : Station Alpha               │
│ Distance : 234.5 AL                       │
│ ETA : 2h 34min                           │
│                                          │
│ [▶ Avancer] [◼ Stop] [⚡ Hypersaut]      │
│                                          │
│ ═══════════════════════════════════════  │
│ RADAR - Portée : 50 AL                   │
│ ─────────────────────────────────────────│
│ ⚠️  Vaisseau inconnu - 12.3 AL (NE)     │
│ 🏭 Station Commerciale - 45.7 AL (S)     │
│ 🌍 Planète terraformée - 23.1 AL (O)     │
│                                          │
│ [🔍 Scanner Contact]                     │
└───────────────────────────────────────────┘
```

#### 2. Ingénierie
**Route :** `/navire/ingenierie`

**Contenu :**
- État des systèmes du vaisseau
  - Moteurs (propulsion, hyperpropulsion)
  - Générateurs d'énergie
  - Boucliers
  - Systèmes de survie
  - Armement
- Niveau d'intégrité de la coque
- Consommation énergétique
- Réparations nécessaires
- Améliorations disponibles
- Diagnostics et alertes

**Interface :**
```
┌───────────────────────────────────────────┐
│ INGÉNIERIE                                │
├───────────────────────────────────────────┤
│ État Général : ████████░░ 80%            │
│                                          │
│ Systèmes :                               │
│  ⚡ Générateur Principal   : ████████░░ 85% │
│  🚀 Moteurs Sublumiques   : ██████████ 100% │
│  ⭐ Hyperpropulseur       : ███████░░░ 70% │
│  🛡️ Boucliers             : █████░░░░░ 50% │
│  🎯 Armes                 : ████████░░ 80% │
│  💨 Vie Support           : ██████████ 100% │
│                                          │
│ Coque : ███████░░░ 75%                   │
│                                          │
│ ⚠️ Alerte : Hyperpropulseur nécessite    │
│           maintenance (72h max)          │
│                                          │
│ [🔧 Réparer] [⬆️ Améliorer]              │
└───────────────────────────────────────────┘
```

#### 3. COM
**Route :** `/navire/com`

**Contenu :**
- Communications radio/subspace
  - Messages reçus (inbox)
  - Envoyer message
  - Contacts
  - Fréquences publiques/privées
- Actualités galactiques
- Alertes et communiqués officiels
- Signaux de détresse
- Transactions commerciales
- Négociations avec contacts

**Catégories de messages :**
- 📨 Messages personnels
- 📢 Annonces publiques
- 🚨 Alertes de sécurité
- 💼 Offres commerciales
- ⚠️ SOS / Détresse
- 📰 Actualités

#### 4. Soute
**Route :** `/navire/soute`

**Contenu :**
- Inventaire de la cargaison
  - Marchandises
  - Ressources
  - Équipements
  - Objets spéciaux
- Capacité de stockage
  - Tonnage utilisé / total
  - Compartiments spéciaux (réfrigéré, blindé, etc.)
- Gestion du cargo
  - Trier, filtrer
  - Larguer cargo
  - Transférer vers station/autre vaisseau

**Interface :**
```
┌───────────────────────────────────────────┐
│ SOUTE                                     │
├───────────────────────────────────────────┤
│ Capacité : 450/800 tonnes (56%)          │
│                                          │
│ Cargo :                                  │
│                                          │
│ 📦 Minerai de fer         : 150t         │
│ 💎 Cristaux énergétiques  : 25t          │
│ 🍽️ Denrées alimentaires   : 80t          │
│ 🔧 Pièces détachées       : 45t          │
│ 💊 Médicaments            : 30t          │
│ ⚡ Cellules énergie       : 120t         │
│                                          │
│ Compartiments spéciaux :                 │
│ ❄️ Réfrigéré : 50/100t                   │
│ 🔒 Blindé : 0/50t                        │
│                                          │
│ [↔️ Transférer] [🗑️ Larguer]             │
└───────────────────────────────────────────┘
```

#### 5. Équipage
**Route :** `/navire/equipage`

**Contenu :**
- Liste des membres d'équipage
  - Nom, rôle, compétences
  - Statut (moral, santé, fatigue)
  - Salaire
- Postes disponibles / Recrutement
- Gestion des assignations
  - Affectation aux postes
  - Rotation des quarts
- Formation et amélioration
- Relations et événements d'équipage

**Interface :**
```
┌───────────────────────────────────────────┐
│ ÉQUIPAGE                                  │
├───────────────────────────────────────────┤
│ Membres : 8/12                           │
│                                          │
│ 👨‍✈️ Lt. Sarah Connor - Pilote            │
│    Navigation: ████████░░ 8/10           │
│    Moral: 😊 Excellent                   │
│                                          │
│ 👨‍🔧 John "Sparks" Miller - Ingénieur     │
│    Réparation: █████████░ 9/10           │
│    Moral: 😐 Correct                     │
│                                          │
│ 👨‍⚕️ Dr. Elena Vasquez - Médecin          │
│    Médecine: ██████░░░░ 6/10             │
│    Moral: 😊 Bon                         │
│                                          │
│ Postes vacants : 4                       │
│  - Artilleur (recommandé)                │
│  - Navigateur Junior                     │
│  - Mécanicien                            │
│  - Officier scientifique                 │
│                                          │
│ [👥 Recruter] [📋 Gérer Assignations]    │
└───────────────────────────────────────────┘
```

### Contexte : À bord d'une Station

Titre du menu : **🏭 STATION**

Le menu change pour refléter le contexte d'une station spatiale :

#### 1. Hall Principal
**Route :** `/station/hall`
- Informations sur la station
- Services disponibles
- Départ vers vaisseau

#### 2. Hangar
**Route :** `/station/hangar`
- Gestion des vaisseaux amarrés
- Services de réparation et maintenance
- Achat/Vente de vaisseaux
- Modification et améliorations

#### 3. Marché
**Route :** `/station/marche`
- Commerce de marchandises
- Offres et demandes
- Prix du marché
- Contrats commerciaux

#### 4. Bureau des Missions
**Route :** `/station/missions`
- Missions disponibles
- Missions en cours
- Réputation et récompenses

#### 5. Cantina
**Route :** `/station/cantina`
- Recrutement d'équipage
- Rumeurs et informations
- Rencontres avec PNJ

---

## ⚙️ Menu JEU

Menu de gestion méta-jeu (hors contexte du personnage).

### 1. Profil
**Route :** `/jeu/profil`

**Contenu :**
- Informations du compte joueur
  - Pseudo
  - Email (masqué)
  - Date d'inscription
  - Dernière connexion
- Personnages du compte
  - Liste des personnages créés
  - Personnage actif
  - Créer nouveau personnage
  - Supprimer personnage
- Paramètres du jeu
  - Notifications
  - Préférences d'affichage
  - Raccourcis clavier
  - Son/Musique
- Statistiques globales
  - Temps de jeu total
  - Achievements débloqués
  - Records personnels

### 2. Quitter
**Route :** `/logout`

**Contenu :**
- Confirmation de déconnexion
- Sauvegarde automatique
- Message de sortie

---

## 🔧 Menu ADMIN

Menu spécial visible uniquement pour les administrateurs.

### 1. Dashboard
**Route :** `/admin/dashboard`

**Contenu :**
- Vue d'ensemble du jeu
  - Joueurs connectés
  - Statistiques serveur
  - Activité récente
  - Alertes système
- Graphiques et métriques
  - Évolution joueurs
  - Activité économique
  - Performance technique

### 2. Carte Univers
**Route :** `/admin/carte`

**Contenu :**
- Carte complète de l'univers
  - Tous les secteurs
  - Tous les systèmes
  - Tous les objets
- Mode édition
  - Créer/Modifier objets spatiaux
  - Ajuster positions
  - Configurer propriétés
- Vue des joueurs
  - Position de tous les joueurs
  - Vaisseaux actifs
  - Activité en temps réel

### 3. Gestion Joueurs
**Route :** `/admin/joueurs`

**Contenu :**
- Liste des comptes
  - Rechercher joueur
  - Voir détails compte/personnages
  - Éditer informations
  - Bannir/Débannir
  - Modifier crédits/ressources
- Modération
  - Messages signalés
  - Actions suspectes
  - Logs d'actions joueurs

### 4. Logs Système
**Route :** `/admin/logs`

**Contenu :**
- Logs d'application
  - Erreurs PHP/Laravel
  - Requêtes SQL lentes
  - Exceptions
- Logs d'activité
  - Connexions
  - Transactions importantes
  - Actions critiques
- Filtres et recherche

### 5. Outils Debug
**Route :** `/admin/debug`

**Contenu :**
- Outils de développement
  - Génération de données test
  - Reset secteurs
  - Simulation événements
  - Tests de performance
- Cache management
  - Vider cache
  - Voir contenu cache
  - Statistiques cache
- Base de données
  - Backup/Restore
  - Migrations
  - Queries directes (avec précaution)

---

## 🔄 Comparaison avec Lunastar

### Tableau de Correspondance

| **Lunastar** | **Conquête Galactique** | **Notes** |
|--------------|-------------------------|-----------|
| **Menu Personnage** | | |
| Dossier | Dossier | ✅ Identique |
| Spatiocarte | Spatiocarte | ✅ Identique |
| Gestion | Gestion | ✅ Identique |
| **Menu Vaisseau** | **Menu Navire/Station** | 🔄 Contextuel |
| Timonerie | Timonerie | ✅ Navigation + Radar intégré |
| Radar | *(intégré dans Timonerie)* | 🔄 Fusionné pour simplifier |
| Systèmes | Ingénierie | 🔄 Renommé (plus évocateur) |
| Radio | COM | 🔄 Renommé (terme SF standard) |
| Soutes | Soute | ✅ Quasi-identique |
| Équipage | Équipage | ✅ Identique |
| **Menu Jeu** | | |
| Profil | Profil | ✅ Identique |
| Quitter | Quitter | ✅ Identique |
| **Ajouts** | | |
| *(absent)* | Menu Admin | ➕ Nouveau (avec sous-menus) |
| *(absent)* | Mode Station | ➕ Nouveau (menu contextuel) |

### Différences Principales

1. **Radar intégré dans Timonerie**
   - Lunastar : 2 menus séparés
   - Nous : 1 menu unifié (plus fluide)

2. **Renommages**
   - "Systèmes" → "Ingénierie" (plus immersif)
   - "Radio" → "COM" (communications, standard SF)
   - "Vaisseau" → "Navire" (pour éviter confusion avec objet)

3. **Menu Contextuel Station**
   - Le menu "Navire" devient "Station" avec contenus différents
   - Adapté au contexte du joueur

4. **Menu Admin Complet**
   - Absent de Lunastar (ou non montré)
   - Essentiel pour la gestion du jeu

---

## 💡 Notes d'Implémentation

### Structure HTML/CSS Recommandée

```html
<nav class="main-menu">
    <!-- Personnage -->
    <div class="menu-section">
        <h3 class="menu-title">📊 PERSONNAGE</h3>
        <ul class="menu-items">
            <li><a href="/personnage/dossier">Dossier</a></li>
            <li><a href="/personnage/spatiocarte">Spatiocarte</a></li>
            <li><a href="/personnage/gestion">Gestion</a></li>
        </ul>
    </div>

    <!-- Navire/Station (contextuel) -->
    <div class="menu-section">
        <h3 class="menu-title">
            @if($context === 'navire')
                🚀 NAVIRE
            @else
                🏭 STATION
            @endif
        </h3>
        <ul class="menu-items">
            @if($context === 'navire')
                <li><a href="/navire/timonerie">Timonerie</a></li>
                <li><a href="/navire/ingenierie">Ingénierie</a></li>
                <li><a href="/navire/com">COM</a></li>
                <li><a href="/navire/soute">Soute</a></li>
                <li><a href="/navire/equipage">Équipage</a></li>
            @else
                <li><a href="/station/hall">Hall Principal</a></li>
                <li><a href="/station/hangar">Hangar</a></li>
                <li><a href="/station/marche">Marché</a></li>
                <li><a href="/station/missions">Missions</a></li>
                <li><a href="/station/cantina">Cantina</a></li>
            @endif
        </ul>
    </div>

    <!-- Jeu -->
    <div class="menu-section">
        <h3 class="menu-title">⚙️ JEU</h3>
        <ul class="menu-items">
            <li><a href="/jeu/profil">Profil</a></li>
            <li><a href="/logout">Quitter</a></li>
        </ul>
    </div>

    <!-- Admin (si admin) -->
    @if(auth()->user()->is_admin)
    <div class="menu-section menu-admin">
        <h3 class="menu-title">🔧 ADMIN</h3>
        <ul class="menu-items">
            <li><a href="/admin/dashboard">Dashboard</a></li>
            <li><a href="/admin/carte">Carte Univers</a></li>
            <li><a href="/admin/joueurs">Gestion Joueurs</a></li>
            <li><a href="/admin/logs">Logs Système</a></li>
            <li><a href="/admin/debug">Outils Debug</a></li>
        </ul>
    </div>
    @endif
</nav>
```

### Gestion du Contexte Navire/Station

```php
// Dans un Middleware ou Service
class GameContextService
{
    public function getMenuContext(Personnage $personnage): string
    {
        // Si le personnage est dans un vaisseau
        if ($personnage->vaisseau_actif_id) {
            return 'navire';
        }

        // Si le personnage est dans une station
        if ($personnage->station_actuelle_id) {
            return 'station';
        }

        // Défaut
        return 'navire';
    }
}
```

### Routes Laravel

```php
// routes/web.php

// Groupe authentifié
Route::middleware(['auth'])->group(function () {

    // Menu Personnage
    Route::prefix('personnage')->group(function () {
        Route::get('/dossier', [PersonnageController::class, 'dossier'])->name('personnage.dossier');
        Route::get('/spatiocarte', [PersonnageController::class, 'spatiocarte'])->name('personnage.spatiocarte');
        Route::get('/gestion', [PersonnageController::class, 'gestion'])->name('personnage.gestion');
    });

    // Menu Navire
    Route::prefix('navire')->group(function () {
        Route::get('/timonerie', [NavireController::class, 'timonerie'])->name('navire.timonerie');
        Route::get('/ingenierie', [NavireController::class, 'ingenierie'])->name('navire.ingenierie');
        Route::get('/com', [NavireController::class, 'com'])->name('navire.com');
        Route::get('/soute', [NavireController::class, 'soute'])->name('navire.soute');
        Route::get('/equipage', [NavireController::class, 'equipage'])->name('navire.equipage');
    });

    // Menu Station
    Route::prefix('station')->group(function () {
        Route::get('/hall', [StationController::class, 'hall'])->name('station.hall');
        Route::get('/hangar', [StationController::class, 'hangar'])->name('station.hangar');
        Route::get('/marche', [StationController::class, 'marche'])->name('station.marche');
        Route::get('/missions', [StationController::class, 'missions'])->name('station.missions');
        Route::get('/cantina', [StationController::class, 'cantina'])->name('station.cantina');
    });

    // Menu Jeu
    Route::prefix('jeu')->group(function () {
        Route::get('/profil', [JeuController::class, 'profil'])->name('jeu.profil');
    });
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Menu Admin
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/carte', [AdminController::class, 'carte'])->name('admin.carte');
        Route::get('/joueurs', [AdminController::class, 'joueurs'])->name('admin.joueurs');
        Route::get('/logs', [AdminController::class, 'logs'])->name('admin.logs');
        Route::get('/debug', [AdminController::class, 'debug'])->name('admin.debug');
    });
});
```

### Style CSS Recommandé

```css
.main-menu {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    padding: 1rem;
    border-right: 2px solid #0f3460;
    min-height: 100vh;
    width: 250px;
}

.menu-section {
    margin-bottom: 2rem;
}

.menu-title {
    font-family: 'Orbitron', sans-serif;
    color: #4a9eff;
    font-size: 0.9rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #0f3460;
    cursor: default; /* Non cliquable */
}

.menu-items {
    list-style: none;
    padding: 0;
    margin: 0;
}

.menu-items li {
    margin: 0.25rem 0;
}

.menu-items a {
    display: block;
    padding: 0.5rem 1rem;
    color: #e0e0e0;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.2s;
    font-size: 0.9rem;
}

.menu-items a:hover {
    background: rgba(74, 158, 255, 0.1);
    color: #4a9eff;
    padding-left: 1.25rem;
}

.menu-items a.active {
    background: rgba(74, 158, 255, 0.2);
    color: #4a9eff;
    border-left: 3px solid #4a9eff;
}

.menu-admin {
    border-top: 2px solid #ff6b6b;
    padding-top: 1rem;
}

.menu-admin .menu-title {
    color: #ff6b6b;
}

.menu-admin .menu-items a:hover {
    background: rgba(255, 107, 107, 0.1);
    color: #ff6b6b;
}
```

---

## ✅ Checklist de Validation

Vous avez demandé de vérifier qu'on n'a rien oublié. Voici la checklist :

### Menu Personnage
- ✅ Dossier
- ✅ Spatiocarte
- ✅ Gestion

### Menu Navire (dans vaisseau)
- ✅ Timonerie (navigation + radar)
- ✅ Ingénierie (ex-Systèmes)
- ✅ COM (ex-Radio)
- ✅ Soute
- ✅ Équipage

### Menu Station (dans station)
- ✅ Hall Principal
- ✅ Hangar
- ✅ Marché
- ✅ Bureau des Missions
- ✅ Cantina

### Menu Jeu
- ✅ Profil
- ✅ Quitter

### Menu Admin
- ✅ Dashboard
- ✅ Carte Univers
- ✅ Gestion Joueurs
- ✅ Logs Système
- ✅ Outils Debug

### Fonctionnalités Transverses
- ✅ Menu contextuel (Navire/Station)
- ✅ Titres non cliquables
- ✅ Sous-menus cliquables
- ✅ Menu Admin visible uniquement si admin
- ✅ Routes définies
- ✅ Style CSS cohérent

---

## 🚀 Prochaines Étapes d'Implémentation

1. **Phase 1 : Structure de base**
   - Créer le layout principal avec menu
   - Implémenter les routes de base
   - Créer les contrôleurs vides

2. **Phase 2 : Menu Personnage**
   - Implémenter Dossier
   - Implémenter Spatiocarte (version basique)
   - Implémenter Gestion

3. **Phase 3 : Menu Navire**
   - Implémenter Timonerie (navigation)
   - Implémenter Timonerie (radar/scan)
   - Implémenter Ingénierie
   - Implémenter COM
   - Implémenter Soute
   - Implémenter Équipage

4. **Phase 4 : Menu Station**
   - Implémenter Hall
   - Implémenter Hangar
   - Implémenter Marché
   - Implémenter Missions
   - Implémenter Cantina

5. **Phase 5 : Menu Admin**
   - Implémenter Dashboard
   - Implémenter Carte Univers
   - Implémenter Gestion Joueurs
   - Implémenter Logs
   - Implémenter Debug Tools

---

## 📚 Références

- [GDD_Central.md](./GDD_Central.md) - Document central
- [GDD_Architecture_Technique.md](./GDD_Architecture_Technique.md) - Architecture technique
- [GDD_Vaisseaux_Complet.md](./GDD_Vaisseaux_Complet.md) - Système de vaisseaux
- [SYSTEME_STATIONS.md](./SYSTEME_STATIONS.md) - Système de stations
- Inspiration : **Lunastar** (jeu de référence)

---

**Dernière mise à jour :** 2025-01-25
