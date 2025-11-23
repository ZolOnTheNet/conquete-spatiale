@props(['location' => null, 'sections' => []])

@if($location && !empty($sections))
<!-- Menu contextuel basé sur la localisation -->
<nav class="space-y-4">
    <!-- Informations de localisation -->
    <div class="px-4 py-3 bg-cyan-900/20 border border-cyan-500/30 rounded">
        <div class="text-xs text-gray-400 mb-1">Position actuelle</div>
        <div class="text-sm text-cyan-400 font-bold">{{ $location->getDescription() }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ $location->getCoordonneesFormatees() }}</div>
    </div>

    <!-- Sections du menu -->
    @foreach($sections as $sectionKey => $section)
    <div>
        <div class="px-4 py-2 text-xs font-bold text-gray-400 uppercase flex items-center gap-2">
            <span>{{ $section['icon'] }}</span>
            <span>{{ $section['label'] }}</span>
        </div>
        <div class="space-y-1">
            @foreach($section['items'] as $item)
            <a href="{{ route($item['route']) }}"
               class="block px-4 py-2 rounded hover:bg-cyan-500/10 text-gray-300 hover:text-cyan-400 text-sm transition-colors
                      {{ request()->routeIs($item['route']) ? 'bg-cyan-500/20 text-cyan-300' : '' }}">
                {{ $item['label'] }}
            </a>
            @endforeach
        </div>
    </div>
    @endforeach

    <!-- Lien Admin si applicable -->
    @if(auth()->user()?->is_admin)
    <div class="mt-6 pt-4 border-t border-gray-700">
        <a href="{{ route('admin.index') }}" class="block px-4 py-2 rounded hover:bg-red-500/10 text-red-400 text-sm">
            🔧 Administration
        </a>
    </div>
    @endif
</nav>
@else
<!-- Menu par défaut si pas de localisation -->
<nav class="space-y-2">
    <a href="{{ route('dashboard') }}" class="block px-4 py-2 rounded hover:bg-cyan-500/10 text-gray-300">
        Tableau de bord
    </a>
    <a href="{{ route('personnage.selection') }}" class="block px-4 py-2 rounded hover:bg-cyan-500/10 text-gray-300">
        Sélection personnage
    </a>
</nav>
@endif
