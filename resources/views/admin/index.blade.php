@extends('layouts.admin')

@section('title', 'Admin - Dashboard')

@section('admin-title', 'DASHBOARD')

@section('admin-content')
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

                <a href="{{ route('admin.production') }}" class="bg-gray-800/50 border border-gray-700 rounded-lg p-4 hover:bg-gray-700/50 transition">
                    <div class="text-orange-400 font-bold">Gerer Productions</div>
                    <div class="text-xs text-gray-500">Modifier gisements et ressources</div>
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
@endsection
