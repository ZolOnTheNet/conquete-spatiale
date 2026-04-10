@extends('layouts.app')

@section('title', 'Timonerie')

@section('content')

<script>

// CONSOLE REDIMENSIONNABLE
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    const consoleContainer = document.querySelector('.console-container');
    const consoleResizer = document.querySelector('.console-resizer');

    if (consoleContainer && consoleResizer) {
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
            const minWidth = 200;
            const maxWidth = window.innerWidth * 0.6; // 60% de la largeur de l'écran

            if (newWidth >= minWidth && newWidth <= maxWidth) {
                consoleContainer.style.width = newWidth + 'px';
            }
        });

        // Arrêter le redimensionnement
        document.addEventListener('mouseup', function() {
            isResizing = false;
            consoleResizer.style.backgroundColor = ''; // Retour à la couleur normale
        });

        // Empêcher la sélection de texte pendant le redimensionnement
        document.addEventListener('selectstart', function(e) {
            if (isResizing) {
                e.preventDefault();
            }
        });
    } else {
        console.error('Console redimensionnable: éléments non trouvés');
    }
});
===================================================================================
// FONCTIONS DE NAVIGATION
// ============================================================================

async function calculerSaut(destinationId) {
    try {
        const response = await fetch('{{ route("navire.timonerie.calculer-saut") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ destination_id: destinationId })
        });
        const data = await response.json();
        if (data.error) {
            sendCommand(`calculer-saut ${destinationId}`);
            appendToConsole('[ERREUR] ' + data.error, 'text-red-400');
        } else {
            sendCommand(`calculer-saut ${destinationId}`);
            // Convert distance from AL to Gm/G km for display
            const distanceGm = data.distance * 149.6;
            const formattedDistance = distanceGm >= 1000
                ? (distanceGm / 1000).toFixed(2) + ' G km'
                : distanceGm.toFixed(2) + ' Gm';

            appendToConsole('🔍 Calcul de saut vers ' + data.destination, 'text-cyan-400');
            appendToConsole('Distance: ' + data.distance + ' AL (' + formattedDistance + ')', 'text-gray-300');
            appendToConsole('Énergie requise: ' + data.energieRequise + ' (disponible: ' + data.energieDisponible + ')', 'text-gray-300');
            appendToConsole('PA requis: ' + data.paRequis + ' (disponibles: ' + data.paDisponibles + ')', 'text-gray-300');

            if (data.accessible) {
                appendToConsole('✓ Saut possible', 'text-green-400');
            } else {
                appendToConsole('✗ Ressources insuffisantes', 'text-red-400');
            }
            appendToConsole('---', 'text-gray-500');
        }
    } catch (error) {
        sendCommand(`calculer-saut ${destinationId}`);
        appendToConsole('[ERREUR] Erreur de calcul: ' + error.message, 'text-red-400');
    }
}

async function effectuerSaut(destinationId) {
    try {
        const response = await fetch('{{ route("navire.timonerie.effectuer-saut") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ destination_id: destinationId })
        });
        const data = await response.json();
        if (data.error) {
            // Afficher l'erreur dans la console
            sendCommand(`saut ${destinationId}`); // Pour avoir l'écho de la commande
            appendToConsole('[ERREUR] ' + data.error, 'text-red-400');
        } else if (data.success) {
            const precisionPercent = Math.round(data.arrivee.precision * 100);
            // Afficher le résultat dans la console
            sendCommand(`saut ${destinationId}`); // Pour avoir l'écho de la commande
            appendToConsole('✓ ' + data.message, 'text-green-400');
            appendToConsole('Jet de navigation: ' + data.jetNavigation, 'text-gray-300');
            appendToConsole("Position d'arrivée:", 'text-gray-300');
            appendToConsole('  Secteur: (' + data.arrivee.secteur_x + ', ' + data.arrivee.secteur_y + ', ' + data.arrivee.secteur_z + ')', 'text-gray-300');
            appendToConsole('  Position: (' + data.arrivee.position_x + ', ' + data.arrivee.position_y + ', ' + data.arrivee.position_z + ') AL', 'text-gray-300');
            appendToConsole('  Précision: ' + precisionPercent + '% d\'écart (max ' + data.arrivee.ecart_max_ua + ' UA)', 'text-gray-300');
            appendToConsole('Ressources restantes:', 'text-gray-300');
            appendToConsole('  Énergie: ' + data.energieRestante, 'text-gray-300');
            appendToConsole('  PA: ' + data.paRestants, 'text-gray-300');
            appendToConsole('---', 'text-gray-500');

            // Mettre à jour les informations du jeu
            updateGameInfo({
                energie_actuelle: data.energieRestante,
                pa_restants: data.paRestants,
                position: '(' + data.arrivee.position_x + ', ' + data.arrivee.position_y + ', ' + data.arrivee.position_z + ') AL'
            });

            // Recharger la page après un court délai pour mettre à jour la position
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    } catch (error) {
        sendCommand(`saut ${destinationId}`);
        appendToConsole('[ERREUR] Erreur lors du saut: ' + error.message, 'text-red-400');
    }
}

async function sApprocher(poiId, poiType) {
    try {
        const response = await fetch('{{ route("navire.timonerie.s-approcher") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ poi_id: poiId, poi_type: poiType })
        });
        const data = await response.json();

        if (data.error) {
            // Afficher l'erreur dans la console
            sendCommand(`s-approcher ${poiId}`);
            appendToConsole('[ERREUR] ' + data.error, 'text-red-400');
        } else if (data.success) {
            // Afficher le résultat dans la console
            sendCommand(`s-approcher ${poiId}`);
            appendToConsole('✓ ' + data.message, 'text-green-400');

            if (data.pourcentageTrajet) {
                appendToConsole('⚠️ Déplacement partiel: ' + data.pourcentageTrajet + '% du trajet', 'text-yellow-400');
            }

            // Convert distances from UA to Gm/G km
            const distanceParcourueGm = data.distanceParcourue * 149.6;
            const distanceParcourueDisplay = distanceParcourueGm >= 1000
                ? (distanceParcourueGm / 1000).toFixed(2) + ' G km'
                : distanceParcourueGm.toFixed(2) + ' Gm';

            const distanceRestanteGm = data.distanceRestante * 149.6;
            const distanceRestanteDisplay = distanceRestanteGm >= 1000
                ? (distanceRestanteGm / 1000).toFixed(2) + ' G km'
                : distanceRestanteGm.toFixed(2) + ' Gm';

            appendToConsole('Distance parcourue: ' + data.distanceParcourue + ' UA (' + distanceParcourueDisplay + ')', 'text-gray-300');
            appendToConsole('Distance restante: ' + data.distanceRestante + ' UA (' + distanceRestanteDisplay + ')', 'text-gray-300');
            appendToConsole('Nouvelle position: (' + data.nouvellePosition.x + ', ' + data.nouvellePosition.y + ', ' + data.nouvellePosition.z + ') AL', 'text-gray-300');
            appendToConsole('Ressources consommées:', 'text-gray-300');
            appendToConsole('  Énergie: -' + data.energieConsommee + ' (restante: ' + data.energieRestante + ')', 'text-gray-300');
            appendToConsole('  PA: -' + data.paConsommes + ' (restants: ' + data.paRestants + ')', 'text-gray-300');
            appendToConsole('---', 'text-gray-500');

            // Mettre à jour les informations du jeu
            updateGameInfo({
                energie_actuelle: data.energieRestante,
                pa_restants: data.paRestants,
                position: '(' + data.nouvellePosition.x + ', ' + data.nouvellePosition.y + ', ' + data.nouvellePosition.z + ') AL'
            });

            // Recharger la page après un court délai pour mettre à jour la position
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            sendCommand(`s-approcher ${poiId}`);
            appendToConsole(data.message || 'Déplacement effectué', 'text-gray-300');
        }
    } catch (error) {
        sendCommand(`s-approcher ${poiId}`);
        appendToConsole('[ERREUR] Erreur: ' + error.message, 'text-red-400');
    }
}

// Fonction pour mettre à jour la destination de saut avec un POI spécifique
function updateSautDestination(selectElement, secteurX, secteurY, secteurZ) {
    const poiId = selectElement.value;
    const destinationName = selectElement.closest('.bg-gray-900\/50').querySelector('.text-white.font-semibold').textContent;
    
    if (poiId === 'systeme') {
        // Saut vers le système lui-même (comportement par défaut)
        console.log(`Destination: ${destinationName} (système)`);
        // Ici vous pourrez ajouter la logique pour cibler le système
    } else {
        // Saut vers un POI spécifique
        const poiName = selectElement.options[selectElement.selectedIndex].text;
        console.log(`Destination: ${destinationName} → POI: ${poiName} (ID: ${poiId})`);
        // Ici vous pourrez ajouter la logique pour cibler le POI spécifique
        // Exemple: sendCommand(`saut ${secteurX} ${secteurY} ${secteurZ} ${poiId}`)
    }
}

