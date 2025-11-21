@extends('layouts.app')

@section('title', 'Admin - Comptes')

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
                <a href="{{ route('admin.comptes') }}" class="block px-4 py-2 rounded bg-red-500/20 text-red-300">
                    Comptes
                </a>
                <a href="{{ route('admin.univers') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Univers
                </a>
                <a href="{{ route('admin.planetes') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Planètes
                </a>
                <a href="{{ route('admin.backup') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-gray-300">
                    Backup
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <h2 class="text-xl font-bold text-white mb-6">Gestion des Comptes</h2>

            <div class="bg-gray-800/50 border border-gray-700 rounded-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">ID</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Nom</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Email</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Personnages</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Admin</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-400">Cree le</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($comptes as $compte)
                        <tr class="hover:bg-gray-700/30">
                            <td class="px-4 py-3 text-sm text-gray-300">{{ $compte->id }}</td>
                            <td class="px-4 py-3 text-sm text-white">{{ $compte->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-300">{{ $compte->email }}</td>
                            <td class="px-4 py-3 text-sm text-cyan-400">{{ $compte->personnages->count() }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if($compte->is_admin)
                                    <span class="text-red-400">Oui</span>
                                @else
                                    <span class="text-gray-500">Non</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $compte->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $comptes->links() }}
            </div>
        </main>
    </div>
</div>
@endsection
