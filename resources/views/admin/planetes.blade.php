@extends('layouts.app')

@section('title', 'Admin - Planètes')

@section('content')
<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-gray-900/90 border-b border-red-500/30 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-orbitron text-red-400">ADMINISTRATION</h1>
        </div>
        <a href="{{ route('dashboard') }}" class="text-cyan-400 hover:text-cyan-300 text-sm">
            Retour au jeu
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
                <a href="{{ route('admin.planetes') }}" class="block px-4 py-2 rounded bg-red-500/20 text-red-300">
                    Planètes
                </a>
                <a href="{{ route('admin.production') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Productions
                </a>
                <a href="{{ route('admin.backup') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Backup
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <h2 class="text-xl font-bold text-white mb-6">Planètes et Objets Célestes</h2>

            <div class="bg-gray-800/50 border border-gray-700 rounded-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Nom</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Système</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Type</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Rayon</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Détectabilité</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">POI Connu</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Accessible</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($planetes as $planete)
                        <tr class="hover:bg-gray-700/30">
                            <td class="px-4 py-3 text-sm text-white">{{ $planete->nom }}</td>
                            <td class="px-4 py-3 text-sm text-yellow-300">
                                @if($planete->systemeStellaire)
                                    {{ $planete->systemeStellaire->nom }}
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-blue-400">
                                @if($planete->type_planete)
                                    {{ $planete->type_planete }}
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-purple-400">
                                @if($planete->rayon)
                                    {{ number_format($planete->rayon, 2) }}
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-cyan-300 font-semibold">
                                @if($planete->detectabilite_base)
                                    {{ number_format($planete->detectabilite_base, 2) }}
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($planete->poi_connu)
                                    <span class="text-green-400">✓</span>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($planete->accessible)
                                    <span class="text-green-400">Oui</span>
                                @else
                                    <span class="text-red-400" title="{{ $planete->raison_inaccessible }}">Non</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $planetes->links() }}
            </div>
        </main>
    </div>
</div>
@endsection