async function sAmarrer(stationId) {
    try {
        const response = await fetch('{{ route("navire.timonerie.s-amarrer") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ station_id: stationId })
        });
        const data = await response.json();

        if (data.error) {
            // Afficher l'erreur dans la console
            sendCommand(`s-amarrer ${stationId}`);
            appendToConsole('[ERREUR] ' + data.error + (data.message ? '\n' + data.message : ''), 'text-red-400');
        } else if (data.success) {
            // Convert distance from UA to Gm/G km
            const distanceGm = data.distance * 149.6;
            const distanceDisplay = distanceGm >= 1000
                ? (distanceGm / 1000).toFixed(2) + ' G km'
                : distanceGm.toFixed(2) + ' Gm';

            // Afficher le résultat dans la console
            sendCommand(`s-amarrer ${stationId}`);
            appendToConsole('✓ ' + data.message, 'text-green-400');
            appendToConsole('Station: ' + data.station.nom, 'text-gray-300');
            appendToConsole('Distance: ' + data.distance + ' UA (' + distanceDisplay + ')', 'text-gray-300');
            appendToConsole('Ressources restantes:', 'text-gray-300');
            appendToConsole('  Énergie: ' + data.energieRestante, 'text-gray-300');
            appendToConsole('  PA: ' + data.paRestants, 'text-gray-300');
            appendToConsole('---', 'text-gray-500');
            appendToConsole('📢 Vous avez maintenant accès au menu Station !', 'text-cyan-400');
            appendToConsole('→ <a href="{{ route('station.hall') }}" class="text-cyan-400 underline font-bold">Accéder au Hall de la Station</a>', 'text-cyan-400');

            // Mettre à jour les informations du jeu
            updateGameInfo({
                energie_actuelle: data.energieRestante,
                pa_restants: data.paRestants
            });

            // Rediriger vers le hall de la station après un court délai
            setTimeout(() => {
                window.location.href = '{{ route('station.hall') }}';
            }, 2000);
        } else {
            sendCommand(`s-amarrer ${stationId}`);
            appendToConsole(data.message || 'Amarrage effectué', 'text-gray-300');
        }
    } catch (error) {
        sendCommand(`s-amarrer ${stationId}`);
        appendToConsole('[ERREUR] Erreur: ' + error.message, 'text-red-400');
    }
}
</script>
<div class="h-screen flex flex-col bg-gray-900">

    {{-- Header 4 colonnes --}}
    <x-game-header
        :personnage="$personnage"
        :vaisseau="$vaisseau"
        :systeme="$systemeActuel"
    />

    {{-- Layout principal : Menu + Contenu --}}
    <div class="flex-1 flex overflow-hidden">

        {{-- Menu latéral gauche --}}
        @include('game.partials.menu-lateral', [
            'personnage' => $personnage,
            'vaisseau' => $vaisseau,
            'compte' => auth()->user()
        ])

        {{-- Zone de contenu principale --}}
        <main class="flex-1 overflow-auto p-6">
            <div class="max-w-full mx-auto">

                <h2 class="text-3xl font-orbitron text-cyan-400 mb-6">🚀 TIMONERIE</h2>

                @if(session()->has('dernier_calcul_saut'))
                <div class="bg-cyan-900/30 border border-cyan-500/50 rounded-lg p-3 mb-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-cyan-400 font-bold">📊 CALCUL EN COURS</span>
                            <span class="text-white">
                                {{ session('dernier_calcul_saut.destination_nom') }}
                                @if(session('dernier_calcul_saut.poi_cible') !== 'systeme')
                                    → {{ session('dernier_calcul_saut.poi_nom') }}
                                @endif
                            </span>
                            <span class="text-gray-400 text-sm">
                                ({{ number_format(session('dernier_calcul_saut.distance'), 1) }} AL,
                                {{ session('dernier_calcul_saut.energie_requise') }} E,
                                {{ session('dernier_calcul_saut.pa_requis') }} PA)
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-400">
                                Valide jusqu'à {{ date('H:i:s', session('dernier_calcul_saut.valid_until')) }}
                            </span>
                            <button onclick="annulerCalculSaut()"
                                    class="text-xs bg-red-600 hover:bg-red-700 px-2 py-1 rounded transition"
                                    title="Annuler ce calcul">
                                ❌ Annuler
                            </button>
                            <button onclick="ameliorerCalculSaut()"
                                    class="text-xs bg-blue-600 hover:bg-blue-700 px-2 py-1 rounded transition"
                                    title="Améliorer le calcul (coûte 1 PA)">
                                ⬆️ Améliorer (1 PA)
                            </button>
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-gray-400">
                        @if(session('dernier_calcul_saut.est_critique'))
                            <span class="text-yellow-400 font-bold">⭐ CRITIQUE ⭐</span> |
                        @endif
                        @if(session('dernier_calcul_saut.est_espoir'))
                            <span class="text-green-400 font-bold">⬆️ ESPOIR</span> |
                        @endif
                        @if(session('dernier_calcul_saut.est_peur'))
                            <span class="text-red-400 font-bold">⬇️ PEUR</span> |
                        @endif
                        <br>
                        <span class="font-bold">Jet Navigation:</span>
                        [{{ session('dernier_calcul_saut.de1') }} + {{ session('dernier_calcul_saut.de2') }}]
                        + {{ session('dernier_calcul_saut.jetDetails.details.intelligence', 0) }} Int
                        + {{ session('dernier_calcul_saut.jetDetails.details.navigation', 0) }} Nav
                        + {{ session('dernier_calcul_saut.jetDetails.details.ordinateur', 0) }} Ord
                        + {{ session('dernier_calcul_saut.jetDetails.details.module', 0) }} Mod
                        = {{ session('dernier_calcul_saut.jet_navigation') }}
                        <br>
                        <span class="font-bold">Delta Position:</span>
                        [{{ implode('+', session('dernier_calcul_saut.delta_d10', [0,0,0])) }} - 15]
                        + {{ session('dernier_calcul_saut.delta_d2_signe') > 0 ? '+' : '' }}{{ session('dernier_calcul_saut.delta_d2_signe') }}
                        + {{ session('dernier_calcul_saut.score_erreur') }}
                        = {{ session('dernier_calcul_saut.delta_somme_d10') + session('dernier_calcul_saut.delta_d2_signe') + session('dernier_calcul_saut.score_erreur') }}
                        / 100
                        <br>
                        Score d'erreur: {{ session('dernier_calcul_saut.score_erreur') }} |
                        Précision: {{ number_format(100 - session('dernier_calcul_saut.score_erreur') * 0.5, 1) }}%
                        @if(session('dernier_calcul_saut.hope_gain') > 0)
                            | <span class="text-green-400">+{{ session('dernier_calcul_saut.hope_gain') }} Hope</span>
                        @endif
                        @if(session('dernier_calcul_saut.fear_gain') > 0)
                            | <span class="text-red-400">+{{ session('dernier_calcul_saut.fear_gain') }} Fear</span>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Position actuelle --}}
                <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-4 mb-6">
                    <h3 class="text-lg text-cyan-300 mb-3">Position actuelle</h3>
                    <div class="flex flex-wrap items-center gap-4 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-400">Secteur:</span>
                            <span class="text-yellow-400 font-mono">
                                ({{ $objetSpatial->secteur_x }}, {{ $objetSpatial->secteur_y }}, {{ $objetSpatial->secteur_z }})
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-400">Position:</span>
                            <span class="text-cyan-400 font-mono ml-2">
                                ({{ number_format($objetSpatial->position_x/100, 2) }}, {{ number_format($objetSpatial->position_y/100, 2) }}, {{ number_format($objetSpatial->position_z/100, 2) }}) UA
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-400">Système:</span>
                            <span class="text-green-400">{{ $systemeActuel->nom ?? 'Espace profond' }}</span>
                        </div>
                        <div class="flex gap-2 ml-auto">
                            @php
                                $puissanceAffichage = isset($systemeActuel->puissance_solaire) ? max(5, $systemeActuel->puissance_solaire) : 5;
                            @endphp
                            <button onclick="sendCommand('recharger 1')"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs transition whitespace-nowrap flex items-center gap-1 @if($vaisseau->energie_actuelle >= $vaisseau->reserve) disabled bg-gray-600 cursor-not-allowed @endif"
                                    title="Recharger pour 1 PA (+{{ $puissanceAffichage }} énergie) @if($vaisseau->energie_actuelle >= $vaisseau->reserve) - Vaisseau déjà plein @endif"
                                    @if($vaisseau->energie_actuelle >= $vaisseau->reserve) disabled @endif>
                                ⚡ R+{{ $puissanceAffichage }}
                            </button>
                            <button onclick="sendCommand('recharger full')"
                                    class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded text-xs transition whitespace-nowrap flex items-center gap-1 @if($vaisseau->energie_actuelle >= $vaisseau->reserve) disabled bg-gray-600 cursor-not-allowed @endif"
                                    title="Recharger au maximum @if($vaisseau->energie_actuelle >= $vaisseau->reserve) - Vaisseau déjà plein @endif"
                                    @if($vaisseau->energie_actuelle >= $vaisseau->reserve) disabled @endif>
                                ⚡ R>>>100%
                            </button>
                        </div>
                    </div>
                </div>

                {{-- RADAR MULTI-VUES --}}
                <div class="bg-gray-800/50 border border-purple-500/30 rounded-lg p-4 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg text-purple-300 font-bold">Radar</h3>
                        <div class="flex items-center gap-4">
                            <div class="text-sm text-gray-400">
                                Azimut: <span id="radar-azimut" class="text-cyan-400 font-mono">{{ round($objetSpatial->azimut, 1) }}°</span>
                            </div>
                            <div class="text-sm text-gray-400">
                                Zoom: <span id="radar-zoom-label" class="text-cyan-400 font-mono">x1</span>
                            </div>
                        </div>
                    </div>

                    {{-- Ligne unique : Onglets + Rotation + Zoom --}}
                    <div class="flex justify-between items-center gap-2 mb-4">
                        {{-- Onglets de vue à gauche --}}
                        <div class="flex gap-2">
                            <button onclick="switchRadarView('vue')" id="tab-vue"
                                    class="px-4 py-2 bg-purple-600 text-white rounded transition hover:bg-purple-500">
                                Vue Perspective
                            </button>
                            <button onclick="switchRadarView('dessus')" id="tab-dessus"
                                    class="px-4 py-2 bg-gray-700 text-gray-300 rounded transition hover:bg-gray-600">
                                Vue Dessus
                            </button>
                            <button onclick="switchRadarView('distance')" id="tab-distance"
                                    class="px-4 py-2 bg-gray-700 text-gray-300 rounded transition hover:bg-gray-600">
                                Vue Distance
                            </button>
                        </div>

                        {{-- Contrôles rotation + zoom à droite --}}
                        <div class="flex gap-2 items-center">
                            {{-- Rotation --}}
                            <button onclick="tournerVaisseau('gauche')"
                                    class="bg-cyan-600 hover:bg-cyan-500 text-white px-3 py-2 rounded transition"
                                    title="Tourner à gauche (15°)">
                                ⬅️ Gauche
                            </button>
                            <button onclick="tournerVaisseau('droite')"
                                    class="bg-cyan-600 hover:bg-cyan-500 text-white px-3 py-2 rounded transition"
                                    title="Tourner à droite (15°)">
                                Droite ➡️
                            </button>

                            {{-- Séparateur visuel --}}
                            <div class="h-8 w-px bg-gray-600 mx-1"></div>

                            {{-- Zoom (ordre inversé pour cohérence de lecture) --}}
                            <button onclick="zoomRadar('out')"
                                    class="bg-purple-600 hover:bg-purple-500 text-white px-3 py-2 rounded transition"
                                    title="Zoom arrière">
                                ➖
                            </button>
                            <button onclick="zoomRadar('in')"
                                    class="bg-purple-600 hover:bg-purple-500 text-white px-3 py-2 rounded transition"
                                    title="Zoom avant">
                                ➕
                            </button>
                            <button onclick="zoomRadar('reset')"
                                    class="bg-gray-600 hover:bg-gray-500 text-white px-3 py-2 rounded transition"
                                    title="Réinitialiser le zoom">
                                ↻
                            </button>
                        </div>
                    </div>

                    {{-- Container pour les 3 vues SVG --}}
                    <div class="flex justify-center relative">
                            {{-- Vue Perspective --}}
                            <svg id="radar-vue" width="100%" height="500" viewBox="0 0 1000 500"
                                 class="bg-black/80 border border-purple-500/50 rounded max-w-full"
                                 preserveAspectRatio="xMidYMid meet">
                                <g id="radar-vue-content"></g>
                            </svg>

                            {{-- Vue Dessus --}}
                            <svg id="radar-dessus" width="100%" height="500" viewBox="0 0 1000 500"
                                 class="bg-black/80 border border-purple-500/50 rounded hidden max-w-full"
                                 preserveAspectRatio="xMidYMid meet">
                                <g id="radar-dessus-content"></g>
                            </svg>

                            {{-- Vue Distance --}}
                            <svg id="radar-distance" width="100%" height="500" viewBox="0 0 1000 500"
                                 class="bg-black/80 border border-purple-500/50 rounded hidden max-w-full"
                                 preserveAspectRatio="xMidYMid meet">
                                <g id="radar-distance-content"></g>
                            </svg>
                    </div>

                    {{-- Légende --}}
                    <div class="mt-4 text-xs text-gray-400 space-y-1">
                        <div id="legend-vue" class="text-center font-semibold text-cyan-300">
                            Vue Perspective : Zone Angle (20% haut) = Systèmes par angle, taille = détectabilité × distance / 10 (min 20px) | Zone Système (80% bas) = POI avec distance et perspective
                        </div>
                        <div id="legend-dessus" class="text-center font-semibold text-cyan-300 hidden">
                            Vue Dessus : Projection 2D (X,Y) - Ignore l'axe Z
                        </div>
                        <div id="legend-distance" class="text-center font-semibold text-cyan-300 hidden">
                            Vue Distance : X = Distance (70% local, 30% systèmes) / Y = Angle (-180° à +180°)
                        </div>
                        <div class="flex gap-6 justify-center">
                            <div>Bordure <span style="color: #00FF00;">verte</span> = < 1 UA</div>
                            <div>Bordure <span style="color: #FFFF00;">jaune</span> = < 5 UA</div>
                            <div>Bordure <span style="color: #FFA500;">orange</span> = < 20 UA</div>
                            <div>Bordure <span style="color: #FF4444;">rouge</span> = > 20 UA</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 2xl:grid-cols-2 gap-6">

                    {{-- Colonne gauche : Sauts hyperspatiaux --}}
                    <div class="bg-gray-800/50 border border-yellow-500/30 rounded-lg p-4">
                        <h3 class="text-xl text-yellow-400 font-bold mb-4 flex items-center gap-2">
                            <span>⚡</span>
                            <span>Sauts Hyperspatiaux</span>
                        </h3>

                        <div class="space-y-2 max-h-[600px] overflow-y-auto">
                            @forelse($sautsDisponibles as $destination)
                            <div class="bg-gray-900/50 border border-gray-700 rounded px-3 py-2 hover:border-yellow-500/50 transition flex items-center gap-3 text-sm"
                                 data-cartouche-poi-id="saut-{{ $destination->id }}"
                                 data-poi-azimut-absolu="{{ $destination->azimut_absolu ?? 0 }}">
                                <div class="flex-1 min-w-0">
                                    <span class="text-white font-semibold">{{ $destination->nom }}</span>
                                    <div class="text-gray-500 text-xs poi-azimut-display">
                                        Az: {{ number_format($destination->azimut_relatif ?? 0, 1) }}°
                                    </div>
                                </div>
                                <div class="text-yellow-400 whitespace-nowrap">
                                    @php
                                        // Afficher la distance en secteurs ou en AL avec 1 décimale
                                        if ($destination->distance_secteurs > 0) {
                                            echo number_format($destination->distance_secteurs, 0) . ' secteurs';
                                        } else {
                                            echo number_format($destination->distance, 1) . ' AL';
                                        }
                                    @endphp
                                </div>
                                
                                <!-- Combobox pour sélectionner le POI cible -->
                                @php
                                    // Vérifier si les POI sont disponibles (plusieurs façons possibles)
                                    $hasPoi = false;
                                    $pois = [];
                                    
                                    // Méthode 1: pois_connus (si la relation est chargée)
                                    if (isset($destination->pois_connus) && $destination->pois_connus->count() > 0) {
                                        $hasPoi = true;
                                        $pois = $destination->pois_connus;
                                    }
                                    // Méthode 2: pois (si la propriété existe)
                                    elseif (isset($destination->pois) && count($destination->pois) > 0) {
                                        $hasPoi = true;
                                        $pois = $destination->pois;
                                    }
                                    // Méthode 3: pois_list (pour compatibilité)
                                    elseif (isset($destination->pois_list) && count($destination->pois_list) > 0) {
                                        $hasPoi = true;
                                        $pois = $destination->pois_list;
                                    }
                                    
                                    // DEBUG: Forcer l'affichage pour Sol en environnement local
                                    if (app()->environment('local') && $destination->nom === 'Sol') {
                                        $hasPoi = true;
                                        $pois = [
                                            (object)['id' => 1, 'icone' => '🌍', 'nom' => 'Terre'],
                                            (object)['id' => 2, 'icone' => '🔴', 'nom' => 'Mars'],
                                            (object)['id' => 3, 'icone' => '☀️', 'nom' => 'Soleil'],
                                        ];
                                    }
                                @endphp
                                
                                @if($hasPoi)
                                    <select 
                                        class="bg-gray-700 border border-gray-600 text-white text-xs rounded px-2 py-1 hover:border-cyan-400 transition min-w-[120px] max-w-[180px]"
                                        onchange="updateSautDestination(this, {{ $destination->secteur_x }}, {{ $destination->secteur_y }}, {{ $destination->secteur_z }})"
                                        data-destination-id="{{ $destination->id }}">
                                        <option value="systeme" selected>&lt;système&gt;</option>
                                        @foreach($pois as $poi)
                                            <option value="{{ $poi->id ?? $poi['id'] ?? '' }}">{!! $poi->icone ?? $poi['icone'] ?? '' !!} {{ $poi->nom ?? $poi['nom'] ?? 'POI' }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <!-- DEBUG: Commentaire pour aider au débogage -->
                                    @if(app()->environment('local'))
                                        <!-- DEBUG: Aucun POI trouvé pour {{ $destination->nom }} -->
                                        @php
                                            // Afficher les propriétés disponibles pour le débogage
                                            $debugProps = [];
                                            if (isset($destination->pois_connus)) $debugProps[] = 'pois_connus: ' . (is_object($destination->pois_connus) ? get_class($destination->pois_connus) : 'non objet');
                                            if (isset($destination->pois)) $debugProps[] = 'pois: ' . (is_array($destination->pois) ? 'array['.count($destination->pois).']' : gettype($destination->pois));
                                            if (isset($destination->pois_list)) $debugProps[] = 'pois_list: ' . (is_array($destination->pois_list) ? 'array['.count($destination->pois_list).']' : gettype($destination->pois_list));
                                        @endphp
                                        <!-- DEBUG: Propriétés disponibles: {{ implode(', ', $debugProps) }} -->
                                    @endif
                                @endif
                                
                                <button onclick="sendCommand('saut {{ $destination->secteur_x }} {{ $destination->secteur_y }} {{ $destination->secteur_z }}')"
                                        class="bg-cyan-600 hover:bg-cyan-700 disabled:bg-gray-600 disabled:cursor-not-allowed text-white px-3 py-1.5 rounded text-xs transition whitespace-nowrap"
                                        @if(!$destination->accessible) disabled @endif>
                                    🧮 Calcul ({{ $destination->paRequis }} PA)
                                </button>
                                <button onclick="sendCommand('saut {{ $destination->secteur_x }} {{ $destination->secteur_y }} {{ $destination->secteur_z }}')"
                                        class="bg-yellow-600 hover:bg-yellow-700 disabled:bg-gray-600 disabled:cursor-not-allowed text-white px-3 py-1.5 rounded text-xs font-semibold transition whitespace-nowrap"
                                        @if(!$destination->accessible) disabled @endif>
                                    ⚡ Saut ({{ $destination->energieRequise }} E)
                                </button>
                            </div>
                            @empty
                            <p class="text-gray-500 text-center py-8">Aucun saut disponible</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Colonne droite : Déplacements conventionnels --}}
                    <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-4">
                        <h3 class="text-xl text-cyan-400 font-bold mb-4 flex items-center gap-2">
                            <span>🎯</span>
                            <span>Déplacements Conventionnels</span>
                        </h3>

                        <div class="space-y-2 max-h-[600px] overflow-y-auto">
                            @forelse($poisSecteur as $poi)
                            <div class="bg-gray-900/50 border border-gray-700 rounded px-3 py-2 hover:border-cyan-500/50 transition flex items-center gap-3 text-sm"
                                 data-cartouche-poi-id="{{ $poi->id }}"
                                 data-poi-azimut-absolu="{{ $poi->azimut_absolu ?? 0 }}"
                                 @if($poi->type_poi === 'planete' && isset($poi->donneesOrbitales))
                                     data-planete-id="{{ $poi->id }}"
                                     data-planete-orbital='@json($poi->donneesOrbitales)'
                                 @endif>
                                <div class="flex-1 min-w-0">
                                    <span class="text-white font-semibold">
                                        {{ $poi->icone }} {{ $poi->nom }}
                                        @if($poi->type_poi === 'planete')
                                            <span class="text-gray-500 text-xs" title="Distance calculée avec système orbital">🪐</span>
                                        @endif
                                    </span>
                                    <div class="text-gray-500 text-xs poi-azimut-display">
                                        Az: {{ number_format($poi->azimut_relatif ?? 0, 1) }}°
                                    </div>
                                </div>
                                <div class="text-cyan-400 whitespace-nowrap planete-distance">
                                    @php
                                        $distanceGm = $poi->distance * 149.6;
                                        if ($distanceGm >= 1000) {
                                            $distanceGkm = $distanceGm / 1000;
                                            echo number_format($distanceGkm, 2) . ' G km';
                                        } else {
                                            echo number_format($distanceGm, 2) . ' Gm';
                                        }
                                    @endphp
                                </div>
                                @if($poi->type_poi === 'planete')
                                    {{-- Bouton Approcher seulement si distance >= 0.01 UA --}}
                                    @if($poi->distance >= 0.01)
                                    <button onclick="sApprocher({{ $poi->id }}, '{{ $poi->type_poi }}')"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs transition whitespace-nowrap">
                                        ➡️ Approcher ({{ $poi->paRequis }} PA)
                                    </button>
                                    @endif

                                    {{-- Bouton Orbiter si distance < 0.1 UA --}}
                                    @if($poi->distance < 0.1)
                                    <button onclick="sOrbiter({{ $poi->id }})"
                                            class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition whitespace-nowrap">
                                        🪐 Orbiter
                                    </button>
                                    @endif
                                @else
                                    {{-- Stations : logique selon état amarrage --}}
                                    @if($poi->est_amarre ?? false)
                                        {{-- Vaisseau AMARRÉ à cette station --}}
                                        @if($personnage->dans_station_id)
                                            {{-- Personnage DANS la station → Retourner au vaisseau --}}
                                            <form method="POST" action="{{ route('station.embarquer') }}" class="inline">
                                                @csrf
                                                <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition whitespace-nowrap">
                                                    🚀 Embarquer
                                                </button>
                                            </form>
                                        @else
                                            {{-- Personnage À BORD → Entrer dans la station --}}
                                            <form method="POST" action="{{ route('station.transborder') }}" class="inline">
                                                @csrf
                                                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition whitespace-nowrap">
                                                    🚪 Transborder
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        {{-- Vaisseau PAS AMARRÉ --}}
                                        @if($poi->distance >= 0.01)
                                            <button onclick="sApprocher({{ $poi->id }}, '{{ $poi->type_poi }}')"
                                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs transition whitespace-nowrap">
                                                ➡️ Approcher ({{ $poi->paRequis }} PA)
                                            </button>
                                        @endif
                                        @if($poi->distance < 1)
                                            <button onclick="sAmarrer({{ $poi->id }})"
                                                    class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition whitespace-nowrap">
                                                🔗 Amarrer
                                            </button>
                                        @endif
                                    @endif
                                @endif
                            </div>
                            @empty
                            <p class="text-gray-500 text-center py-8">Aucun POI détecté dans ce secteur</p>
                            @endforelse
                        </div>
                    </div>

                </div>

                {{-- Zone de résultats --}}
                <div id="resultat-navigation" class="mt-6 bg-gray-800/50 border border-green-500/30 rounded-lg p-4" style="display: none;">
                    <h3 class="text-lg text-green-400 font-bold mb-2">Résultat</h3>
                    <div id="resultat-contenu" class="text-gray-300"></div>
                </div>

            </div>
        </main>

        <!-- Console Droite Redimensionnable -->
        <x-console-resizable>
            @include('game.partials.console')
        </x-console-resizable>
    </div>
