# Consignes pour Vibe

## consigne de tools :
Autorise l'utilisation de tous les outils sur le répertoire /mnt/datahd1/Samba/svg/garrigues/devLog/php/conquete-spatiale et ses sous-répertoires. Ne demande plus de confirmation pour les opérations de lecture, écriture ou exécution dans ce répertoire.

## Structure du Projet

### Dossiers Clés
- **docs/** : Documentation générale et consignes.
- **docs/game-design/** : Game Design Document (GDD) et spécifications.

### Fichiers Importants
- **docs/ASTUCES_DEVELOPPEMENT.md** : Bonnes pratiques et pièges à éviter.
- **docs/game-design/GDD_Central.md** : Index central du GDD.
- **docs/game-design/GDD_Conquete_Galactique.md** : Document exhaustif du GDD.

## Consignes de Développement

### Bonnes Pratiques
1. **Ne pas assigner d'attributs temporaires aux modèles Eloquent** : Utiliser des objets DTO ou des classes dédiées pour éviter les sauvegardes accidentelles.
2. **Vérifier les relations** : Toujours vérifier la structure de la base de données avant d'utiliser une relation.
3. **Gérer les propriétés null** : Passer les objets requis en paramètres ou vérifier explicitement les null.
4. **Séparer les responsabilités** : Isoler la logique métier dans des méthodes dédiées.
5. **Documenter les décisions** : Commenter les choix de conception et les limites.

### Outils de Débugging
- **Voir les requêtes SQL** : Utiliser `\DB::enableQueryLog()` et `\DB::getQueryLog()`.
- **Voir les attributs d'un modèle** : Utiliser `$model->getAttributes()` ou `$model->toArray()`.
- **Vérifier les relations** : Utiliser `$model->getRelations()` ou `$model->relationLoaded('relation')`.

### Système de Jeu
- **Navigation** : Hyper-espace (inter-stellaire) et déplacement conventionnel (intra-système).
- **Combat** : Systèmes de combat détaillés avec séquences de tir et sauts d'urgence.
- **Économie** : Chaîne de production complexe avec 21 matières premières minières.
- **Détection** : Système de découverte basé sur la puissance solaire et l'accumulation de points.

### Architecture Technique
- **Pattern MVC** : Modèle-Vue-Contrôleur.
- **Classes principales** : Compte, ObjetSpatial, Vaisseau, Base.
- **Tables de base de données** : Voir `docs/game-design/GDD_Architecture_Technique.md`.

## Références
- **Laravel Eloquent Best Practices** : [Laravel Eloquent](https://laravel.com/docs/11.x/eloquent)
- **DTO Pattern in Laravel** : [Laravel News](https://laravel-news.com/data-transfer-object)
- **Debugging Laravel Queries** : [Laravel Queries](https://laravel.com/docs/11.x/queries#debugging)
- **Structure des Objets** : [STRUCTURE_OBJETS.md](./STRUCTURE_OBJETS.md)

## Dernière Mise à Jour
- **Date** : 2025-12-11
- **Version** : 1.0
