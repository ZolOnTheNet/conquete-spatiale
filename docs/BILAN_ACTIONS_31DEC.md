# Actions de Refactorisation - Conquête Spatiale

**Date :** 31 décembre 2025
**Mise à jour :** 3 janvier 2026 - Bilan complet et nouveaux plans
**Pour :** Claude Code (Sonnet)

---

## Checklist Principale

```
★★★ PRIORITÉ 00 (URGENT - Systèmes de base) ★★★
[ ] P00-A : Système de Marché Dynamique → PLAN_SONNET_00_MARCHE_DYNAMIQUE.md
[ ] P00-B : Système de Dés Daggerheart → PLAN_SONNET_00B_SYSTEME_DES_DAGGERHEART.md

PRIORITÉ 0 (Critique - Qualité du code) - TERMINÉ
[X] P0-1 : Nettoyer les fichiers de test de la racine
[X] P0-2 : Créer le trait Detectable (réduction duplication)
[X] P0-2b : Appliquer le trait aux 5 modèles
[X] P0-3 : Créer le middleware InjectPersonnage
[X] P0-3b : Mettre à jour les contrôleurs
[ ] P0-4 : Centraliser les calculs de distance (À VÉRIFIER)
[X] P0-5 : Ajouter les indexes manquants

PRIORITÉ 1 (Haute - Backend Admin) - TERMINÉ
[X] P1-1 : Améliorer l'AdminController (découpage)
[X] P1-2 : Système de Mines (admin + vues)
[X] P1-3 : Système de Marché (MarcheService créé) → REMPLACÉ par P00-A
[X] P1-4 : Gestion des Stations (admin CRUD + vues)
[X] P1-5 : Gestion des Ressources (admin CRUD + vues)

PRIORITÉ 2 (Moyenne - Missions & Factions)
[ ] P2-1 : Système de Missions → PLAN_SONNET_02_MISSIONS.md
[ ] P2-2 : Gestion des Factions → PLAN_SONNET_03_FACTIONS_REPUTATION.md
[ ] P2-3 : Système de Réputation → PLAN_SONNET_03_FACTIONS_REPUTATION.md

PRIORITÉ 3 (Basse - Combat - À définir plus tard)
[ ] P3-1 : Créer le modèle Ennemi
[ ] P3-2 : Créer le modèle Allie
[ ] P3-3 : Système de Combat complet
```

---

## ÉTAT ACTUEL (3 janvier 2026)

### Fichiers créés et validés

| Fichier | Statut |
|---------|--------|
| `app/Traits/Detectable.php` | ✅ Créé |
| `app/Traits/HasInventaire.php` | ✅ Créé |
| `app/Http/Middleware/InjectPersonnage.php` | ✅ Créé et enregistré |
| `app/Http/Controllers/Admin/AdminDashboardController.php` | ✅ Créé |
| `app/Http/Controllers/Admin/AdminStationController.php` | ✅ Créé |
| `app/Http/Controllers/Admin/AdminMineController.php` | ✅ Créé |
| `app/Http/Controllers/Admin/AdminResourceController.php` | ✅ Créé |
| `app/Services/MarcheService.php` | ✅ Créé |
| `database/migrations/2026_01_01_185617_add_missing_indexes.php` | ✅ Créé |
| `database/scripts/` | ✅ 38 fichiers déplacés |

### Vues Admin créées

| Dossier | Fichiers |
|---------|----------|
| `resources/views/admin/mines/` | index, create, edit |
| `resources/views/admin/stations/` | index, create, edit |
| `resources/views/admin/ressources/` | index, create, edit |
| `resources/views/admin/gisements/` | index, create |
| `resources/views/layouts/admin.blade.php` | Layout admin |

### Modèles avec trait Detectable

- ✅ `app/Models/ObjetSpatial.php`
- ✅ `app/Models/Station.php`
- ✅ `app/Models/Planete.php`
- ✅ `app/Models/Mine.php`
- ✅ `app/Models/ZoneSpatiale.php`

---

## PLANS SONNET DISPONIBLES

### ★ Plan #00-A : Marché Dynamique (PRIORITAIRE)
**Fichier :** `docs/PLAN_SONNET_00_MARCHE_DYNAMIQUE.md`
**Objectif :** Système économique réaliste avec offre/demande
**Contenu :**
- Tables : `marche_stocks`, `marche_transactions`, `marche_config`
- Prix dynamiques basés sur le niveau de stock
- Cycle économique (production/consommation)
- Joueur : voit indicateurs (Rare/Disponible/Surplus), pas les chiffres
- MJ : rapport économique complet, régulation

### ★ Plan #00-B : Système de Dés Daggerheart (PRIORITAIRE)
**Fichier :** `docs/PLAN_SONNET_00B_SYSTEME_DES_DAGGERHEART.md`
**Objectif :** Système de résolution avec 2d12 Espoir/Peur
**10 Compétences Spatiales :**
1. **Pilotage (PIL)** - Manœuvrer un vaisseau
2. **Navigation (NAV)** - Tracer des routes, cartes stellaires
3. **Ingénierie (ING)** - Réparer, modifier, construire
4. **Systèmes (SYS)** - Scanners, boucliers, communications
5. **Artillerie (ART)** - Armes du vaisseau
6. **Survie (SUR)** - Environnements hostiles, EVA
7. **Commerce (COM)** - Négocier, estimer
8. **Perception (PER)** - Observer, détecter
9. **Influence (INF)** - Convaincre, intimider
10. **Piratage (PIR)** - Systèmes informatiques

**Mécanique :** Le joueur choisit sa compétence quand un jet est demandé

### Plan #01 : Validation
**Fichier :** `docs/PLAN_SONNET_01_VALIDATION.md`

### Plan #02 : Missions
**Fichier :** `docs/PLAN_SONNET_02_MISSIONS.md`

### Plan #03 : Factions et Réputation
**Fichier :** `docs/PLAN_SONNET_03_FACTIONS_REPUTATION.md`

---

## PROCHAINES ACTIONS (Ordre recommandé)

### 1. Marché Dynamique (PRIORITÉ ABSOLUE)
```bash
# Exécuter PLAN_SONNET_00_MARCHE_DYNAMIQUE.md
```

### 2. Système de Dés Daggerheart
```bash
# Exécuter PLAN_SONNET_00B_SYSTEME_DES_DAGGERHEART.md
```

### 3. Validation puis Missions/Factions
Après les systèmes de base.

---

## NOTES TECHNIQUES

### Constantes de conversion (rappel)
```php
CUA_PAR_UA = 100           // 1 UA = 100 cUA
UA_PAR_AL = 63241          // 1 AL = 63,241 UA
CUA_PAR_AL = 6324100       // 1 AL = 6,324,100 cUA
```

### Pattern personnage standardisé
```php
// Dans tous les contrôleurs, utiliser :
$personnage = $request->attributes->get('personnage');
```

### Validation des fichiers
Avant de commit, toujours vérifier :
```bash
php -l <fichier.php>
php artisan route:list
php artisan config:clear
```

---

*Document mis à jour le 3 janvier 2026*
*Pour exécution par Claude Code Sonnet*
