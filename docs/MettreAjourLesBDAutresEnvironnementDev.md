 🔄 Pour les autres environnements de développement

  Voici la procédure complète pour reconstruire l'univers :

  # 1. Migration et seed (30 secondes)
  php artisan migrate:fresh --seed

  # 2. Enrichissement des noms (5 secondes)
  php artisan gaia:enrich-common-names

  # 3. Import des exoplanètes NASA (3-5 minutes)
  php artisan exoplanet:import-real --gaia-only

  Total : ~4-6 minutes pour avoir un univers complet avec données réelles NASA ✓

  Les 191 systèmes restants gardent leurs planètes procédurales, ce qui est parfait pour le jeu !
