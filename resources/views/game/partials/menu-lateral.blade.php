{{-- Menu Latéral Gauche --}}
<aside class="w-64 bg-gray-900/90 border-r border-cyan-500/30 flex flex-col">
    <div class="bg-gray-800/50 border-b border-cyan-500/30 px-4 py-3">
        <h2 class="text-sm font-bold text-cyan-400">NAVIGATION</h2>
    </div>

    <div class="flex-1 overflow-y-auto p-2">
        {{-- SECTION PERSONNAGE --}}
        <div class="mb-3">
            <h3 class="text-xs text-gray-500 uppercase px-2 mb-1">👤 Personnage</h3>
            <a href="{{ route('personnage.dossier') }}" class="block px-3 py-2 rounded text-sm text-gray-300 hover:text-cyan-400 hover:bg-cyan-900/20 transition">
                Dossier
            </a>
            <a href="{{ route('carte') }}" class="block px-3 py-2 rounded text-sm text-gray-300 hover:text-cyan-400 hover:bg-cyan-900/20 transition">
                Spatiocarte
            </a>
            <a href="{{ route('personnage.gestion') }}" class="block px-3 py-2 rounded text-sm text-gray-300 hover:text-cyan-400 hover:bg-cyan-900/20 transition">
                Gestion
            </a>
        </div>

        {{-- SECTION VAISSEAU (contextuel : devient STATION si amarré) --}}
        <div class="mb-3">
            @if(isset($vaisseau) && $vaisseau && $vaisseau->dans_station_id)
                {{-- Amarré dans une station --}}
                <h3 class="text-xs text-gray-500 uppercase px-2 mb-1">🏭 Station</h3>
                <a href="{{ route('station.hall') }}" class="block px-3 py-2 rounded text-sm text-gray-300 hover:text-cyan-400 hover:bg-cyan-900/20 transition">
                    Hall
                </a>
                <a href="{{ route('station.hangar') }}" class="block px-3 py-2 rounded text-sm text-gray-300 hover:text-cyan-400 hover:bg-cyan-900/20 transition">
                    Hangar
                </a>
                <a href="{{ route('station.marche') }}" class="block px-3 py-2 rounded text-sm text-gray-300 hover:text-cyan-400 hover:bg-cyan-900/20 transition">
                    Marché
                </a>
                <a href="{{ route('station.missions') }}" class="block px-3 py-2 rounded text-sm text-gray-300 hover:text-cyan-400 hover:bg-cyan-900/20 transition">
                    Missions
                </a>
                <a href="{{ route('station.cantina') }}" class="block px-3 py-2 rounded text-sm text-gray-300 hover:text-cyan-400 hover:bg-cyan-900/20 transition">
                    Cantina
                </a>
            @else
                {{-- Dans le vaisseau --}}
                <h3 class="text-xs text-gray-500 uppercase px-2 mb-1">🚀 Vaisseau</h3>
                <a href="{{ route('navire.timonerie') }}" class="block px-3 py-2 rounded text-sm text-gray-300 hover:text-cyan-400 hover:bg-cyan-900/20 transition">
                    Timonerie
                </a>
                <a href="{{ route('navire.ingenierie') }}" class="block px-3 py-2 rounded text-sm text-gray-300 hover:text-cyan-400 hover:bg-cyan-900/20 transition">
                    Ingénierie
                </a>
                <a href="{{ route('navire.com') }}" class="block px-3 py-2 rounded text-sm text-gray-300 hover:text-cyan-400 hover:bg-cyan-900/20 transition">
                    COM
                </a>
                <a href="{{ route('navire.soute') }}" class="block px-3 py-2 rounded text-sm text-gray-300 hover:text-cyan-400 hover:bg-cyan-900/20 transition">
                    Soute
                </a>
                <a href="{{ route('navire.equipage') }}" class="block px-3 py-2 rounded text-sm text-gray-300 hover:text-cyan-400 hover:bg-cyan-900/20 transition">
                    Équipage
                </a>
            @endif
        </div>

        {{-- SECTION JEU --}}
        <div class="mb-3">
            <h3 class="text-xs text-gray-500 uppercase px-2 mb-1">🎮 Jeu</h3>
            <a href="{{ route('jeu.profil') }}" class="block px-3 py-2 rounded text-sm text-gray-300 hover:text-cyan-400 hover:bg-cyan-900/20 transition">
                Profil
            </a>
            <form method="POST" action="{{ route('logout') }}" class="inline w-full">
                @csrf
                <button type="submit" class="w-full text-left px-3 py-2 rounded text-sm text-gray-300 hover:text-red-400 hover:bg-red-900/20 transition">
                    Quitter
                </button>
            </form>
        </div>

        {{-- SECTION ADMIN (si admin) --}}
        @if(isset($compte) && ($compte->is_admin ?? false))
        <div class="mb-3 border-t border-cyan-500/20 pt-3">
            <h3 class="text-xs text-red-400 uppercase px-2 mb-1">⚙️ Administration</h3>
            <a href="{{ route('admin.index') }}" class="block px-3 py-2 rounded text-sm text-red-300 hover:text-red-200 hover:bg-red-900/20 transition">
                Univers
            </a>
        </div>
        @endif
    </div>

    <!-- Info personnage (en bas) -->
    @if(isset($personnage))
    <div class="border-t border-cyan-500/20 p-4">
        <div class="text-xs text-gray-500">Niveau {{ $personnage->niveau }}</div>
        <div class="text-sm text-gray-300">{{ $personnage->prenom ?? '' }} {{ $personnage->nom }}</div>
        <div class="flex items-center justify-between mt-1">
            <a href="{{ route('personnage.selection') }}" class="text-xs text-cyan-500 hover:text-cyan-400 underline" title="Changer de personnage">
                👤 Personnages
            </a>
            <div class="text-xs text-gray-500">XP: {{ $personnage->experience }}</div>
        </div>
    </div>
    @endif
</aside>