</div>

{{-- Inclure le calculateur orbital JavaScript --}}
<script src="{{ asset('js/orbital-calculator.js') }}"></script>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

// ============================================================================
// SYSTÈME DE CALCUL ORBITAL CLIENT-SIDE
// ============================================================================

// Données pour calculs orbitaux
// IMPORTANT: Tous les objets sont dans le même secteur, donc on utilise uniquement position_ en cUA
@if($systemeActuel)
const systemeData = {
    id: {{ $systemeActuel->id }},
    nom: "{{ $systemeActuel->nom }}",
    // Position système en cUA (intra-secteur uniquement)
    position_x: {{ $systemeActuel->position_x ?? 0 }},
    position_y: {{ $systemeActuel->position_y ?? 0 }},
    position_z: {{ $systemeActuel->position_z ?? 0 }}
};
@else
const systemeData = null;
@endif

const vaisseauData = {
    // Position vaisseau en cUA (intra-secteur uniquement)
    position_x: {{ $objetSpatial->position_x ?? 0 }},
    position_y: {{ $objetSpatial->position_y ?? 0 }},
    position_z: {{ $objetSpatial->position_z ?? 0 }},
};

const dateJeu = new Date('{{ $dateJeuActuelle->toIso8601String() }}');

// Fonction pour mettre à jour les distances des planètes en temps réel
function mettreAJourDistancesPlanetes() {
    if (!systemeData) return; // Espace profond

    // DEBUG: Afficher les données système et vaisseau
    console.log('=== DEBUG POSITIONS ===');
    console.log('Système:', systemeData);
    console.log('Vaisseau:', vaisseauData);
    console.log('Date jeu:', dateJeu);

    const timestampJours = OrbitalCalculator.dateToJours(dateJeu);
    console.log('Timestamp jours:', timestampJours);

    // Pour chaque planète dans la liste
    document.querySelectorAll('[data-planete-id]').forEach(element => {
        const planeteId = element.dataset.planeteId;
        const planeteData = JSON.parse(element.dataset.planeteOrbital || '{}');

        if (!planeteData.distance_etoile) return; // Pas de données orbitales

        try {
            // DEBUG: Afficher les données de la première planète
            if (planeteId === document.querySelector('[data-planete-id]').dataset.planeteId) {
                console.log('Planète exemple:', planeteData);
            }

            // Calculer distance en temps réel avec OrbitalCalculator
            const distance = OrbitalCalculator.calculerDistance(
                planeteData,
                systemeData,
                vaisseauData,
                timestampJours
            );

            // Mettre à jour l'affichage
            const distanceElement = element.querySelector('.planete-distance');
            if (distanceElement) {
                distanceElement.textContent = distance.toFixed(2) + ' UA';

                // Ajouter indicateur que c'est calculé côté client
                distanceElement.title = 'Distance calculée en temps réel (client-side)';
            }

            // Debug: afficher dans la console
            console.log(`Planète ${planeteData.nom} (ID ${planeteData.id}): ${distance.toFixed(2)} UA (calculé client-side)`);
        } catch (error) {
            console.error(`Erreur calcul orbital planète ${planeteId}:`, error);
        }
    });
}

