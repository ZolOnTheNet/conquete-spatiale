<div class="p-6">
    <h2 class="text-2xl font-orbitron text-cyan-400 mb-6">ÉTAT DU VAISSEAU</h2>

    @if($vaisseau)
    <div class="space-y-4">
        <div class="panel p-4">
            <h3 class="text-lg text-cyan-300 mb-3">Systèmes</h3>
            <div class="text-center text-gray-400 py-4">
                <p class="text-sm">État des systèmes du vaisseau en développement</p>
                <p class="text-xs text-gray-600 mt-2">
                    Cette section affichera l'état de la coque, des boucliers, de l'énergie, et des différents systèmes.
                </p>
            </div>
        </div>
    </div>
    @else
    <div class="panel p-4 text-center text-gray-500">
        <p>Aucun vaisseau actif</p>
    </div>
    @endif
</div>
