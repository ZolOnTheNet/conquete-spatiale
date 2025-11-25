# 🚀 Référence Rapide - Commandes Admin

---

## 📋 Toutes les Commandes

| Commande | Description | Exemple |
|----------|-------------|---------|
| `/adm scan` | Scanner avec infos avancées | `/adm scan` |
| `/adm mv` | Déplacer objets | `/adm mv perso 1 station 2` |
| `/adm tp` | Téléporter PJ actuel | `/adm tp 50 30 10` |
| `/adm give` | Donner ressources | `/adm give pa 20` |
| `/adm info` | Info détaillée objet | `/adm info perso 1` |
| `/adm list` | Lister tous les objets | `/adm list persos` |
| `/adm print` | Debug objet Laravel | `/adm print PJ` |
| `/adm su` | Changer de compte | `/adm su 2` |

---

## 🔥 Commandes Essentielles

### Téléportation
```bash
/adm tp 100 50 25          # Téléporter vers secteur (100,50,25)
```

### Debug Personnage Actuel
```bash
/adm print PJ              # Voir tous les attributs + relations
/adm print SHIP            # Voir le vaisseau actuel
/adm print COMPTE          # Voir le compte actuel
```

### Donner des PA
```bash
/adm give pa 36            # Remettre PA au max
```

### Lister Tout
```bash
/adm list persos           # Tous les personnages
/adm list vaisseaux        # Tous les vaisseaux
/adm list comptes alice    # Chercher un compte
```

### Changer de Compte
```bash
/adm list comptes test     # Chercher le compte
/adm su 3                  # Se substituer au compte #3
/adm su back               # Revenir au compte admin
```

---

## 🎯 Alias Rapides `/adm print`

| Alias | Cible | Exemple |
|-------|-------|---------|
| `PJ`, `PERSO`, `PERSONNAGE` | Personnage actuel | `/adm print PJ` |
| `SHIP`, `VAISSEAU` | Vaisseau actuel | `/adm print SHIP` |
| `COMPTE` | Compte actuel | `/adm print COMPTE` |
| `OBJECTS` | Liste objets dispo | `/adm print OBJECTS` |

---

## 🔍 Modèles Interrogeables

```bash
/adm print Personnage 1
/adm print Vaisseau 2
/adm print Station 1
/adm print SystemeStellaire 5
/adm print Planete 10
/adm print ObjetSpatial 3
/adm print Ressource 1
/adm print Arme 2
/adm print Bouclier 1
/adm print Combat 1
/adm print Ennemi 5
/adm print Gisement 10
/adm print Marche 1
/adm print Recette 3
```

---

## 💡 Workflows Courants

### Déboguer un bug
```bash
/adm print PJ              # État personnage
/adm print SHIP            # État vaisseau
/adm info perso 1          # Infos résumées
```

### Téléporter pour tester
```bash
/adm tp 0 0 0              # Retour au système Sol
/adm scan                  # Voir ce qu'il y a autour
/adm tp 100 100 100        # Zone test lointaine
```

### Réinitialiser PA
```bash
/adm give pa 36            # Full PA
```

### Placer personnage
```bash
/adm list persos           # Voir les IDs
/adm list vaisseaux        # Voir les IDs vaisseaux
/adm mv perso 2 vaisseau 5 # Placer perso 2 dans vaisseau 5
```

### Tester un autre compte
```bash
/adm list comptes alice    # Trouver le compte
/adm su 2                  # Se substituer
/adm print PJ              # Vérifier état
position                   # Tester le jeu
/adm su back               # Revenir
```

---

## 📖 Documentation Complète

Voir : `docs/COMMANDES_ADMIN.md`

---

**Version** : 1.2
**Dernière mise à jour** : 2025-11-23