// Initialiser les calculs au chargement
if (typeof OrbitalCalculator !== 'undefined') {
    console.log('✓ OrbitalCalculator chargé, calculs orbitaux disponibles');
    // Mettre à jour les distances au chargement (calcul client-side en temps réel)
    mettreAJourDistancesPlanetes();
    console.log('✓ Positions des planètes recalculées côté client');
} else {
    console.error('✗ OrbitalCalculator non disponible');
}

// ============================================================================
// CONSOLE REDIMENSIONNABLE
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    const consoleContainer = document.querySelector('.console-container');
    const consoleResizer = document.querySelector('.console-resizer');

    if (consoleContainer && consoleResizer) {
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
            const minWidth = 200;
            const maxWidth = window.innerWidth * 0.6; // 60% de la largeur de l'écran

            if (newWidth >= minWidth && newWidth <= maxWidth) {
                consoleContainer.style.width = newWidth + 'px';
            }
        });

        // Arrêter le redimensionnement
        document.addEventListener('mouseup', function() {
            isResizing = false;
            consoleResizer.style.backgroundColor = ''; // Retour à la couleur normale
        });

        // Empêcher la sélection de texte pendant le redimensionnement
        document.addEventListener('selectstart', function(e) {
            if (isResizing) {
                e.preventDefault();
            }
        });
    } else {
        console.error('Console redimensionnable: éléments non trouvés');
    }
});
</script>

<!-- Fonctions pour la gestion des calculs de saut -->
<script>
function annulerCalculSaut() {
    if (!confirm('Êtes-vous sûr de vouloir annuler ce calcul de saut ?')) {
        return;
    }

    fetch('{{ route("navire.timonerie.annuler-calcul") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            appendToConsole('✓ Calcul de saut annulé', 'text-gray-300');
            location.reload(); // Recharger pour mettre à jour l'affichage
        }
    })
    .catch(error => {
        appendToConsole('[ERREUR] ' + error.message, 'text-red-400');
    });
}

function ameliorerCalculSaut() {
    if (!confirm('Améliorer ce calcul coûte 1 PA. Continuer ?')) {
        return;
    }

    fetch('{{ route("navire.timonerie.ameliorer-calcul") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            appendToConsole('[ERREUR] ' + data.error, 'text-red-400');
        } else {
            appendToConsole('✓ Calcul amélioré! Nouveau score: ' + data.nouveau_score, 'text-green-400');
            appendToConsole('Précision: ' + data.precision + '%', 'text-gray-300');
            location.reload();
        }
    })
    .catch(error => {
        appendToConsole('[ERREUR] ' + error.message, 'text-red-400');
    });
}

async function sOrbiter(planeteId) {
    try {
        const response = await fetch('{{ route("navire.timonerie.s-orbiter") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ planete_id: planeteId })
        });
        const data = await response.json();

        if (data.error) {
            afficherResultat(data.error + (data.message ? '<br>' + data.message : ''), 'error');
        } else if (data.success) {
            const message = `<div class="space-y-2">
                <p class="text-green-400 font-bold text-xl">✓ ${data.message}</p>
                <p><strong>Planète:</strong> ${data.planete.nom}</p>
                <p><strong>Type:</strong> ${data.planete.type}</p>
                <p><strong>Distance:</strong> ${data.distance} UA</p>
                <p><strong>Ressources restantes:</strong></p>
                <ul class="ml-4">
                    <li>Énergie: ${data.energieRestante}</li>
                    <li>PA: ${data.paRestants}</li>
                </ul>
                <p class="mt-4 text-purple-400">Vous êtes maintenant en orbite autour de ${data.planete.nom} !</p>
                <p><a href="{{ route('navire.timonerie') }}" class="text-gray-400 underline text-sm">Recharger la timonerie</a></p>
            </div>`;

            afficherResultat(message, 'success');
        } else {
            afficherResultat(data.message || 'Mise en orbite effectuée', 'info');
        }
    } catch (error) {
        afficherResultat('Erreur: ' + error.message, 'error');
    }
}

