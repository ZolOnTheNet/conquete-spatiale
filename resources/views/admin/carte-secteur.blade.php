@extends('layouts.app')

@section('title', 'Admin - Secteur')

@section('content')
<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-gray-900/90 border-b border-red-500/30 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-orbitron text-red-400">SECTEUR {{ $x }}, {{ $y }}, {{ $z }}</h1>
        </div>
        <a href="{{ route('admin.carte', ['x' => $x * 10, 'y' => $y * 10, 'z' => $z * 10]) }}" class="text-cyan-400 hover:text-cyan-300 text-sm">
            ← Retour à la carte
        </a>
    </header>

    <div class="flex-1 flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900/80 border-r border-red-500/20 p-4">
            <nav class="space-y-2">
                <a href="{{ route('admin.index') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Dashboard
                </a>
                <a href="{{ route('admin.comptes') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Comptes
                </a>
                <a href="{{ route('admin.univers') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Univers
                </a>
                <a href="{{ route('admin.planetes') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Planètes
                </a>
                <a href="{{ route('admin.production') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Productions
                </a>
                <a href="{{ route('admin.carte') }}" class="block px-4 py-2 rounded bg-red-500/20 text-red-300">
                    Carte
                </a>
                <a href="{{ route('admin.backup') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Backup
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            @if($systemes->count() > 0)
                @foreach($systemes as $systeme)
                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4 mb-6">
                    <!-- Informations du système -->
                    <div class="mb-4">
                        <h2 class="text-xl font-bold text-yellow-400 mb-2">
                            <a href="{{ route('admin.univers.show', $systeme->id) }}" class="hover:text-yellow-300">
                                {{ $systeme->nom }}
                            </a>
                        </h2>
                        <div class="flex gap-4 text-sm text-gray-400">
                            <div>Type: <span class="text-white">{{ $systeme->type_etoile }}</span></div>
                            <div>Puissance: <span class="text-cyan-400">{{ $systeme->puissance }}</span></div>
                            <div>Planètes: <span class="text-green-400">{{ $systeme->planetes->count() }}</span></div>
                            <div>Position: <span class="text-purple-400">
                                {{ number_format($systeme->position_x, 2) }},
                                {{ number_format($systeme->position_y, 2) }},
                                {{ number_format($systeme->position_z, 2) }} AL
                            </span></div>
                        </div>
                    </div>

                    <!-- Carte visuelle du système -->
                    <div class="bg-black border border-gray-600 rounded p-4">
                        <svg width="600" height="600" viewBox="0 0 600 600">
                            <!-- Grille de fond -->
                            <defs>
                                <pattern id="grid-{{ $systeme->id }}" width="60" height="60" patternUnits="userSpaceOnUse">
                                    <path d="M 60 0 L 0 0 0 60" fill="none" stroke="rgba(100,100,100,0.2)" stroke-width="1"/>
                                </pattern>
                            </defs>
                            <rect width="600" height="600" fill="url(#grid-{{ $systeme->id }})"/>

                            <!-- Axes de référence -->
                            <line x1="300" y1="0" x2="300" y2="600" stroke="rgba(100,150,200,0.3)" stroke-width="1" stroke-dasharray="5,5"/>
                            <line x1="0" y1="300" x2="600" y2="300" stroke="rgba(100,150,200,0.3)" stroke-width="1" stroke-dasharray="5,5"/>

                            <!-- Texte d'échelle -->
                            <text x="10" y="20" fill="rgba(200,200,200,0.5)" font-size="12">10 AL × 10 AL (secteur {{ $x }}, {{ $y }}, {{ $z }})</text>
                            <text x="10" y="590" fill="rgba(200,200,200,0.5)" font-size="10">Centre: 300,300 = position ({{ number_format($systeme->position_x, 1) }}, {{ number_format($systeme->position_y, 1) }}) AL</text>

                            @php
                                // Convertir la position du système en coordonnées SVG
                                // Position 0-10 AL -> 0-600 pixels (60px par AL)
                                // Centre la carte sur le système
                                $centerX = 300;
                                $centerY = 300;
                                $scale = 60; // 60 pixels par AL

                                // Position du système (relatif au centre)
                                $sysX = $centerX + ($systeme->position_x - 5) * $scale;
                                $sysY = $centerY + ($systeme->position_y - 5) * $scale;
                            @endphp

                            <!-- Système stellaire (étoile) -->
                            <circle cx="{{ $sysX }}" cy="{{ $sysY }}" r="8" fill="yellow" stroke="orange" stroke-width="2">
                                <animate attributeName="r" values="8;10;8" dur="2s" repeatCount="indefinite"/>
                            </circle>
                            <text x="{{ $sysX }}" y="{{ $sysY - 15 }}" fill="yellow" font-size="12" text-anchor="middle" font-weight="bold">☉</text>
                            <text x="{{ $sysX }}" y="{{ $sysY + 25 }}" fill="white" font-size="10" text-anchor="middle">{{ $systeme->nom }}</text>

                            <!-- Planètes -->
                            @foreach($systeme->planetes as $index => $planete)
                                @php
                                    // Disposer les planètes en cercle autour du système
                                    // avec un rayon croissant selon l'index
                                    $angle = ($index / max($systeme->planetes->count(), 1)) * 2 * M_PI;
                                    $orbitRadius = 50 + ($index * 30);
                                    $planetX = $sysX + cos($angle) * $orbitRadius;
                                    $planetY = $sysY + sin($angle) * $orbitRadius;

                                    // Couleur selon le type de planète
                                    $planetColors = [
                                        'tellurique' => '#8B4513',
                                        'gazeuse' => '#4169E1',
                                        'glacee' => '#87CEEB',
                                        'oceanique' => '#1E90FF',
                                        'desertique' => '#DEB887',
                                        'volcanique' => '#FF4500',
                                    ];
                                    $planetColor = $planetColors[$planete->type] ?? '#808080';
                                @endphp

                                <!-- Orbite -->
                                <circle cx="{{ $sysX }}" cy="{{ $sysY }}" r="{{ $orbitRadius }}"
                                        fill="none" stroke="rgba(100,100,100,0.3)" stroke-width="1" stroke-dasharray="2,2"/>

                                <!-- Planète -->
                                <circle cx="{{ $planetX }}" cy="{{ $planetY }}" r="5" fill="{{ $planetColor }}" stroke="white" stroke-width="1"/>
                                <text x="{{ $planetX }}" y="{{ $planetY - 10 }}" fill="white" font-size="9" text-anchor="middle">{{ $planete->nom }}</text>

                                @if($planete->accessible)
                                    <text x="{{ $planetX }}" y="{{ $planetY + 15 }}" fill="lime" font-size="8" text-anchor="middle">✓</text>
                                @endif
                            @endforeach

                            <!-- Étiquettes des axes -->
                            <text x="590" y="295" fill="rgba(200,200,200,0.7)" font-size="10">X+</text>
                            <text x="305" y="15" fill="rgba(200,200,200,0.7)" font-size="10">Y+</text>
                        </svg>
                    </div>

                    <!-- Liste des planètes -->
                    @if($systeme->planetes->count() > 0)
                    <div class="mt-4">
                        <h3 class="text-sm font-bold text-gray-400 mb-2">Planètes du système:</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach($systeme->planetes as $planete)
                            <div class="bg-gray-900/50 border border-gray-700 rounded p-2 text-xs">
                                <div class="font-bold text-cyan-400">{{ $planete->nom }}</div>
                                <div class="text-gray-400">Type: {{ ucfirst($planete->type) }}</div>
                                <div class="text-gray-400">Rayon: {{ number_format($planete->rayon, 1) }} R</div>
                                <div class="text-gray-400">Masse: {{ number_format($planete->masse, 1) }} M</div>
                                @if($planete->accessible)
                                    <div class="text-green-400">✓ Accessible</div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            @else
                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-8 text-center">
                    <div class="text-gray-400 text-lg mb-2">Secteur vide</div>
                    <div class="text-gray-500 text-sm">Aucun système stellaire dans ce secteur</div>
                </div>
            @endif
        </main>
    </div>
</div>
@endsection
