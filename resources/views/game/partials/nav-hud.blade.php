{{-- Navigation latérale — style HUD (utilisé par layouts/game-hud.blade.php) --}}
<div class="nav-scroll">

  {{-- Section contextuelle : Vaisseau ou Station --}}
  @if(isset($personnage) && $personnage->dans_station_id)
  <div class="nav-section">
    <div class="nav-section-head">Station</div>
    <a href="{{ route('station.hall') }}"     class="nav-item {{ request()->routeIs('station.hall')     ? 'active' : '' }}">Hall      <span class="nav-cmd">→</span></a>
    <a href="{{ route('station.hangar') }}"   class="nav-item {{ request()->routeIs('station.hangar')   ? 'active' : '' }}">Hangar    <span class="nav-cmd">→</span></a>
    <a href="{{ route('station.marche') }}"   class="nav-item {{ request()->routeIs('station.marche')   ? 'active' : '' }}">Marché    <span class="nav-cmd">→</span></a>
    <a href="{{ route('station.missions') }}" class="nav-item {{ request()->routeIs('station.missions') ? 'active' : '' }}">Missions  <span class="nav-cmd">→</span></a>
    <a href="{{ route('station.cantina') }}"  class="nav-item {{ request()->routeIs('station.cantina')  ? 'active' : '' }}">Cantina   <span class="nav-cmd">→</span></a>
  </div>
  @else
  <div class="nav-section">
    <div class="nav-section-head">Vaisseau</div>
    <a href="{{ route('navire.timonerie') }}"  class="nav-item {{ request()->routeIs('navire.timonerie')  ? 'active' : '' }}">Timonerie  <span class="nav-cmd">→</span></a>
    <a href="{{ route('navire.ingenierie') }}" class="nav-item {{ request()->routeIs('navire.ingenierie') ? 'active' : '' }}">Ingénierie <span class="nav-cmd">→</span></a>
    <a href="{{ route('navire.com') }}"        class="nav-item {{ request()->routeIs('navire.com')        ? 'active' : '' }}">COM        <span class="nav-cmd">→</span></a>
    <a href="{{ route('navire.soute') }}"      class="nav-item {{ request()->routeIs('navire.soute')      ? 'active' : '' }}">Soute      <span class="nav-cmd">→</span></a>
    <a href="{{ route('navire.equipage') }}"   class="nav-item {{ request()->routeIs('navire.equipage')   ? 'active' : '' }}">Équipage   <span class="nav-cmd">→</span></a>
  </div>
  @endif

  {{-- Section Personnage --}}
  <div class="nav-section">
    <div class="nav-section-head">Personnage</div>
    <a href="{{ route('personnage.dossier') }}" class="nav-item {{ request()->routeIs('personnage.dossier') ? 'active' : '' }}">Dossier    <span class="nav-cmd">→</span></a>
    <a href="{{ route('carte') }}"              class="nav-item {{ (request()->routeIs('carte*') || request()->routeIs('personnage.spatiocarte*')) ? 'active' : '' }}">Spatiocarte <span class="nav-cmd">→</span></a>
    <a href="{{ route('personnage.gestion') }}" class="nav-item {{ request()->routeIs('personnage.gestion') ? 'active' : '' }}">Gestion    <span class="nav-cmd">→</span></a>
  </div>

  {{-- Section Jeu --}}
  <div class="nav-section">
    <div class="nav-section-head">Jeu</div>
    <a href="{{ route('jeu.profil') }}" class="nav-item {{ request()->routeIs('jeu.profil') ? 'active' : '' }}">Profil <span class="nav-cmd">→</span></a>
  </div>

  {{-- Section Administration --}}
  @if(isset($_compte) && ($_compte->is_admin ?? false))
  <div class="nav-section">
    <div class="nav-section-head" style="color:#ef4444;">Administration</div>
    <a href="{{ route('admin.index') }}" class="nav-item" style="color:#f87171;">Univers <span class="nav-cmd">→</span></a>
  </div>
  @endif

</div>

{{-- Pied de nav : infos personnage --}}
@if(isset($personnage))
<div class="nav-footer">
  <div class="nav-footer-meta">NVL {{ $personnage->niveau }} · XP {{ $personnage->experience }}</div>
  <div class="nav-footer-name">{{ $personnage->prenom ?? '' }} {{ $personnage->nom }}</div>
  <a href="{{ route('personnage.selection') }}" class="nav-footer-link">⇌ Personnages</a>
</div>
@endif