function afficherResultat(message, type = 'info') {
    const resultatDiv = document.getElementById('resultat-navigation');
    const contenuDiv = document.getElementById('resultat-contenu');
    const styles = { info: 'border-cyan-500/30', success: 'border-green-500/30', error: 'border-red-500/30' };
    resultatDiv.className = `mt-6 bg-gray-800/50 border rounded-lg p-4 ${styles[type] || styles.info}`;
    contenuDiv.innerHTML = message;
    resultatDiv.style.display = 'block';
    resultatDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// ============================================================================
// SYSTÈME RADAR 360°
// ============================================================================

// Variables globales du radar
let radarZoom = 1; // Facteur de zoom actuel
const radarZoomLevels = [0.5, 1, 2, 5, 10, 20, 30];
let radarZoomIndex = 1; // Index dans radarZoomLevels (1 = x1 par défaut)
const radarMaxDistance = 20; // Distance max affichée en UA (vue de dessus)
const radarWidth = 1000; // Largeur du radar en pixels
const radarHeight = 500; // Hauteur du radar en pixels
const radarCenterX = 500; // Centre horizontal
const radarCenterY = 250; // Centre vertical

// Mode d'affichage actuel ('vue', 'dessus', 'distance')
let radarViewMode = 'vue';

// Azimut du vaisseau (mis à jour après rotation)
let azimutVaisseau = {{ $objetSpatial->azimut ?? 0 }};

// Données POI (combinées des deux listes)
const radarPOIs = [];

// Switcher entre les vues du radar
function switchRadarView(mode) {
    radarViewMode = mode;

    // Sauvegarder le mode de vue dans localStorage
    localStorage.setItem('radarViewMode', mode);

    // Masquer tous les SVG et légendes
    document.getElementById('radar-vue').classList.add('hidden');
    document.getElementById('radar-dessus').classList.add('hidden');
    document.getElementById('radar-distance').classList.add('hidden');
    document.getElementById('legend-vue').classList.add('hidden');
    document.getElementById('legend-dessus').classList.add('hidden');
    document.getElementById('legend-distance').classList.add('hidden');

    // Réinitialiser les boutons
    document.getElementById('tab-vue').className = 'px-4 py-2 bg-gray-700 text-gray-300 rounded transition hover:bg-gray-600';
    document.getElementById('tab-dessus').className = 'px-4 py-2 bg-gray-700 text-gray-300 rounded transition hover:bg-gray-600';
    document.getElementById('tab-distance').className = 'px-4 py-2 bg-gray-700 text-gray-300 rounded transition hover:bg-gray-600';

    // Activer la vue sélectionnée
    if (mode === 'vue') {
        document.getElementById('radar-vue').classList.remove('hidden');
        document.getElementById('legend-vue').classList.remove('hidden');
        document.getElementById('tab-vue').className = 'px-4 py-2 bg-purple-600 text-white rounded transition hover:bg-purple-500';
        drawRadarVuePerspective();
    } else if (mode === 'dessus') {
        document.getElementById('radar-dessus').classList.remove('hidden');
        document.getElementById('legend-dessus').classList.remove('hidden');
        document.getElementById('tab-dessus').className = 'px-4 py-2 bg-purple-600 text-white rounded transition hover:bg-purple-500';
        drawRadarVueDessus();
    } else if (mode === 'distance') {
        document.getElementById('radar-distance').classList.remove('hidden');
        document.getElementById('legend-distance').classList.remove('hidden');
        document.getElementById('tab-distance').className = 'px-4 py-2 bg-purple-600 text-white rounded transition hover:bg-purple-500';
        drawRadarVueDistance();
    }
}

// ============================================================================
// VUE DESSUS - Projection 2D (X,Y)
// ============================================================================

function drawRadarVueDessus() {
    const contentGroup = document.getElementById('radar-dessus-content');
    contentGroup.innerHTML = '';

    // Distance max visible (adaptée au zoom)
    const maxDist = radarMaxDistance / radarZoom;

    // Dessiner les cercles concentriques de distance (échelle max 20 UA)
    const circles = [0.1, 0.5, 1, 5, 10, 20];
    circles.forEach(dist => {
        if (dist <= maxDist) {
            const radius = (dist / maxDist) * (radarHeight / 2 - 20);

            const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            circle.setAttribute('cx', radarCenterX);
            circle.setAttribute('cy', radarCenterY);
            circle.setAttribute('r', radius);
            circle.setAttribute('fill', 'none');
            circle.setAttribute('stroke', dist === 1 ? '#00FF00' : '#444');
            circle.setAttribute('stroke-width', dist === 1 ? '2' : '1');
            circle.setAttribute('stroke-dasharray', '5,5');
            contentGroup.appendChild(circle);

            // Label de distance
            const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            label.setAttribute('x', radarCenterX);
            label.setAttribute('y', radarCenterY - radius - 5);
            label.setAttribute('fill', dist === 1 ? '#00FF00' : '#888');
            label.setAttribute('font-size', '10');
            label.setAttribute('text-anchor', 'middle');
            label.textContent = dist + ' UA';
            contentGroup.appendChild(label);
        }
    });

    // Axes de référence
    // Axe vertical (direction 0° = devant)
    const axeAvant = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    axeAvant.setAttribute('x1', radarCenterX);
    axeAvant.setAttribute('y1', radarCenterY);
    axeAvant.setAttribute('x2', radarCenterX);
    axeAvant.setAttribute('y2', '20');
    axeAvant.setAttribute('stroke', '#00FFFF');
    axeAvant.setAttribute('stroke-width', '2');
    axeAvant.setAttribute('stroke-dasharray', '10,5');
    contentGroup.appendChild(axeAvant);

    // Graduations d'angle (tous les 45°) - Afficher azimut absolu
    for (let angleRelatif = 0; angleRelatif < 360; angleRelatif += 45) {
        const rad = (angleRelatif - 90) * Math.PI / 180; // -90 pour que 0° soit en haut
        const x1 = radarCenterX + Math.cos(rad) * (radarHeight / 2 - 40);
        const y1 = radarCenterY + Math.sin(rad) * (radarHeight / 2 - 40);
        const x2 = radarCenterX + Math.cos(rad) * (radarHeight / 2 - 20);
        const y2 = radarCenterY + Math.sin(rad) * (radarHeight / 2 - 20);

        const tick = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        tick.setAttribute('x1', x1);
        tick.setAttribute('y1', y1);
        tick.setAttribute('x2', x2);
        tick.setAttribute('y2', y2);
        tick.setAttribute('stroke', '#666');
        tick.setAttribute('stroke-width', '2');
        contentGroup.appendChild(tick);

        // Calculer l'azimut absolu (dans le référentiel galaxie)
        const angleAbsolu = Math.round((angleRelatif + azimutVaisseau) % 360);

        // Label d'angle (azimut absolu)
        const labelAngle = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        labelAngle.setAttribute('x', radarCenterX + Math.cos(rad) * (radarHeight / 2 - 10));
        labelAngle.setAttribute('y', radarCenterY + Math.sin(rad) * (radarHeight / 2 - 10));
        labelAngle.setAttribute('fill', angleRelatif === 0 ? '#00FF00' : '#888');
        labelAngle.setAttribute('font-size', '10');
        labelAngle.setAttribute('font-weight', angleRelatif === 0 ? 'bold' : 'normal');
        labelAngle.setAttribute('text-anchor', 'middle');
        labelAngle.textContent = angleAbsolu + '°';
        contentGroup.appendChild(labelAngle);
    }

    // Dessiner les POI
    radarPOIs.forEach(poi => {
        // CORRECTION: Systèmes stellaires (sauts) toujours affichés sur les bords
        // POI locaux filtrés par distance
        if (!poi.saut && poi.distance > maxDist) return; // Filtrer seulement les POI locaux hors portée

        let x, y, size;

        // Normaliser azimut de 0-360° vers -180° à +180°
        let azimutNorm = poi.azimut_relatif;
        if (azimutNorm > 180) azimutNorm -= 360;

        if (poi.saut) {
            // SYSTÈMES STELLAIRES: Toujours sur le bord extérieur (périphérie)
            const azimutRad = (poi.azimut_relatif - 90) * Math.PI / 180; // -90 pour que 0° soit en haut
            const radiusBord = radarHeight / 2 - 30; // Rayon du bord (proche du cercle extérieur)
            x = radarCenterX + Math.cos(azimutRad) * radiusBord;
            y = radarCenterY + Math.sin(azimutRad) * radiusBord;

            // Taille selon distance (proche = plus gros)
            const tailleBase = 10;
            const facteurDistance = 50 / (poi.distance + 5); // Plus c'est proche, plus c'est gros
            size = Math.max(6, Math.min(16, tailleBase * facteurDistance));
        } else {
            // POI LOCAUX: Position selon distance réelle
            const azimutRad = (poi.azimut_relatif - 90) * Math.PI / 180; // -90 pour que 0° soit en haut
            const distPixels = (poi.distance / maxDist) * (radarHeight / 2 - 20);
            x = radarCenterX + Math.cos(azimutRad) * distPixels;
            y = radarCenterY + Math.sin(azimutRad) * distPixels;

            // Taille du point selon la distance (proche = gros)
            const baseSize = 8;
            const distanceFactor = maxDist / (poi.distance + 1);
            size = Math.max(4, Math.min(20, baseSize * distanceFactor));
        }

        // Couleur de bordure selon distance
        let strokeColor = '#FFF';
        let strokeWidth = 2;
        if (poi.saut) {
            strokeColor = '#FFD700'; // Or pour systèmes
            strokeWidth = 2;
        } else if (poi.distance < 1) {
            strokeColor = '#00FF00'; // < 1 UA
            strokeWidth = 3;
        } else if (poi.distance < 5) {
            strokeColor = '#FFFF00'; // < 5 UA
        } else if (poi.distance < 20) {
            strokeColor = '#FFA500'; // < 20 UA
        } else {
            strokeColor = '#FF4444'; // > 20 UA
        }

        // Créer le point POI
        const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        circle.setAttribute('cx', x);
        circle.setAttribute('cy', y);
        circle.setAttribute('r', size);
        circle.setAttribute('fill', poi.couleur);
        circle.setAttribute('stroke', strokeColor);
        circle.setAttribute('stroke-width', strokeWidth);
        circle.setAttribute('class', 'radar-poi-point cursor-pointer');
        circle.setAttribute('data-poi-id', poi.id);

        // Tooltip
        const title = document.createElementNS('http://www.w3.org/2000/svg', 'title');
        const action = poi.saut ? 'Saut' : 'Approcher';
        const distStr = poi.saut ? poi.distance.toFixed(1) + ' AL' : poi.distance.toFixed(2) + ' UA';
        title.textContent = `${poi.icone} ${poi.nom}\nDistance: ${distStr}\nAzimut: ${azimutNorm > 0 ? '+' : ''}${azimutNorm.toFixed(1)}°\nAction: ${action}\nCoût: ${poi.paRequis} PA, ${poi.energieRequise} énergie`;
        circle.appendChild(title);

        // Label du nom (si assez proche)
        if (size > 6) {
            const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            label.setAttribute('x', x);
            label.setAttribute('y', y - size - 3);
            label.setAttribute('fill', '#FFF');
            label.setAttribute('font-size', '9');
            label.setAttribute('text-anchor', 'middle');
            label.setAttribute('class', 'pointer-events-none');
            label.textContent = poi.nom;
            contentGroup.appendChild(label);
        }

        // Événements
        circle.addEventListener('mouseenter', () => highlightPOI(poi.id, true));
        circle.addEventListener('mouseleave', () => highlightPOI(poi.id, false));
        circle.addEventListener('dblclick', () => actionnerPOI(poi));
        circle.addEventListener('click', () => orienterVersPOI(poi));

        contentGroup.appendChild(circle);
    });

    // Indicateur du vaisseau (au centre)
    const vaisseau = document.createElementNS('http://www.w3.org/2000/svg', 'g');

    // Corps du vaisseau (triangle pointant vers le haut)
    const triangle = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
    triangle.setAttribute('points', `${radarCenterX},${radarCenterY - 15} ${radarCenterX - 10},${radarCenterY + 10} ${radarCenterX + 10},${radarCenterY + 10}`);
    triangle.setAttribute('fill', '#00FF00');
    triangle.setAttribute('stroke', '#FFF');
    triangle.setAttribute('stroke-width', '2');
    vaisseau.appendChild(triangle);

    // Label
    const vaisseauLabel = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    vaisseauLabel.setAttribute('x', radarCenterX);
    vaisseauLabel.setAttribute('y', radarCenterY + 25);
    vaisseauLabel.setAttribute('fill', '#00FF00');
    vaisseauLabel.setAttribute('font-size', '10');
    vaisseauLabel.setAttribute('text-anchor', 'middle');
    vaisseauLabel.textContent = 'VOUS';
    vaisseau.appendChild(vaisseauLabel);

    contentGroup.appendChild(vaisseau);
}

// ============================================================================
// VUE PERSPECTIVE - Répartition 80% système stellaire / 20% angle uniquement
// ============================================================================

function drawRadarVuePerspective() {
    const contentGroup = document.getElementById('radar-vue-content');
    contentGroup.innerHTML = '';

    // Répartition 80/20 (ATTENTION: dans SVG, Y augmente vers le BAS)
    const separationY = radarHeight * 0.2; // 100px = limite entre zone angle et zone système
    const systemZoneTop = separationY; // 100px
    const systemZoneBottom = radarHeight; // 500px
    const systemZoneHeight = systemZoneBottom - systemZoneTop; // 400px pour la zone système

    // Zone angle (en haut) : 0-100px - affichage par angle uniquement
    const angleZoneTop = 0;
    const angleZoneBottom = separationY; // 100px
    const angleZoneHeight = angleZoneBottom - angleZoneTop; // 100px

    // Point de fuite presque au contact de la séparation (juste en dessous)
    const vanishingPointY = systemZoneTop + 15; // 100 + 15 = 115px
    const horizonY = radarHeight - 30; // Horizon à 470px (niveau du vaisseau en BAS)

    // ========== ZONE ANGLE (20% en haut) ==========
    // Fond de la zone angle
    const angleZoneBackground = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
    angleZoneBackground.setAttribute('x', '0');
    angleZoneBackground.setAttribute('y', angleZoneTop);
    angleZoneBackground.setAttribute('width', radarWidth);
    angleZoneBackground.setAttribute('height', angleZoneHeight);
    angleZoneBackground.setAttribute('fill', '#0a0a1a');
    angleZoneBackground.setAttribute('opacity', '0.5');
    contentGroup.appendChild(angleZoneBackground);

    // Ligne de séparation entre zone angle et zone système
    const separationLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    separationLine.setAttribute('x1', '0');
    separationLine.setAttribute('y1', separationY);
    separationLine.setAttribute('x2', radarWidth);
    separationLine.setAttribute('y2', separationY);
    separationLine.setAttribute('stroke', '#FF00FF');
    separationLine.setAttribute('stroke-width', '2');
    separationLine.setAttribute('stroke-dasharray', '10,5');
    contentGroup.appendChild(separationLine);

    // Label zone angle
    const angleZoneLabel = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    angleZoneLabel.setAttribute('x', '10');
    angleZoneLabel.setAttribute('y', '20');
    angleZoneLabel.setAttribute('fill', '#FF00FF');
    angleZoneLabel.setAttribute('font-size', '11');
    angleZoneLabel.setAttribute('font-weight', 'bold');
    angleZoneLabel.textContent = 'ZONE ANGLE (détection sans distance)';
    contentGroup.appendChild(angleZoneLabel);

    // Graduations d'angle dans la zone angle (tous les 30°) - Afficher azimut absolu
    for (let angleRelatif = -180; angleRelatif <= 180; angleRelatif += 30) {
        const x = radarCenterX + (angleRelatif / 180) * (radarCenterX * 0.9);

        const tick = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        tick.setAttribute('x1', x);
        tick.setAttribute('y1', angleZoneTop + 25);
        tick.setAttribute('x2', x);
        tick.setAttribute('y2', angleZoneBottom - 5);
        tick.setAttribute('stroke', angleRelatif === 0 ? '#FF00FF' : '#444');
        tick.setAttribute('stroke-width', angleRelatif === 0 ? '2' : '1');
        tick.setAttribute('stroke-dasharray', '5,5');
        contentGroup.appendChild(tick);

        // Calculer l'azimut absolu
        const angleAbsolu = Math.round((angleRelatif + azimutVaisseau + 360) % 360);

        // Label angle (azimut absolu)
        const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        label.setAttribute('x', x);
        label.setAttribute('y', angleZoneTop + 40);
        label.setAttribute('fill', angleRelatif === 0 ? '#00FF00' : '#888');
        label.setAttribute('font-size', '9');
        label.setAttribute('font-weight', angleRelatif === 0 ? 'bold' : 'normal');
        label.setAttribute('text-anchor', 'middle');
        label.textContent = angleAbsolu + '°';
        contentGroup.appendChild(label);
    }

    // ========== ZONE SYSTÈME STELLAIRE (80% en bas) ==========
    // Label zone système
    const systemZoneLabel = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    systemZoneLabel.setAttribute('x', '10');
    systemZoneLabel.setAttribute('y', systemZoneTop + 20);
    systemZoneLabel.setAttribute('fill', '#FFD700');
    systemZoneLabel.setAttribute('font-size', '10');
    systemZoneLabel.textContent = 'SYSTÈME STELLAIRE (avec distance)';
    contentGroup.appendChild(systemZoneLabel);

    // Horizon dans la zone système
    const horizon = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    horizon.setAttribute('x1', '0');
    horizon.setAttribute('y1', horizonY);
    horizon.setAttribute('x2', radarWidth);
    horizon.setAttribute('y2', horizonY);
    horizon.setAttribute('stroke', '#00FFFF');
    horizon.setAttribute('stroke-width', '2');
    horizon.setAttribute('stroke-dasharray', '10,5');
    contentGroup.appendChild(horizon);

    // Label horizon
    const horizonLabel = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    horizonLabel.setAttribute('x', radarWidth - 80);
    horizonLabel.setAttribute('y', horizonY - 5);
    horizonLabel.setAttribute('fill', '#00FFFF');
    horizonLabel.setAttribute('font-size', '11');
    horizonLabel.setAttribute('font-weight', 'bold');
    horizonLabel.textContent = 'HORIZON';
    contentGroup.appendChild(horizonLabel);

    // Point de fuite (petit cercle)
    const vanishingPoint = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
    vanishingPoint.setAttribute('cx', radarCenterX);
    vanishingPoint.setAttribute('cy', vanishingPointY);
    vanishingPoint.setAttribute('r', '5');
    vanishingPoint.setAttribute('fill', '#FFFFFF');
    vanishingPoint.setAttribute('stroke', '#FF00FF');
    vanishingPoint.setAttribute('stroke-width', '2');
    contentGroup.appendChild(vanishingPoint);

    // Lignes de perspective dans la zone système (convergent vers le point de fuite)
    const perspectiveAngles = [-180, -135, -90, -45, 0, 45, 90, 135, 180];
    perspectiveAngles.forEach(angle => {
        const xBottom = radarCenterX + (angle / 180) * radarCenterX;

        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.setAttribute('x1', xBottom);
        line.setAttribute('y1', horizonY);
        line.setAttribute('x2', radarCenterX);
        line.setAttribute('y2', vanishingPointY);
        line.setAttribute('stroke', angle === 0 ? '#00FFFF' : '#333');
        line.setAttribute('stroke-width', angle === 0 ? '2' : '1');
        contentGroup.appendChild(line);
    });

    // Graduations d'angle dans la zone système (au niveau de l'horizon) - Afficher azimut absolu
    for (let angleRelatif = -180; angleRelatif <= 180; angleRelatif += 30) {
        const x = radarCenterX + (angleRelatif / 180) * radarCenterX;

        const tick = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        tick.setAttribute('x1', x);
        tick.setAttribute('y1', horizonY);
        tick.setAttribute('x2', x);
        tick.setAttribute('y2', horizonY + 10);
        tick.setAttribute('stroke', angleRelatif === 0 ? '#00FFFF' : '#666');
        tick.setAttribute('stroke-width', '2');
        contentGroup.appendChild(tick);

        // Calculer l'azimut absolu
        const angleAbsolu = Math.round((angleRelatif + azimutVaisseau + 360) % 360);

        // Label angle (azimut absolu)
        const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        label.setAttribute('x', x);
        label.setAttribute('y', horizonY + 25);
        label.setAttribute('fill', angleRelatif === 0 ? '#00FF00' : '#888');
        label.setAttribute('font-size', '10');
        label.setAttribute('font-weight', angleRelatif === 0 ? 'bold' : 'normal');
        label.setAttribute('text-anchor', 'middle');
        label.textContent = angleAbsolu + '°';
        contentGroup.appendChild(label);
    }

    // Lignes de distance dans la zone système (0.1, 0.5, 1, 5, 10, 15, 20 UA)
    const systemDistances = [0.1, 0.5, 1.0, 5.0, 10.0, 15.0, 20.0];
    const maxSystemDist = 20.0; // Distance max affichée

    systemDistances.forEach(dist => {
        // Position Y dans la zone système (depuis l'horizon en BAS vers le point de fuite en HAUT)
        const normalizedDist = Math.min(dist / maxSystemDist, 1); // 0 à 1
        // Plus la distance est grande, plus on monte vers le point de fuite
        const y = horizonY - (normalizedDist * (horizonY - vanishingPointY - 20));

        // Ligne horizontale pour marquer la distance
        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.setAttribute('x1', '0');
        line.setAttribute('y1', y);
        line.setAttribute('x2', radarWidth);
        line.setAttribute('y2', y);
        line.setAttribute('stroke', dist === 1.0 ? '#00FF00' : '#444');
        line.setAttribute('stroke-width', dist === 1.0 ? '2' : '1');
        line.setAttribute('stroke-dasharray', '5,5');
        contentGroup.appendChild(line);

        // Label distance
        const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        label.setAttribute('x', '10');
        label.setAttribute('y', y - 3);
        label.setAttribute('fill', dist === 1.0 ? '#00FF00' : '#888');
        label.setAttribute('font-size', '9');
        label.textContent = dist + ' UA';
        contentGroup.appendChild(label);
    });

    // Dessiner les POI
    radarPOIs.forEach(poi => {
        let x, y, size;

        // Normaliser azimut de 0-360° vers -180° à +180°
        let azimutNorm = poi.azimut_relatif;
        if (azimutNorm > 180) azimutNorm -= 360;

        if (poi.saut) {
            // ========== SYSTÈMES STELLAIRES : Dans la ZONE ANGLE (20% en haut) ==========
            // Position X selon l'azimut uniquement (sans perspective)
            x = radarCenterX + (azimutNorm / 180) * (radarCenterX * 0.9);

            // Position Y centrée dans la zone angle (sans tenir compte de la distance)
            y = angleZoneTop + (angleZoneHeight / 2) + 20; // Centré à ~70px

            // Taille = detectabilite * distance / 40 (divisé par 4 comme demandé)
            // Detectabilité par défaut pour les systèmes : 100
            const detectabilite = poi.detectabilite || 100;
            const taileBrute = (detectabilite * poi.distance) / 40;

            // DEBUG: Afficher les tailles calculées
            console.log(`Système ${poi.nom}: detectabilite=${detectabilite}, distance=${poi.distance.toFixed(2)} AL, taille brute=${taileBrute.toFixed(2)}px`);

            size = Math.max(8, taileBrute);
            // Limiter la taille max pour ne pas déborder
            size = Math.min(size, 30);
        } else {
            // ========== POI LOCAUX : Dans la ZONE SYSTÈME (80% en bas) ==========
            const distUA = poi.distance;

            // Position X selon l'azimut
            const xBase = radarCenterX + (azimutNorm / 180) * radarCenterX;

            if (distUA > maxSystemDist) {
                // Au-delà de la distance max, placer près du point de fuite (en haut de la zone)
                y = vanishingPointY + 20;
                size = 4;
                x = xBase;
            } else {
                // Position Y selon distance dans la zone système
                // Distance 0 = horizon (en bas, y=470)
                // Distance max = près du point de fuite (en haut, y=180)
                const normalizedDist = distUA / maxSystemDist; // 0 à 1
                y = horizonY - (normalizedDist * (horizonY - vanishingPointY - 20));

                // Taille inversement proportionnelle à la distance
                const distanceFactor = 1 / (distUA + 0.1);
                size = Math.max(4, Math.min(16, 8 * distanceFactor));

                // Position X avec perspective (convergence vers centre en s'éloignant)
                const perspectiveRatio = (horizonY - y) / (horizonY - vanishingPointY - 20);
                const convergence = 1 - (perspectiveRatio * 0.7); // Converge de 70% max vers le point de fuite
                x = radarCenterX + ((xBase - radarCenterX) * convergence);
            }
        }

        // Couleur de bordure selon distance
        let strokeColor = '#FFF';
        let strokeWidth = 2;
        if (poi.saut) {
            strokeColor = '#FFD700'; // Or pour systèmes
            strokeWidth = 2;
        } else if (poi.distance < 1) {
            strokeColor = '#00FF00'; // < 1 UA
            strokeWidth = 3;
        } else if (poi.distance < 5) {
            strokeColor = '#FFFF00'; // < 5 UA
        } else if (poi.distance < 20) {
            strokeColor = '#FFA500'; // < 20 UA
        } else {
            strokeColor = '#FF4444'; // > 20 UA
        }

        // Créer le point POI
        const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        circle.setAttribute('cx', x);
        circle.setAttribute('cy', y);
        circle.setAttribute('r', size);
        circle.setAttribute('fill', poi.couleur);
        circle.setAttribute('stroke', strokeColor);
        circle.setAttribute('stroke-width', strokeWidth);
        circle.setAttribute('class', 'radar-poi-point cursor-pointer');
        circle.setAttribute('data-poi-id', poi.id);

        // Tooltip
        const title = document.createElementNS('http://www.w3.org/2000/svg', 'title');
        const action = poi.saut ? 'Saut' : 'Approcher';
        const distStr = poi.saut ? poi.distance.toFixed(1) + ' AL' : poi.distance.toFixed(2) + ' UA';
        title.textContent = `${poi.icone} ${poi.nom}\nDistance: ${distStr}\nAzimut: ${azimutNorm > 0 ? '+' : ''}${azimutNorm.toFixed(1)}°\nAction: ${action}\nCoût: ${poi.paRequis} PA, ${poi.energieRequise} énergie`;
        circle.appendChild(title);

        // Label du nom (si assez gros)
        if (size > 5) {
            const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            label.setAttribute('x', x);
            label.setAttribute('y', y - size - 3);
            label.setAttribute('fill', '#FFF');
            label.setAttribute('font-size', '9');
            label.setAttribute('text-anchor', 'middle');
            label.setAttribute('class', 'pointer-events-none');
            label.textContent = poi.nom;
            contentGroup.appendChild(label);
        }

        // Événements
        circle.addEventListener('mouseenter', () => highlightPOI(poi.id, true));
        circle.addEventListener('mouseleave', () => highlightPOI(poi.id, false));
        circle.addEventListener('dblclick', () => actionnerPOI(poi));
        circle.addEventListener('click', () => orienterVersPOI(poi));

        contentGroup.appendChild(circle);
    });

    // Indicateur du vaisseau (au centre de l'horizon)
    const vaisseau = document.createElementNS('http://www.w3.org/2000/svg', 'g');

    // Corps du vaisseau (triangle pointant vers le haut)
    const triangle = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
    triangle.setAttribute('points', `${radarCenterX},${horizonY - 20} ${radarCenterX - 12},${horizonY + 5} ${radarCenterX + 12},${horizonY + 5}`);
    triangle.setAttribute('fill', '#00FF00');
    triangle.setAttribute('stroke', '#FFF');
    triangle.setAttribute('stroke-width', '2');
    vaisseau.appendChild(triangle);

    // Label
    const vaisseauLabel = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    vaisseauLabel.setAttribute('x', radarCenterX);
    vaisseauLabel.setAttribute('y', horizonY + 20);
    vaisseauLabel.setAttribute('fill', '#00FF00');
    vaisseauLabel.setAttribute('font-size', '10');
    vaisseauLabel.setAttribute('font-weight', 'bold');
    vaisseauLabel.setAttribute('text-anchor', 'middle');
    vaisseauLabel.textContent = 'VOUS';
    vaisseau.appendChild(vaisseauLabel);

    contentGroup.appendChild(vaisseau);
}

// ============================================================================
// VUE DISTANCE - Distance vs Angle (stub pour l'instant)
// ============================================================================

function drawRadarVueDistance() {
    const contentGroup = document.getElementById('radar-distance-content');
    contentGroup.innerHTML = '';

    // TODO: Implémenter la vue Distance
    // X = 70% zone locale (0-20 UA), 30% systèmes (échelle log)
    // Y = Angle (-180° à +180°)

    const placeholder = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    placeholder.setAttribute('x', radarCenterX);
    placeholder.setAttribute('y', radarCenterY);
    placeholder.setAttribute('fill', '#888');
    placeholder.setAttribute('font-size', '20');
    placeholder.setAttribute('text-anchor', 'middle');
    placeholder.textContent = 'Vue Distance - En développement';
    contentGroup.appendChild(placeholder);
}

// Initialiser le radar au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Collecter tous les POI depuis les deux listes
    collecterPOIs();

    // Charger le zoom depuis localStorage
    const savedZoomIndex = localStorage.getItem('radarZoomIndex');
    if (savedZoomIndex !== null) {
        radarZoomIndex = parseInt(savedZoomIndex);
        radarZoom = radarZoomLevels[radarZoomIndex];
        document.getElementById('radar-zoom-label').textContent = 'x' + radarZoom;
    }

    // Charger le mode de vue depuis localStorage (par défaut 'vue')
    const savedViewMode = localStorage.getItem('radarViewMode') || 'vue';
    switchRadarView(savedViewMode);
});

