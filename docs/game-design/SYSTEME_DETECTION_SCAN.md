# ? SYST?ME DE D?TECTION ET SCAN

## Vue d'ensemble

Le système de scan spatial permet aux joueurs de détecter les objets et points d'intérêt (PoI) dans l'univers.

---

##  Objets prédéfinis au démarrage

### Système Solaire - Objets automatiquement découverts

Tous les joueurs commencent avec ces objets dans leur carte :

| Objet | Type | Station associée | Accessible directement |
|-------|------|------------------|----------------------|
| **Sol** | ?toile (G2V) | - | Oui |
| **Terre** | Planète tellurique | Terra-Maxi-Hub | ? Non (trop de circulation) |
| **Lune** | Satellite naturel | **Lunastar-station** ? | ? Non (trop de circulation) |
| **Mars** | Planète tellurique | Mars-spatiogare | ? Non (trop de circulation) |
| **Jupiter** | Géante gazeuse | Jupiter-spatiogare | Oui (via station) |
| **Neptune** | Géante de glace | Neptune-spatiogare | Oui (via station) |

**Point de départ :** Lunastar-station (orbite lunaire)

---

## œ Stations spatiales (Spatiogares)

### Format de nommage
- **Stations majeures** : Nom personnalisé (Terra-Maxi-Hub, Lunastar-station)
- **Stations standards** : `[Nom Planète]-spatiogare`

### Caractéristiques
- Modifiables depuis le backend admin
- Permettent l'accès aux planètes à forte circulation
- Servent de points de commerce et ravitaillement

---

## ? Système de détection

### Score de détectabilité

Chaque objet spatial a un **score de détectabilité de base** :

#### Formule pour les PoI (étoiles, galaxies)
```
detectabilite_base = (200 - Puissance_Etoile) / 3
```

**Plus le score est BAS, plus l'objet est FACILE à détecter.**

#### Exemples de calcul

| Type étoile | Puissance | Détectabilité | Commentaire |
|-------------|-----------|---------------|-------------|
| **Sol (G2V)** | 50 | 50 | Exception : puissance fixée à 50 |
| **O** | 150-200 | 0-17 | Très facile (énormes étoiles bleues) |
| **B** | 100-140 | 20-33 | Facile |
| **A** | 80-100 | 33-40 | Assez facile |
| **F** | 60-80 | 40-47 | Moyen |
| **G** | 40-60 | 47-53 | Moyen (comme Sol) |
| **K** | 30-40 | 53-57 | Difficile |
| **M** | 20-30 | 57-60 | Très difficile (naines rouges) |

### Modificateurs de distance

Le score final est modifié par la distance :

```
score_detection_final = detectabilite_base + modificateur_distance
```

#### Distance en Unités Astronomiques (UA)
```
modificateur_distance = distance_ua * 100
```

#### Distance en secteurs
```
modificateur_distance = distance_secteurs * facteur_secteur
```

**Où :**
- `facteur_secteur` = taille d'un secteur en UA (configurable)

---

##  Commande SCAN

### Portée de scan

1. **Objets locaux** : tous les objets dans le secteur actuel
2. **PoI distants** : étoiles et points d'intérêt des autres secteurs (dans la limite de portée du scanner)

### PoI connus

Les PoI découverts précédemment sont **automatiquement détectés** lors des scans suivants (même à grande distance).

### Mécanique de scan

Pour chaque objet/PoI non découvert :

1. Calculer `score_detection_final`
2. Lancer jet de détection (dés + capacités du vaisseau)
3. Si `resultat_jet >= score_detection_final` ? **Objet détecté !**
4. Sinon ? Objet reste caché

### Scan cumulatif

Le niveau de scan dans un secteur est **cumulatif** :
- Plusieurs scans dans le même secteur augmentent les chances de détection
- Se réinitialise si le vaisseau change de secteur

---

## œ Carte galactique

### Objets affichés

- ? Objets découverts (via scan ou prédéfinis)
- ? PoI connus automatiquement visibles
- ? Objets non découverts (brouillard de guerre)

### Commandes associées

- `scan` - Scanner le secteur actuel
- `carte` - Voir tous les systèmes découverts
- `position` - Voir position actuelle

---

## ? Implémentation technique

### Modèles concernés

- `SystemeStellaire` - étoiles avec puissance et détectabilité
- `Planete` - planètes avec score de détection
- `Station` - stations spatiales (à créer)
- `Decouverte` - objets découverts par personnage

### Seeders

- `GaiaSeeder` - Système Solaire complet avec stations
- `UniverseSeeder` - Génération procédurale

### Configuration

Fichier `config/game.php` :
```php
'detection' => [
    'sol_puissance' => 50,           // Exception pour Sol
    'ua_per_sector' => 10,            // Taille d'un secteur en UA
    'scan_portee_max' => 100,         // Portée max du scanner en secteurs
    'detectabilite_formule' => '(200 - puissance) / 3',
],
```

---

## ? Backend Admin

### Gestion des stations

Interface admin pour :
- Renommer les stations
- Modifier accessibilité planètes
- Ajuster scores de détection
- Créer/supprimer stations

Route : `/admin/stations`

---

##  Notes de développement

- [ ] Créer modèle `Station`
- [ ] Migration pour table `stations`
- [ ] Modifier `GaiaSeeder` pour créer système solaire complet
- [ ] Implémenter calcul de détection dans `SystemeStellaire`
- [ ] Modifier commande `scan` dans `GameController`
- [ ] Interface admin pour gérer stations
- [ ] Tests unitaires du système de détection

---

**Dernière mise à jour :** 2025-11-20
**Statut :** En développement
