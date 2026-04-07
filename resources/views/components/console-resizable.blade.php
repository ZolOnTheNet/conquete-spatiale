@props(['width' => '25%'])

<!-- Console Droite Redimensionnable -->
<div class="console-container border-l border-gray-700 relative flex" style="width: {{ $width }};">
    <!-- Poignée de redimensionnement -->
    <div class="console-resizer w-1 h-full cursor-col-resize bg-gray-700 hover:bg-cyan-500 transition-colors"></div>

    <!-- Contenu de la console - prend toute la largeur disponible -->
    <div class="flex-1 min-w-0">
        {{ $slot }}
    </div>
</div>

<!-- Charger le script de redimensionnement -->
@once
    @push('scripts')
        <script src="{{ asset('js/console-resizer.js') }}"></script>
        <script>
        // Gestion de la taille de la police
        function adjustFontSize(delta) {
            const consoleOutput = document.getElementById('console-output');
            if (consoleOutput) {
                let currentSize = parseFloat(getComputedStyle(consoleOutput).fontSize);
                let newSize = currentSize + delta;
                
                // Limites de taille (8px à 20px)
                newSize = Math.max(8, Math.min(20, newSize));
                
                consoleOutput.style.fontSize = newSize + 'px';
                localStorage.setItem('consoleFontSize', newSize);
            }
        }
        
        // Charger la taille sauvegardée au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const savedSize = localStorage.getItem('consoleFontSize');
            if (savedSize) {
                const consoleOutput = document.getElementById('console-output');
                if (consoleOutput) {
                    consoleOutput.style.fontSize = savedSize + 'px';
                }
            }
        });
        </script>
    @endpush
@endonce