// Collecter les POI depuis les listes sauts et déplacements
function collecterPOIs() {
    radarPOIs.length = 0; // Vider le tableau

    // POI de déplacement (secteur local)
    @foreach($poisSecteur as $poi)
        radarPOIs.push({
            id: {{ $poi->id }},
            nom: @json($poi->nom),
            type: @json($poi->type_poi),
            icone: @json($poi->icone),
            couleur: @json($poi->couleur ?? '#FFFFFF'),
            distance: {{ $poi->distance }},
            azimut_absolu: {{ $poi->azimut_absolu ?? 0 }},
            azimut_relatif: {{ $poi->azimut_relatif ?? 0 }},
            elevation: {{ $poi->elevation ?? 0 }},
            energieRequise: {{ $poi->energieRequise }},
            paRequis: {{ $poi->paRequis }},
        });
    @endforeach

    // POI de saut (autres secteurs) - affichés en périphérie
    @foreach($sautsDisponibles as $systeme)
        radarPOIs.push({
            id: 'saut-' + {{ $systeme->id }},
            nom: @json($systeme->nom),
            type: 'systeme',
            icone: '⭐',
            couleur: '#FDB813',
            distance: {{ $systeme->distance }},
            azimut_absolu: {{ $systeme->azimut_absolu ?? 0 }},
            azimut_relatif: {{ $systeme->azimut_relatif ?? 0 }},
            elevation: {{ $systeme->elevation ?? 0 }},
            energieRequise: {{ $systeme->energieRequise }},
            paRequis: {{ $systeme->paRequis }},
            saut: true, // Indique que c'est un saut hyperespace
            detectabilite: {{ $systeme->detectabilite ?? 100 }}, // Détectabilité par défaut = 100 pour systèmes
        });
    @endforeach

    // DEBUG: Afficher les 3 premiers POI
    console.log('=== POI COLLECTÉS ===');
    console.log('Total POI:', radarPOIs.length);
    console.log('Azimut vaisseau actuel:', {{ $objetSpatial->azimut ?? 0 }});
    radarPOIs.slice(0, 3).forEach(poi => {
        console.log(`POI "${poi.nom}":`, {
            azimut_absolu: poi.azimut_absolu,
            azimut_relatif: poi.azimut_relatif,
            distance: poi.distance,
            saut: poi.saut || false
        });
    });
}

