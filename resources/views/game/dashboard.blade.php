@extends('layouts.game-hud')

@section('title', 'Tableau de bord')

@section('hud-content')
<div class="hud-page-title">Tableau de bord</div>

{{-- Message bienvenue --}}
<div class="hud-panel" style="margin-bottom:16px;text-align:center;padding:28px;">
    <div style="font-size:14px;color:var(--text-secondary);margin-bottom:8px;">
        Bienvenue, <span style="color:var(--text-primary);font-weight:700;">{{ $personnage->prenom ?? '' }} {{ $personnage->nom }}</span>
    </div>
    <div style="font-family:var(--mono);font-size:11px;color:var(--text-muted);">
        Utilisez la navigation à gauche ou la console pour piloter votre vaisseau.
    </div>
    <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);margin-top:6px;">
        Tapez <span style="color:var(--data);">help</span> pour voir les commandes disponibles.
    </div>
</div>

{{-- Stats rapides --}}
@if($vaisseau)
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
    <div class="hud-panel">
        <div class="hud-panel-title">Vaisseau</div>
        <div style="font-size:14px;font-weight:700;color:var(--text-primary);">{{ $vaisseau->nom }}</div>
        <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);margin-top:2px;">{{ $vaisseau->modele ?? 'Scout' }}</div>
    </div>
    <div class="hud-panel">
        <div class="hud-panel-title">Position</div>
        @if($vaisseau->objetSpatial)
        <div style="font-family:var(--mono);font-size:11px;color:var(--data);">
            Secteur ({{ $vaisseau->objetSpatial->secteur_x }}, {{ $vaisseau->objetSpatial->secteur_y }}, {{ $vaisseau->objetSpatial->secteur_z }})
        </div>
        @if($systeme ?? null)
        <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);margin-top:2px;">{{ $systeme->nom }}</div>
        @endif
        @else
        <div style="font-family:var(--mono);font-size:11px;color:var(--text-muted);">Position inconnue</div>
        @endif
    </div>
</div>
@endif

{{-- Accès rapides --}}
<div class="hud-panel">
    <div class="hud-panel-title">Accès Rapides</div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
        <a href="{{ route('navire.timonerie') }}" style="display:block;padding:10px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);text-decoration:none;text-align:center;transition:border-color 0.12s;" onmouseover="this.style.borderColor='var(--data)'" onmouseout="this.style.borderColor='var(--border-subtle)'">
            <div style="font-size:18px;margin-bottom:4px;">🚀</div>
            <div style="font-family:var(--mono);font-size:10px;color:var(--text-secondary);">Timonerie</div>
        </a>
        <a href="{{ route('carte') }}" style="display:block;padding:10px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);text-decoration:none;text-align:center;transition:border-color 0.12s;" onmouseover="this.style.borderColor='var(--data)'" onmouseout="this.style.borderColor='var(--border-subtle)'">
            <div style="font-size:18px;margin-bottom:4px;">🗺️</div>
            <div style="font-family:var(--mono);font-size:10px;color:var(--text-secondary);">Carte</div>
        </a>
        <a href="{{ route('personnage.dossier') }}" style="display:block;padding:10px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);text-decoration:none;text-align:center;transition:border-color 0.12s;" onmouseover="this.style.borderColor='var(--data)'" onmouseout="this.style.borderColor='var(--border-subtle)'">
            <div style="font-size:18px;margin-bottom:4px;">📋</div>
            <div style="font-family:var(--mono);font-size:10px;color:var(--text-secondary);">Dossier</div>
        </a>
    </div>
</div>
@endsection
