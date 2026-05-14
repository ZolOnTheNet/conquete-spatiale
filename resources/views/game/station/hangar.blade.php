@extends('layouts.game-hud')

@section('title', 'Hangar')

@section('hud-content')
<div class="hud-page-title">Hangar</div>

{{-- État du vaisseau --}}
<div class="hud-panel" style="margin-bottom:16px;border-color:rgba(251,191,36,0.3);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <div class="hud-panel-title" style="margin:0;color:var(--warning);">Votre Vaisseau</div>
        @if($personnage->vaisseauActif)
        <form method="POST" action="{{ route('station.embarquer') }}">
            @csrf
            <button type="submit" style="font-family:var(--mono);font-size:10px;letter-spacing:0.1em;text-transform:uppercase;padding:5px 12px;background:transparent;color:var(--data);border:1px solid var(--data);cursor:pointer;transition:all 0.15s;" onmouseover="this.style.background='rgba(127,212,255,0.1)'" onmouseout="this.style.background='transparent'">
                ▶ Embarquer
            </button>
        </form>
        @endif
    </div>

    @if($personnage->vaisseauActif)
    @php
        $vaisseau   = $personnage->vaisseauActif;
        $nomVaisseau = $vaisseau->objetSpatial->nom ?? 'Vaisseau sans nom';
    @endphp
    <div style="padding:14px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);">
        <div style="font-size:14px;font-weight:700;color:var(--text-primary);margin-bottom:10px;">{{ $nomVaisseau }}</div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;font-family:var(--mono);font-size:11px;">
            <div><div style="color:var(--text-muted);">Coque</div><div style="color:var(--success);">{{ $vaisseau->coque_actuelle ?? 100 }}/{{ $vaisseau->coque_max ?? 100 }}</div></div>
            <div><div style="color:var(--text-muted);">Bouclier</div><div style="color:var(--data);">{{ $vaisseau->bouclier_actuel ?? 0 }}%</div></div>
            <div><div style="color:var(--text-muted);">Énergie</div><div style="color:var(--warning);">{{ $vaisseau->energie_actuelle ?? 0 }}/{{ $vaisseau->reserve ?? 100 }}</div></div>
        </div>
    </div>
    @else
    <p style="color:var(--text-muted);font-size:12px;">Aucun vaisseau actif</p>
    @endif
</div>

{{-- Réparations --}}
<div class="hud-panel" style="margin-bottom:16px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <div class="hud-panel-title" style="margin:0;">Réparations</div>
        @if($personnage->vaisseauActif)
        <a href="{{ route('garage.index') }}" style="font-family:var(--mono);font-size:10px;letter-spacing:0.1em;text-transform:uppercase;padding:5px 12px;background:transparent;color:var(--accent);border:1px solid var(--accent);text-decoration:none;transition:all 0.15s;" onmouseover="this.style.background='rgba(255,138,61,0.1)'" onmouseout="this.style.background='transparent'">
            🔧 Accéder au Garage
        </a>
        @endif
    </div>
    <p style="color:var(--text-secondary);font-size:12px;">Le garage permet de réparer la coque, remplacer des composants et effectuer des maintenances.</p>

    @if($personnage->vaisseauActif)
    @php
        $vaisseau = $personnage->vaisseauActif;
        $dmg = ($vaisseau->coque_max - $vaisseau->coque_actuelle);
        $pannes = is_array($vaisseau->pannes_actuelles) ? count($vaisseau->pannes_actuelles) : 0;
    @endphp
    @if($dmg > 0 || $pannes > 0)
    <div style="margin-top:12px;padding:10px 12px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.3);">
        <div style="color:var(--danger);font-family:var(--mono);font-size:10px;font-weight:700;margin-bottom:6px;">⚠ RÉPARATIONS NÉCESSAIRES</div>
        @if($dmg > 0)<div style="font-size:12px;color:var(--text-secondary);">• Coque : {{ $dmg }} points à réparer</div>@endif
        @if($pannes > 0)<div style="font-size:12px;color:var(--text-secondary);">• {{ $pannes }} panne(s) système détectée(s)</div>@endif
    </div>
    @else
    <div style="margin-top:12px;padding:10px 12px;background:rgba(74,222,128,0.06);border:1px solid rgba(74,222,128,0.3);">
        <div style="color:var(--success);font-family:var(--mono);font-size:10px;">✓ Vaisseau en parfait état</div>
    </div>
    @endif
    @endif
</div>

{{-- Modules --}}
<div class="hud-panel" style="border-color:rgba(167,139,250,0.3);">
    <div class="hud-panel-title" style="color:rgba(167,139,250,0.8);">Modules & Améliorations</div>
    <p style="color:var(--text-muted);font-style:italic;font-size:12px;">Système d'amélioration en développement...</p>
</div>
@endsection