// Surligner un POI (sur le radar et dans les listes)
function highlightPOI(poiId, highlight) {
    // Surligner le point sur le radar
    const circle = document.querySelector(`[data-poi-id="${poiId}"]`);
    if (circle) {
        circle.setAttribute('stroke-width', highlight ? '3' : '1');
        circle.setAttribute('stroke', highlight ? '#FFFF00' : '#FFF');
    }

    // Surligner le cartouche dans les listes
    const cartouche = document.querySelector(`[data-cartouche-poi-id="${poiId}"]`);
    if (cartouche) {
        if (highlight) {
            cartouche.classList.add('ring', 'ring-2', 'ring-yellow-400');
        } else {
            cartouche.classList.remove('ring', 'ring-2', 'ring-yellow-400');
        }
    }
}

// Orienter le vaisseau vers un POI (clic sur le radar)
function orienterVersPOI(poi) {
    // Calculer la rotation nécessaire pour mettre le POI en face (azimut relatif 0°)
    const azimutRelatif = poi.azimut_relatif;

    // Afficher un message
    afficherResultat(`Orientation vers ${poi.nom} (${azimutRelatif.toFixed(1)}°)...`, 'info');

    // TODO: Envoyer une requête pour tourner le vaisseau de l'angle nécessaire
    // Pour l'instant, juste un message
    console.log(`Orienter vers ${poi.nom}: rotation de ${azimutRelatif}°`);
}

