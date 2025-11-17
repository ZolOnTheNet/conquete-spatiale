# Conquête Galactique - Context de Développement

## Vue d'ensemble
Jeu d'exploration spatiale utilisant le système Daggerheart (2d12, jetons Hope/Fear). Un joueur contrôle un personnage pilotant un vaisseau dans un univers procédural basé sur des données astronomiques réelles (GAIA, NASA).

## Architecture Technique
- **Langage** : Python orienté objet avec hiérarchie d'héritage
- **Interface** : 3 panneaux (navigation, affichage contextuel, console)
- **Commandes** : En français sans accents obligatoires
- **Système de coordonnées** : Secteurs entiers + positions décimales

## Système de Jeu Principal

### Mécanique de base (Daggerheart)
- Lancer 2d12 (Hope + Fear)
- Résultat > seuil = succès 
- Résultat < seuil = échec 
Si le dé Hope est plus grand que le dé de Fear, alors le joueur gagne un jeton de d'espoir (Hope), si le dé de Fear est plus grand alors le Joueur "gagne" un jeton de Peur (Fear)
- Égalité des dés = succès critique avec Hope
- Jetons Fear déclenchent des événements narratifs cachés

### Focus du jeu
1. **Phase actuelle** : Exploration et commerce
2. **Phase future** : Conquête (réservée pour plus tard)
3. **Économie** : Chaînes de production à 3 niveaux

## Structure des Documents

### Documents de référence principaux
- `GDD_Central.md` - Document maître avec vue d'ensemble
- `GDD_Vaisseaux_Complet.md` - Système complet des vaisseaux (propulsion, cargo, composants)
- `GDD_Bases_Spatiales.md` - Construction et gestion des stations
- `GDD_Univers_Generation.md` - Algorithmes de génération procédurale
- `GDD_Economie_Complete.md` - Système économique complet
- `GDD_Architecture_Technique.md` - Spécifications techniques
- `GDD_Systeme_Decouverte.md` - Mécanique d'exploration

### Documents de suivi
- `GUIDE_DEMARRAGE.md` - Instructions de démarrage
- `RECAPITULATIF_SESSION.md` - État actuel du projet
- `CORRECTIONS_IMPORTANTES.md` - Points critiques à respecter

## Règles de Développement

### Principes clés
1. **Utiliser les valeurs exactes** des GDD - ne pas inventer de nouvelles valeurs
2. **Système Fear caché** - jamais affiché au joueur
3. **Persistance narrative** - les événements générés persistent dans le monde
4. **Un joueur = un personnage = un vaisseau** à la fois
5. **Modularité** - système conçu pour supporter plusieurs univers SF

### Points d'attention
- Les vaisseaux ont 12 emplacements de composants
- Système de propulsion détaillé (subluminique, hyperpropulsion, saut)
- Découverte stellaire basée sur formule : `portée = Puissance_Solaire * Coefficient_Découverte`
- Économie avec ressources de base, transformées et finales

## Univers Supportés
- Star Wars
- Warhammer 40K
- Star Citizen
- Univers original (inspiré Empire Galactique de François Nédélec)

## Prochaines Étapes de Développement
1. Implémentation du système économique complet
2. Expansion des mécaniques d'exploration
3. Phase conquête (ultérieure)
4. Support multilingue (optionnel)

## Notes Importantes
- Toujours consulter les GDD pour les valeurs numériques
- Le système Fear doit rester invisible mais cohérent
- Privilégier l'exactitude sur l'improvisation
- Architecture orientée objet avec héritage pour les vaisseaux/stations

---

**Pour commencer** : Lire `GDD_Central.md` puis `GDD_Vaisseaux_Complet.md` pour comprendre les systèmes principaux.
