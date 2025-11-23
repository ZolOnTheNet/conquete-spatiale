<div class="p-6">
    <h2 class="text-2xl font-orbitron text-cyan-400 mb-6">POSITION DU VAISSEAU</h2>

    @if($vaisseau && $objetSpatial)
    <div class="space-y-6">
        <!-- Informations du vaisseau -->
        <div class="panel p-4">
            <h3 class="text-lg text-cyan-300 mb-3">Vaisseau</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-400">Nom:</span>
                    <span class="text-white font-bold">{{ $vaisseau->nom ?? 'Sans nom' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Type:</span>
                    <span class="text-white">{{ $vaisseau->modele ?? 'Inconnu' }}</span>
                </div>
            </div>
        </div>

        <!-- Position actuelle -->
        <div class="panel p-4">
            <h3 class="text-lg text-cyan-300 mb-3">Position actuelle</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-400">Secteur:</span>
                    <span class="text-yellow-400 font-mono">
                        ({{ $objetSpatial->secteur_x }}, {{ $objetSpatial->secteur_y }}, {{ $objetSpatial->secteur_z }})
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Position dans secteur:</span>
                    <span class="text-yellow-400 font-mono">
                        ({{ number_format($objetSpatial->position_x, 2) }}, 
                         {{ number_format($objetSpatial->position_y, 2) }}, 
                         {{ number_format($objetSpatial->position_z, 2) }}) AL
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Coordonnées absolues:</span>
                    <span class="text-green-400 font-mono">
                        ({{ number_format($objetSpatial->secteur_x * 10 + $objetSpatial->position_x, 2) }}, 
                         {{ number_format($objetSpatial->secteur_y * 10 + $objetSpatial->position_y, 2) }}, 
                         {{ number_format($objetSpatial->secteur_z * 10 + $objetSpatial->position_z, 2) }}) AL
                    </span>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="panel p-4">
            <h3 class="text-lg text-cyan-300 mb-3">Actions rapides</h3>
            <div class="space-y-2">
                <button onclick="loadView('carte', 'Carte')" 
                        class="w-full btn-primary text-sm py-2">
                    📍 Voir sur la carte
                </button>
                <button onclick="loadView('vaisseau.scanner', 'Scanner')" 
                        class="w-full btn-secondary text-sm py-2">
                    🔍 Scanner les environs
                </button>
            </div>
        </div>
    </div>
    @else
    <div class="panel p-4 text-center text-gray-500">
        <p>Aucun vaisseau actif ou position inconnue</p>
    </div>
    @endif
</div>
