@extends('layouts.app')

@section('title', 'Admin - Dashboard')

@section('content')
<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-gray-900/90 border-b border-red-500/30 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-orbitron text-red-400">ADMINISTRATION</h1>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-cyan-400 hover:text-cyan-300 text-sm">
                Retour au jeu
            </a>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-gray-500 hover:text-red-400 text-sm">
                    Deconnexion
                </button>
            </form>
        </div>
    </header>

    <div class="flex-1 flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900/80 border-r border-red-500/20 p-4">
            <nav class="space-y-2">
                <a href="{{ route('admin.index') }}" class="block px-4 py-2 rounded bg-red-500/20 text-red-300">
                    Dashboard
                </a>
                <a href="{{ route('admin.comptes') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Comptes
                </a>
                <a href="{{ route('admin.univers') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Univers
                </a>
                <a href="{{ route('admin.backup') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Backup
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <h2 class="text-xl font-bold text-white mb-6">Statistiques Generales</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4">
                    <div class="text-3xl font-bold text-cyan-400">{{ $stats['comptes'] }}</div>
                    <div class="text-sm text-gray-400">Comptes</div>
                </div>

                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4">
                    <div class="text-3xl font-bold text-green-400">{{ $stats['personnages'] }}</div>
                    <div class="text-sm text-gray-400">Personnages</div>
                </div>

                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4">
                    <div class="text-3xl font-bold text-yellow-400">{{ $stats['systemes'] }}</div>
                    <div class="text-sm text-gray-400">Systemes</div>
                </div>

                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4">
                    <div class="text-3xl font-bold text-purple-400">{{ $stats['planetes'] }}</div>
                    <div class="text-sm text-gray-400">Planetes</div>
                </div>

                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4">
                    <div class="text-3xl font-bold text-red-400">{{ $stats['combats_actifs'] }}</div>
                    <div class="text-sm text-gray-400">Combats actifs</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <h3 class="text-lg font-bold text-white mt-8 mb-4">Actions Rapides</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('admin.comptes') }}" class="bg-gray-800/50 border border-gray-700 rounded-lg p-4 hover:bg-gray-700/50 transition">
                    <div class="text-cyan-400 font-bold">Gerer Comptes</div>
                    <div class="text-xs text-gray-500">Voir et modifier les utilisateurs</div>
                </a>

                <a href="{{ route('admin.univers') }}" class="bg-gray-800/50 border border-gray-700 rounded-lg p-4 hover:bg-gray-700/50 transition">
                    <div class="text-yellow-400 font-bold">Explorer Univers</div>
                    <div class="text-xs text-gray-500">Voir systemes et planetes</div>
                </a>

                <a href="{{ route('admin.backup') }}" class="bg-gray-800/50 border border-gray-700 rounded-lg p-4 hover:bg-gray-700/50 transition">
                    <div class="text-green-400 font-bold">Backups</div>
                    <div class="text-xs text-gray-500">Sauvegarder la base</div>
                </a>

                <a href="{{ route('dashboard') }}" class="bg-gray-800/50 border border-gray-700 rounded-lg p-4 hover:bg-gray-700/50 transition">
                    <div class="text-purple-400 font-bold">Retour Jeu</div>
                    <div class="text-xs text-gray-500">Interface de jeu</div>
                </a>
            </div>
        </main>
    </div>
</div>
@endsection
