/**
 * Console Resizer - Gestion du redimensionnement de la console
 * Utilisé dans toutes les vues pour une expérience cohérente
 */

document.addEventListener('DOMContentLoaded', function() {
    // Charger la largeur sauvegardée pour chaque console
    document.querySelectorAll('.console-container').forEach(function(consoleContainer) {
        // Récupérer la largeur sauvegardée dans localStorage
        const savedWidth = localStorage.getItem('consoleWidth');
        if (savedWidth) {
            consoleContainer.style.width = savedWidth + 'px';
        }
    });

    // Sélectionner tous les conteneurs de console avec la classe console-container
    document.querySelectorAll('.console-container').forEach(function(consoleContainer) {
        const consoleResizer = consoleContainer.querySelector('.console-resizer');

        if (consoleResizer) {
            let isResizing = false;
            let startX, startWidth;

            // Démarrer le redimensionnement
            consoleResizer.addEventListener('mousedown', function(e) {
                isResizing = true;
                startX = e.clientX;
                startWidth = consoleContainer.offsetWidth;
                e.preventDefault();
                consoleResizer.style.backgroundColor = '#06b6d4'; // Cyan pour indiquer le mode redimensionnement
            });

            // Redimensionner
            document.addEventListener('mousemove', function(e) {
                if (!isResizing) return;

                const newWidth = startWidth - (e.clientX - startX);

                // Appliquer les limites min/max
                // Min: 200px (pour éviter que ce soit trop petit)
                // Max: 50% de la largeur de l'écran (comme demandé)
                const minWidth = 200;
                const maxWidth = window.innerWidth * 0.5; // 50% de la largeur de l'écran

                if (newWidth >= minWidth && newWidth <= maxWidth) {
                    consoleContainer.style.width = newWidth + 'px';
                    // Sauvegarder la largeur pour les autres pages
                    localStorage.setItem('consoleWidth', newWidth);
                }
            });

            // Arrêter le redimensionnement
            document.addEventListener('mouseup', function() {
                isResizing = false;
                if (consoleResizer) {
                    consoleResizer.style.backgroundColor = ''; // Retour à la couleur normale
                }
            });

            // Empêcher la sélection de texte pendant le redimensionnement
            document.addEventListener('selectstart', function(e) {
                if (isResizing) {
                    e.preventDefault();
                }
            });
        }
    });
});

// Export pour utilisation avec des modules si nécessaire
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { init: function() {} };
}