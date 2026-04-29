Résumé des commandes cache

  # Nettoyer uniquement les vues (Blade)
  php artisan view:clear

  # OU tout nettoyer d'un coup (recommandé)
  php artisan optimize:clear

  # OU manuellement chaque cache
  php artisan cache:clear      # Cache application
  php artisan config:clear     # Cache config
  php artisan route:clear      # Cache routes
  php artisan view:clear       # Cache vues
