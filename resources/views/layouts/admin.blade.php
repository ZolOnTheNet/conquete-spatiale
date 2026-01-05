@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-gray-900/90 border-b border-red-500/30 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-orbitron text-red-400">@yield('admin-title', 'ADMINISTRATION')</h1>
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
                <a href="{{ route('admin.index') }}" class="block px-4 py-2 rounded {{ request()->routeIs('admin.index') ? 'bg-red-500/20 text-red-300' : 'hover:bg-red-500/10 text-gray-300' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.comptes') }}" class="block px-4 py-2 rounded {{ request()->routeIs('admin.comptes') ? 'bg-red-500/20 text-red-300' : 'hover:bg-red-500/10 text-gray-300' }}">
                    Comptes
                </a>
                <a href="{{ route('admin.univers') }}" class="block px-4 py-2 rounded {{ request()->routeIs('admin.univers*') ? 'bg-red-500/20 text-red-300' : 'hover:bg-red-500/10 text-gray-300' }}">
                    Univers
                </a>
                <a href="{{ route('admin.planetes') }}" class="block px-4 py-2 rounded {{ request()->routeIs('admin.planetes*') ? 'bg-red-500/20 text-red-300' : 'hover:bg-red-500/10 text-gray-300' }}">
                    Planètes
                </a>
                <a href="{{ route('admin.production') }}" class="block px-4 py-2 rounded {{ request()->routeIs('admin.production*') ? 'bg-red-500/20 text-red-300' : 'hover:bg-red-500/10 text-gray-300' }}">
                    Productions
                </a>
                <a href="{{ route('admin.stations.index') }}" class="block px-4 py-2 rounded {{ request()->routeIs('admin.stations*') ? 'bg-red-500/20 text-red-300' : 'hover:bg-red-500/10 text-gray-300' }}">
                    Stations
                </a>
                <a href="{{ route('admin.mines-admin.index') }}" class="block px-4 py-2 rounded {{ request()->routeIs('admin.mines-admin*') ? 'bg-red-500/20 text-red-300' : 'hover:bg-red-500/10 text-gray-300' }}">
                    Mines
                </a>
                <a href="{{ route('admin.ressources.index') }}" class="block px-4 py-2 rounded {{ request()->routeIs('admin.ressources*') ? 'bg-red-500/20 text-red-300' : 'hover:bg-red-500/10 text-gray-300' }}">
                    Ressources
                </a>
                <a href="{{ route('admin.gisements.index') }}" class="block px-4 py-2 rounded {{ request()->routeIs('admin.gisements*') ? 'bg-red-500/20 text-red-300' : 'hover:bg-red-500/10 text-gray-300' }}">
                    Gisements
                </a>
                <a href="{{ route('admin.carte') }}" class="block px-4 py-2 rounded {{ request()->routeIs('admin.carte*') ? 'bg-red-500/20 text-red-300' : 'hover:bg-red-500/10 text-gray-300' }}">
                    Carte
                </a>
                <a href="{{ route('admin.backup') }}" class="block px-4 py-2 rounded {{ request()->routeIs('admin.backup') ? 'bg-red-500/20 text-red-300' : 'hover:bg-red-500/10 text-gray-300' }}">
                    Backup
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            @yield('admin-content')
        </main>
    </div>
</div>
@endsection
