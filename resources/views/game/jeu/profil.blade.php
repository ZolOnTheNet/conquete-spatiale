@extends('layouts.app')

@section('title', 'Profil Joueur')

@section('content')
<div class="h-screen flex flex-col bg-gray-900">

    {{-- Header 4 colonnes --}}
    <x-game-header
        :personnage="$personnage"
        :vaisseau="$personnage->vaisseauActif"
        :systeme="$systeme ?? null"
    />

    {{-- Layout principal : Menu + Contenu --}}
    <div class="flex-1 flex overflow-hidden">

        {{-- Menu latéral gauche --}}
        @include('game.partials.menu-lateral', [
            'personnage' => $personnage,
            'vaisseau' => $personnage->vaisseauActif ?? null,
            'compte' => auth()->user()
        ])

        {{-- Zone de contenu principale --}}
        <main class="flex-1 overflow-auto p-6">
            <div class="max-w-7xl mx-auto">

                <h2 class="text-3xl font-orbitron text-cyan-400 mb-6">👤 PROFIL JOUEUR</h2>

                {{-- Informations du compte --}}
                <div class="bg-gray-800/50 border border-cyan-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-cyan-300 mb-4">Compte</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Email:</span>
                            <span class="text-white">{{ $compte->email }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Date d'inscription:</span>
                            <span class="text-white">{{ $compte->created_at->format('d/m/Y') }}</span>
                        </div>
                        @if($compte->is_admin)
                            <div class="flex justify-between">
                                <span class="text-gray-400">Rôle:</span>
                                <span class="text-yellow-400 font-bold">⭐ Administrateur</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Personnages --}}
                <div class="bg-gray-800/50 border border-yellow-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-yellow-300 mb-4">Personnages</h3>
                    @if($compte->personnages && $compte->personnages->count() > 0)
                        <div class="space-y-3">
                            @foreach($compte->personnages as $perso)
                                <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <h4 class="text-white font-bold text-lg">{{ $perso->nom }}</h4>
                                            <p class="text-gray-400 text-sm">
                                                Niveau {{ $perso->niveau ?? 1 }} •
                                                {{ number_format($perso->credits ?? 0, 0, ',', ' ') }} CR •
                                                {{ $perso->points_action ?? 0 }}/{{ $perso->max_points_action ?? 36 }} PA
                                                @php
                                                    $paRestant = '';
                                                    if ($perso->points_action >= $perso->max_points_action) {
                                                        $paRestant = '<span class="text-green-400">(max)</span>';
                                                    } elseif ($perso->derniere_recuperation_pa) {
                                                        $delai_minutes = config('game.pa.recuperation_delai', 60);
                                                        $minutes_ecoulees = now()->diffInMinutes($perso->derniere_recuperation_pa);
                                                        $minutes_restantes = $delai_minutes - ($minutes_ecoulees % $delai_minutes);
                                                        if ($minutes_restantes >= 60) {
                                                            $heures = floor($minutes_restantes / 60);
                                                            $mins = $minutes_restantes % 60;
                                                            $paRestant = $mins > 0
                                                                ? "<span class=\"text-cyan-400\">(+1 dans {$heures}h{$mins}m)</span>"
                                                                : "<span class=\"text-cyan-400\">(+1 dans {$heures}h)</span>";
                                                        } else {
                                                            $paRestant = "<span class=\"text-cyan-400\">(+1 dans {$minutes_restantes}m)</span>";
                                                        }
                                                    }
                                                @endphp
                                                {!! $paRestant !!}
                                            </p>
                                        </div>
                                        @if($perso->id === $personnage->id)
                                            <span class="px-3 py-1 bg-green-900/30 text-green-400 text-sm rounded">✓ Actif</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-400 text-sm italic">Aucun personnage</p>
                    @endif
                </div>

                {{-- Statistiques --}}
                <div class="bg-gray-800/50 border border-purple-500/30 rounded-lg p-6 mb-6">
                    <h3 class="text-xl text-purple-300 mb-4">Statistiques</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4">
                            <div class="text-gray-400 mb-1">Systèmes découverts</div>
                            <div class="text-2xl text-cyan-400 font-bold">{{ $personnage->decouvertes()->count() ?? 0 }}</div>
                        </div>
                        <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4">
                            <div class="text-gray-400 mb-1">Distance parcourue</div>
                            <div class="text-2xl text-yellow-400 font-bold">- AL</div>
                            <div class="text-xs text-gray-500">En développement</div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="bg-gray-800/50 border border-gray-500/30 rounded-lg p-6">
                    <h3 class="text-xl text-gray-300 mb-4">Actions</h3>
                    <div class="flex gap-4">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded transition">
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </main>

        {{-- Console Droite Redimensionnable --}}
        <x-console-resizable>
            @include('game.partials.console')
        </x-console-resizable>
    </div>

</div>
@endsection
