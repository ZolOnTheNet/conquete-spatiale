{{-- Menu principal structuré selon GDD --}}
@props(['context' => 'navire', 'personnage' => null, 'isAdmin' => false])

<nav class="main-menu">

    {{-- ═══════════════════════════════════════════════════
        MENU PERSONNAGE
        ═══════════════════════════════════════════════════ --}}
    <div class="menu-section">
        <h3 class="menu-title">📊 PERSONNAGE</h3>
        <ul class="menu-items">
            <li>
                <a href="{{ route('personnage.dossier') }}"
                   class="{{ request()->routeIs('personnage.dossier') ? 'active' : '' }}">
                    Dossier
                </a>
            </li>
            <li>
                <a href="{{ route('personnage.spatiocarte') }}"
                   class="{{ request()->routeIs('personnage.spatiocarte') ? 'active' : '' }}">
                    Spatiocarte
                </a>
            </li>
            <li>
                <a href="{{ route('personnage.gestion') }}"
                   class="{{ request()->routeIs('personnage.gestion') ? 'active' : '' }}">
                    Gestion
                </a>
            </li>
        </ul>
    </div>

    {{-- ═══════════════════════════════════════════════════
        MENU NAVIRE / STATION (Contextuel)
        ═══════════════════════════════════════════════════ --}}
    <div class="menu-section">
        <h3 class="menu-title">
            @if($context === 'navire')
                🚀 NAVIRE
            @else
                🏭 STATION
            @endif
        </h3>
        <ul class="menu-items">
            @if($context === 'navire')
                {{-- Menu Navire --}}
                <li>
                    <a href="{{ route('navire.timonerie') }}"
                       class="{{ request()->routeIs('navire.timonerie') ? 'active' : '' }}">
                        Timonerie
                    </a>
                </li>
                <li>
                    <a href="{{ route('navire.ingenierie') }}"
                       class="{{ request()->routeIs('navire.ingenierie') ? 'active' : '' }}">
                        Ingénierie
                    </a>
                </li>
                <li>
                    <a href="{{ route('navire.com') }}"
                       class="{{ request()->routeIs('navire.com') ? 'active' : '' }}">
                        COM
                    </a>
                </li>
                <li>
                    <a href="{{ route('navire.soute') }}"
                       class="{{ request()->routeIs('navire.soute') ? 'active' : '' }}">
                        Soute
                    </a>
                </li>
                <li>
                    <a href="{{ route('navire.equipage') }}"
                       class="{{ request()->routeIs('navire.equipage') ? 'active' : '' }}">
                        Équipage
                    </a>
                </li>
            @else
                {{-- Menu Station --}}
                <li>
                    <a href="{{ route('station.hall') }}"
                       class="{{ request()->routeIs('station.hall') ? 'active' : '' }}">
                        Hall Principal
                    </a>
                </li>
                <li>
                    <a href="{{ route('station.hangar') }}"
                       class="{{ request()->routeIs('station.hangar') ? 'active' : '' }}">
                        Hangar
                    </a>
                </li>
                <li>
                    <a href="{{ route('station.marche') }}"
                       class="{{ request()->routeIs('station.marche') ? 'active' : '' }}">
                        Marché
                    </a>
                </li>
                <li>
                    <a href="{{ route('station.missions') }}"
                       class="{{ request()->routeIs('station.missions') ? 'active' : '' }}">
                        Missions
                    </a>
                </li>
                <li>
                    <a href="{{ route('station.cantina') }}"
                       class="{{ request()->routeIs('station.cantina') ? 'active' : '' }}">
                        Cantina
                    </a>
                </li>
            @endif
        </ul>
    </div>

    {{-- ═══════════════════════════════════════════════════
        MENU JEU
        ═══════════════════════════════════════════════════ --}}
    <div class="menu-section">
        <h3 class="menu-title">⚙️ JEU</h3>
        <ul class="menu-items">
            <li>
                <a href="{{ route('jeu.profil') }}"
                   class="{{ request()->routeIs('jeu.profil') ? 'active' : '' }}">
                    Profil
                </a>
            </li>
            <li>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="logout-btn">
                        Quitter
                    </button>
                </form>
            </li>
        </ul>
    </div>

    {{-- ═══════════════════════════════════════════════════
        MENU ADMIN (visible uniquement si admin)
        ═══════════════════════════════════════════════════ --}}
    @if($isAdmin)
    <div class="menu-section menu-admin">
        <h3 class="menu-title">🔧 ADMIN</h3>
        <ul class="menu-items">
            <li>
                <a href="{{ route('admin.index') }}"
                   class="{{ request()->routeIs('admin.index') ? 'active' : '' }}">
                    Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('admin.carte') }}"
                   class="{{ request()->routeIs('admin.carte') ? 'active' : '' }}">
                    Carte Univers
                </a>
            </li>
            <li>
                <a href="{{ route('admin.comptes') }}"
                   class="{{ request()->routeIs('admin.comptes') ? 'active' : '' }}">
                    Gestion Joueurs
                </a>
            </li>
            <li>
                <a href="{{ route('admin.univers') }}"
                   class="{{ request()->routeIs('admin.univers') ? 'active' : '' }}">
                    Logs Système
                </a>
            </li>
        </ul>
    </div>
    @endif

