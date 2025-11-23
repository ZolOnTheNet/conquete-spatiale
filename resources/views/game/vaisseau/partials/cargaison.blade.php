<div class="p-6">
    <h2 class="text-2xl font-orbitron text-cyan-400 mb-6">CARGAISON DU VAISSEAU</h2>

    @if($vaisseau)
    <div class="panel p-4">
        <div class="text-center text-gray-400 py-8">
            <p class="mb-4">📦 Soute du vaisseau</p>
            <p class="text-sm">Fonctionnalité en développement</p>
            <p class="text-xs text-gray-600 mt-2">
                Cette section affichera la cargaison transportée par le vaisseau (ressources, marchandises).
            </p>
        </div>
    </div>
    @else
    <div class="panel p-4 text-center text-gray-500">
        <p>Aucun vaisseau actif</p>
    </div>
    @endif
</div>
