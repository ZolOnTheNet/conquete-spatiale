@extends('layouts.app')

@section('title', 'Timonerie')

@section('content')
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
        <x-main-menu
            context="navire"
            :personnage="$personnage"
            :isAdmin="false"
        />

        {{-- Zone de contenu principale --}}
        <main class="flex-1 overflow-auto p-6">
            <div class="max-w-7xl mx-auto">

                <h2 class="text-3xl font-orbitron text-cyan-400 mb-6">🚀 TIMONERIE</h2>

                {{-- Position actuelle --}}
                <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-4 mb-6">
                    <h3 class="text-lg text-cyan-300 mb-3">Position actuelle</h3>
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-400">Secteur:</span>
                            <span class="text-yellow-400 font-mono ml-2">
                                ({{ $objetSpatial->secteur_x }}, {{ $objetSpatial->secteur_y }}, {{ $objetSpatial->secteur_z }})
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400">Position:</span>
                            <span class="text-cyan-400 font-mono ml-2">
                                ({{ $objetSpatial->position_x }}, {{ $objetSpatial->position_y }}, {{ $objetSpatial->position_z }}) AL
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400">Système:</span>
                            <span class="text-green-400 ml-2">{{ $systemeActuel->nom ?? 'Espace profond' }}</span>
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

                        <div class="space-y-3 max-h-[600px] overflow-y-auto">
                            @forelse($sautsDisponibles as $destination)
                            <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4 hover:border-yellow-500/50 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="text-white font-bold text-lg">{{ $destination->nom }}</h4>
                                        <p class="text-gray-400 text-sm">
                                            Distance: <span class="text-yellow-400">{{ number_format($destination->distance, 2) }} AL</span>
                                        </p>
                                    </div>
                                    @if($destination->accessible)
                                        <span class="px-2 py-1 bg-green-900/30 text-green-400 text-xs rounded">✓ Accessible</span>
                                    @else
                                        <span class="px-2 py-1 bg-red-900/30 text-red-400 text-xs rounded">✗ Inaccessible</span>
                                    @endif
                                </div>

                                <div class="text-sm text-gray-400 mb-3">
                                    <span>Énergie: <span class="text-yellow-400">{{ $destination->energieRequise }}</span></span>
                                    <span class="ml-4">PA: <span class="text-cyan-400">{{ $destination->paRequis }}</span></span>
                                </div>

                                <div class="flex gap-2">
                                    <button onclick="calculerSaut({{ $destination->id }})"
                                            class="flex-1 bg-cyan-600 hover:bg-cyan-700 disabled:bg-gray-600 disabled:cursor-not-allowed text-white px-3 py-2 rounded text-sm transition"
                                            @if(!$destination->accessible) disabled @endif>
                                        🧮 Calcul
                                    </button>
                                    <button onclick="effectuerSaut({{ $destination->id }})"
                                            class="flex-1 bg-yellow-600 hover:bg-yellow-700 disabled:bg-gray-600 disabled:cursor-not-allowed text-white px-3 py-2 rounded text-sm font-bold transition"
                                            @if(!$destination->accessible) disabled @endif>
                                        ⚡ Saut
                                    </button>
                                </div>
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

                        <div class="space-y-3 max-h-[600px] overflow-y-auto">
                            @forelse($poisSecteur as $poi)
                            <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4 hover:border-cyan-500/50 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="text-white font-bold flex items-center gap-2">
                                            <span>{{ $poi->icone }}</span>
                                            <span>{{ $poi->nom }}</span>
                                        </h4>
                                        <p class="text-gray-400 text-sm">
                                            Distance: <span class="text-cyan-400">{{ number_format($poi->distance, 2) }} UA</span>
                                        </p>
                                    </div>
                                    @if($poi->distance < 1)
                                        <span class="px-2 py-1 bg-green-900/30 text-green-400 text-xs rounded">★ Amarrage possible</span>
                                    @endif
                                </div>

                                <div class="text-sm text-gray-400 mb-3">
                                    <span>Énergie: <span class="text-yellow-400">{{ $poi->energieRequise }}</span></span>
                                    <span class="ml-4">PA: <span class="text-cyan-400">{{ $poi->paRequis }}</span></span>
                                </div>

                                <div class="flex gap-2">
                                    <button onclick="sApprocher({{ $poi->id }}, 'systeme')"
                                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm transition">
                                        ➡️ S'approcher
                                    </button>
                                    @if($poi->distance < 1)
                                    <button onclick="sAmarrer({{ $poi->id }})"
                                            class="flex-1 bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm font-bold transition">
                                        🔗 S'amarrer
                                    </button>
                                    @endif
                                </div>
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

    </div>

</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