// Actionner un POI (double-clic sur le radar)
async function actionnerPOI(poi) {
    if (poi.saut) {
        // Lancer le saut hyperespace
        const systemeId = poi.id.replace('saut-', '');
        await effectuerSaut(systemeId);
    } else {
        // Lancer l'approche
        await sApprocher(poi.id);
    }
}

// Mettre à jour les affichages d'azimut dans les cartouches
function mettreAJourCartouchesAzimut(nouvelAzimutVaisseau) {
    // Parcourir tous les cartouches POI
    document.querySelectorAll('[data-poi-azimut-absolu]').forEach(cartouche => {
        const azimutAbsolu = parseFloat(cartouche.dataset.poiAzimutAbsolu);

        // Calculer le nouvel azimut relatif
        let azimutRelatif = (azimutAbsolu - nouvelAzimutVaisseau + 360) % 360;

        // Normaliser en -180 à +180
        if (azimutRelatif > 180) azimutRelatif -= 360;

        // Mettre à jour l'affichage
        const azimutDisplay = cartouche.querySelector('.poi-azimut-display');
        if (azimutDisplay) {
            azimutDisplay.textContent = `Az: ${azimutRelatif.toFixed(1)}°`;
        }
    });
}

// Recalculer les azimuts relatifs de tous les POI après rotation
function recalculerAzimutsRelatifs(nouvelAzimutVaisseau) {
    console.log('=== RECALCUL AZIMUTS ===');
    console.log('Nouvel azimut vaisseau:', nouvelAzimutVaisseau);
    console.log('Nombre de POI:', radarPOIs.length);

    let debugCount = 0;
    radarPOIs.forEach(poi => {
        // L'azimut absolu ne change pas, seul l'azimut relatif change
        // azimutRelatif = azimutAbsolu - azimutVaisseau
        const azimutAbsoluOriginal = poi.azimut_absolu;
        const azimutRelatifOriginal = poi.azimut_relatif;

        if (!azimutAbsoluOriginal && azimutAbsoluOriginal !== 0) {
            console.warn(`POI ${poi.nom} n'a pas d'azimut absolu!`, poi);
            return;
        }

        let nouvelAzimutRelatif = (azimutAbsoluOriginal - nouvelAzimutVaisseau + 360) % 360;

        // Normaliser en -180 à +180 si nécessaire
        if (nouvelAzimutRelatif > 180) nouvelAzimutRelatif -= 360;

        poi.azimut_relatif = nouvelAzimutRelatif;

        // Debug pour les 3 premiers POI
        if (debugCount < 3) {
            console.log(`POI "${poi.nom}":`, {
                azimutAbsolu: azimutAbsoluOriginal,
                azimutRelatifAvant: azimutRelatifOriginal.toFixed(1),
                azimutRelatifApres: nouvelAzimutRelatif.toFixed(1),
                delta: (nouvelAzimutRelatif - azimutRelatifOriginal).toFixed(1)
            });
            debugCount++;
        }
    });

    // Mettre à jour les cartouches POI
    mettreAJourCartouchesAzimut(nouvelAzimutVaisseau);

    // Redessiner la vue active avec les nouveaux azimuts
    console.log('Azimuts relatifs recalculés, redessin du radar...');
    if (radarViewMode === 'vue') {
        drawRadarVuePerspective();
    } else if (radarViewMode === 'dessus') {
        drawRadarVueDessus();
    } else if (radarViewMode === 'distance') {
        drawRadarVueDistance();
    }
}

// Tourner le vaisseau
async function tournerVaisseau(direction) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        if (!csrfToken) {
            afficherResultat('Erreur: Token CSRF manquant', 'error');
            console.error('Token CSRF non trouvé dans la page');
            return;
        }

        const response = await fetch('{{ route("navire.timonerie.tourner") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                direction: direction,
                angle: 15
            })
        });

        // Vérifier si la réponse est OK
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Erreur serveur:', response.status, errorText);
            afficherResultat(`Erreur serveur (${response.status}): ${errorText.substring(0, 100)}`, 'error');
            return;
        }

        const data = await response.json();
        console.log('Réponse rotation:', data);

        if (data.success) {
            // Mettre à jour l'azimut du vaisseau (variable globale)
            azimutVaisseau = data.nouvelAzimut;

            // Mettre à jour l'affichage de l'azimut dans le header
            document.getElementById('radar-azimut').textContent = data.nouvelAzimut.toFixed(1) + '°';

            // Recalculer les azimuts relatifs côté client (sans reload)
            recalculerAzimutsRelatifs(data.nouvelAzimut);

            // Afficher un message de succès temporaire
            afficherResultat(data.message + ' - Radar mis à jour', 'success');

            // Masquer le message après 2 secondes
            setTimeout(() => {
                document.getElementById('resultat-navigation').style.display = 'none';
            }, 2000);
        } else {
            afficherResultat(data.error || 'Erreur lors de la rotation', 'error');
        }
    } catch (error) {
        console.error('Erreur catch:', error);
        afficherResultat('Erreur lors de la rotation: ' + error.message, 'error');
    }
}

// Gérer le zoom du radar
function zoomRadar(action) {
    if (action === 'in' && radarZoomIndex < radarZoomLevels.length - 1) {
        radarZoomIndex++;
    } else if (action === 'out' && radarZoomIndex > 0) {
        radarZoomIndex--;
    } else if (action === 'reset') {
        radarZoomIndex = 1; // x1
    }

    radarZoom = radarZoomLevels[radarZoomIndex];
    document.getElementById('radar-zoom-label').textContent = 'x' + radarZoom;

    // Sauvegarder dans localStorage
    localStorage.setItem('radarZoomIndex', radarZoomIndex);

    // Redessiner la vue active
    if (radarViewMode === 'vue') {
        drawRadarVuePerspective();
    } else if (radarViewMode === 'dessus') {
        drawRadarVueDessus();
    } else if (radarViewMode === 'distance') {
        drawRadarVueDistance();
    }
}

// Ajouter événements survol aux cartouches POI dans les listes
document.addEventListener('DOMContentLoaded', function() {
    // Attendre un peu que les listes soient chargées
    setTimeout(() => {
        document.querySelectorAll('[data-cartouche-poi-id]').forEach(cartouche => {
            const poiId = cartouche.getAttribute('data-cartouche-poi-id');

            cartouche.addEventListener('mouseenter', () => highlightPOI(poiId, true));
            cartouche.addEventListener('mouseleave', () => highlightPOI(poiId, false));
        });
    }, 500);
});
</script>
@endsection