</nav>

<style>
/* Menu principal */
.main-menu {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    padding: 1rem;
    border-right: 2px solid #0f3460;
    min-height: 100vh;
    width: 250px;
    font-family: 'Share Tech Mono', monospace;
}

/* Section de menu */
.menu-section {
    margin-bottom: 2rem;
}

/* Titre de section (non cliquable) */
.menu-title {
    font-family: 'Orbitron', sans-serif;
    color: #4a9eff;
    font-size: 0.9rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #0f3460;
    cursor: default;
}

/* Liste des items */
.menu-items {
    list-style: none;
    padding: 0;
    margin: 0;
}

.menu-items li {
    margin: 0.25rem 0;
}

/* Liens du menu */
.menu-items a {
    display: block;
    padding: 0.5rem 1rem;
    color: #e0e0e0;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.2s;
    font-size: 0.9rem;
}

.menu-items a:hover {
    background: rgba(74, 158, 255, 0.1);
    color: #4a9eff;
    padding-left: 1.25rem;
}

.menu-items a.active {
    background: rgba(74, 158, 255, 0.2);
    color: #4a9eff;
    border-left: 3px solid #4a9eff;
    font-weight: bold;
}

/* Bouton quitter */
.logout-btn {
    display: block;
    width: 100%;
    text-align: left;
    padding: 0.5rem 1rem;
    color: #e0e0e0;
    background: none;
    border: none;
    border-radius: 4px;
    transition: all 0.2s;
    font-size: 0.9rem;
    font-family: 'Share Tech Mono', monospace;
    cursor: pointer;
}

.logout-btn:hover {
    background: rgba(255, 107, 107, 0.1);
    color: #ff6b6b;
    padding-left: 1.25rem;
}

/* Menu admin spécial */
.menu-admin {
    border-top: 2px solid #ff6b6b;
    padding-top: 1rem;
    margin-top: 2rem;
}

.menu-admin .menu-title {
    color: #ff6b6b;
    border-bottom-color: #ff6b6b;
}

.menu-admin .menu-items a:hover {
    background: rgba(255, 107, 107, 0.1);
    color: #ff6b6b;
}

.menu-admin .menu-items a.active {
    background: rgba(255, 107, 107, 0.2);
    color: #ff6b6b;
    border-left-color: #ff6b6b;
}

/* ═══════════════════════════════════════════════════
   RESPONSIVE - MOBILE
   ═══════════════════════════════════════════════════ */

@media (max-width: 768px) {
    .main-menu {
        width: 100%;
        min-height: auto;
        border-right: none;
        border-bottom: 2px solid #0f3460;
    }

    .menu-section {
        margin-bottom: 1rem;
    }

    .menu-title {
        font-size: 0.8rem;
    }

    .menu-items a {
        font-size: 0.85rem;
        padding: 0.4rem 0.8rem;
    }
}
</style>