async function calculerSaut(destinationId) {
    try {
        const response = await fetch('{{ route("navire.timonerie.calculer-saut") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ destination_id: destinationId })
        });
        const data = await response.json();
        if (data.error) {
            afficherResultat(data.error, 'error');
        } else {
            const message = `<div class="space-y-2"><p><strong>Destination :</strong> ${data.destination}</p><p><strong>Distance :</strong> ${data.distance} AL</p><p><strong>Énergie requise :</strong> ${data.energieRequise} (disponible: ${data.energieDisponible})</p><p><strong>PA requis :</strong> ${data.paRequis} (disponibles: ${data.paDisponibles})</p><p class="${data.accessible ? 'text-green-400' : 'text-red-400'}"><strong>${data.accessible ? '✓ Saut possible' : '✗ Ressources insuffisantes'}</strong></p></div>`;
            afficherResultat(message, 'info');
        }
    } catch (error) {
        afficherResultat('Erreur de calcul: ' + error.message, 'error');
    }
}

async function effectuerSaut(destinationId) {
    if (!confirm('Effectuer le saut hyperespace maintenant ?')) return;
    try {
        const response = await fetch('{{ route("navire.timonerie.effectuer-saut") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ destination_id: destinationId })
        });
        const data = await response.json();
        if (data.error) {
            afficherResultat(data.error, 'error');
        } else if (data.success) {
            const precisionPercent = Math.round(data.arrivee.precision * 100);
            const message = `<div class="space-y-2"><p class="text-green-400 font-bold">✓ ${data.message}</p><p><strong>Jet de navigation :</strong> ${data.jetNavigation}</p><p><strong>Position d'arrivée :</strong></p><ul class="ml-4"><li>Secteur: (${data.arrivee.secteur_x}, ${data.arrivee.secteur_y}, ${data.arrivee.secteur_z})</li><li>Position: (${data.arrivee.position_x}, ${data.arrivee.position_y}, ${data.arrivee.position_z}) AL</li><li>Précision: ${precisionPercent}% d'écart (max ${data.arrivee.ecart_max_ua} UA)</li></ul><p><strong>Ressources restantes :</strong></p><ul class="ml-4"><li>Énergie: ${data.energieRestante}</li><li>PA: ${data.paRestants}</li></ul><p class="mt-4"><a href="{{ route('navire.timonerie') }}" class="text-cyan-400 underline">Recharger la timonerie</a></p></div>`;
            afficherResultat(message, 'success');
        }
    } catch (error) {
        afficherResultat('Erreur lors du saut: ' + error.message, 'error');
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
            afficherResultat(data.error, 'error');
        } else if (data.success) {
            let message = `<div class="space-y-2"><p class="text-green-400 font-bold">✓ ${data.message}</p>`;

            if (data.pourcentageTrajet) {
                message += `<p class="text-yellow-400">⚠️ Déplacement partiel: ${data.pourcentageTrajet}% du trajet</p>`;
            }

            message += `<p><strong>Distance parcourue:</strong> ${data.distanceParcourue} UA</p>`;
            message += `<p><strong>Distance restante:</strong> ${data.distanceRestante} UA</p>`;
            message += `<p><strong>Nouvelle position:</strong> (${data.nouvellePosition.x}, ${data.nouvellePosition.y}, ${data.nouvellePosition.z}) AL</p>`;
            message += `<p><strong>Ressources consommées:</strong></p>`;
            message += `<ul class="ml-4"><li>Énergie: -${data.energieConsommee} (restante: ${data.energieRestante})</li>`;
            message += `<li>PA: -${data.paConsommes} (restants: ${data.paRestants})</li></ul>`;
            message += `<p class="mt-4"><a href="{{ route('navire.timonerie') }}" class="text-cyan-400 underline">Recharger la timonerie</a></p></div>`;

            afficherResultat(message, 'success');
        } else {
            afficherResultat(data.message || 'Déplacement effectué', 'info');
        }
    } catch (error) {
        afficherResultat('Erreur: ' + error.message, 'error');
    }
}

async function sAmarrer(stationId) {
    if (!confirm('S\'amarrer à cette station ?')) return;
    try {
        const response = await fetch('{{ route("navire.timonerie.s-amarrer") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ station_id: stationId })
        });
        const data = await response.json();

        if (data.error) {
            afficherResultat(data.error + (data.message ? '<br>' + data.message : ''), 'error');
        } else if (data.success) {
            const message = `<div class="space-y-2">
                <p class="text-green-400 font-bold text-xl">✓ ${data.message}</p>
                <p><strong>Station:</strong> ${data.station.nom}</p>
                <p><strong>Distance:</strong> ${data.distance} UA</p>
                <p><strong>Ressources restantes:</strong></p>
                <ul class="ml-4">
                    <li>Énergie: ${data.energieRestante}</li>
                    <li>PA: ${data.paRestants}</li>
                </ul>
                <p class="mt-4 text-cyan-400">Vous avez maintenant accès au menu Station !</p>
                <p class="mt-2"><a href="{{ route('station.hall') }}" class="text-cyan-400 underline font-bold">→ Accéder au Hall de la Station</a></p>
                <p><a href="{{ route('navire.timonerie') }}" class="text-gray-400 underline text-sm">Recharger la timonerie</a></p>
            </div>`;

            afficherResultat(message, 'success');
        } else {
            afficherResultat(data.message || 'Amarrage effectué', 'info');
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
</script>
@endsection
