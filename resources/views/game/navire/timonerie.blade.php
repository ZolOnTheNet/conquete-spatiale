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
            <div class="max-w-7xl mx-auto">

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
                            <span class="text-cyan-400 font-mono">
                                ({{ $objetSpatial->position_x }}, {{ $objetSpatial->position_y }}, {{ $objetSpatial->position_z }}) AL
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

                <div class="grid grid-cols-2 gap-6">

                    {{-- Colonne gauche : Sauts hyperspatiaux --}}
                    <div class="bg-gray-800/50 border border-yellow-500/30 rounded-lg p-4">
                        <h3 class="text-xl text-yellow-400 font-bold mb-4 flex items-center gap-2">
                            <span>⚡</span>
                            <span>Sauts Hyperspatiaux</span>
                        </h3>

                        <div class="space-y-2 max-h-[600px] overflow-y-auto">
                            @forelse($sautsDisponibles as $destination)
                            <div class="bg-gray-900/50 border border-gray-700 rounded px-3 py-2 hover:border-yellow-500/50 transition flex items-center gap-3 text-sm">
                                <div class="flex-1 min-w-0">
                                    <span class="text-white font-semibold">{{ $destination->nom }}</span>
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
                                 @if($poi->type_poi === 'planete' && isset($poi->donneesOrbitales))
                                     data-planete-id="{{ $poi->id }}"
                                     data-planete-orbital='@json($poi->donneesOrbitales)'
                                 @endif>
                                <div class="flex-1 min-w-0">
                                    <span class="text-white font-semibold">{{ $poi->icone }} {{ $poi->nom }}</span>
                                    @if($poi->type_poi === 'planete')
                                        <span class="text-gray-500 text-xs ml-2" title="Distance calculée avec système orbital">🪐</span>
                                    @endif
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
                                <button onclick="sApprocher({{ $poi->id }}, '{{ $poi->type_poi }}')"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs transition whitespace-nowrap">
                                    ➡️ Approcher ({{ $poi->paRequis }} PA)
                                </button>
                                @if($poi->distance < 1)
                                <button onclick="sAmarrer({{ $poi->id }})"
                                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition whitespace-nowrap">
                                    🔗 Amarrer
                                </button>
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
@if($systemeActuel)
const systemeData = {
    id: {{ $systemeActuel->id }},
    nom: "{{ $systemeActuel->nom }}",
    position_x: {{ $systemeActuel->position_x }},
    position_y: {{ $systemeActuel->position_y }},
    position_z: {{ $systemeActuel->position_z }}
};
@else
const systemeData = null;
@endif

const vaisseauData = {
    position_x: {{ $objetSpatial->position_x }},
    position_y: {{ $objetSpatial->position_y }},
    position_z: {{ $objetSpatial->position_z }},
};

const dateJeu = new Date('{{ $dateJeuActuelle->toIso8601String() }}');

// Fonction pour mettre à jour les distances des planètes en temps réel
function mettreAJourDistancesPlanetes() {
    if (!systemeData) return; // Espace profond

    const timestampJours = OrbitalCalculator.dateToJours(dateJeu);

    // Pour chaque planète dans la liste
    document.querySelectorAll('[data-planete-id]').forEach(element => {
        const planeteId = element.dataset.planeteId;
        const planeteData = JSON.parse(element.dataset.planeteOrbital || '{}');

        if (!planeteData.distance_etoile) return; // Pas de données orbitales

        try {
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
            console.log(`Planète ${planeteData.id}: ${distance.toFixed(2)} UA (calculé client-side)`);
        } catch (error) {
            console.error(`Erreur calcul orbital planète ${planeteId}:`, error);
        }
    });
}

// Initialiser les calculs au chargement
if (typeof OrbitalCalculator !== 'undefined') {
    console.log('✓ OrbitalCalculator chargé, calculs orbitaux disponibles');
    // Optionnel: mettre à jour les distances au chargement
    // mettreAJourDistancesPlanetes();
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
</script>
@endsection
