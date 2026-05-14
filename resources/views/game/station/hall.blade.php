@extends('layouts.game-hud')

@section('title', 'Hall Principal')

@section('hud-content')
<div class="hud-page-title">Hall Principal</div>

{{-- Informations station --}}
<div class="hud-panel" style="margin-bottom:16px;">
    <div class="hud-panel-title">Station</div>
    @if($station ?? null)
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px;">
        <div>
            <span style="color:var(--text-muted);">Nom</span>
            <div style="color:var(--text-primary);font-weight:600;">{{ $station->nom }}</div>
        </div>
        <div>
            <span style="color:var(--text-muted);">Type</span>
            <div style="color:var(--warning);">{{ $station->type_etoile ?? 'Orbitale' }}</div>
        </div>
    </div>
    @else
    <p style="color:var(--text-muted);font-style:italic;font-size:12px;">Informations indisponibles</p>
    @endif
</div>

{{-- Services --}}
<div class="hud-panel" style="margin-bottom:16px;border-color:rgba(251,191,36,0.3);">
    <div class="hud-panel-title" style="color:var(--warning);">Services Disponibles</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <a href="{{ route('station.hangar') }}" style="display:block;padding:14px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);text-decoration:none;transition:border-color 0.12s;" onmouseover="this.style.borderColor='var(--data)'" onmouseout="this.style.borderColor='var(--border-subtle)'">
            <div style="font-size:20px;margin-bottom:6px;">🛠️</div>
            <div style="font-size:13px;font-weight:600;color:var(--text-primary);">Hangar</div>
            <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);margin-top:2px;">Réparations & Modules</div>
        </a>
        <a href="{{ route('station.marche') }}" style="display:block;padding:14px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);text-decoration:none;transition:border-color 0.12s;" onmouseover="this.style.borderColor='var(--data)'" onmouseout="this.style.borderColor='var(--border-subtle)'">
            <div style="font-size:20px;margin-bottom:6px;">💰</div>
            <div style="font-size:13px;font-weight:600;color:var(--text-primary);">Marché</div>
            <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);margin-top:2px;">Commerce & Négoce</div>
        </a>
        <a href="{{ route('station.missions') }}" style="display:block;padding:14px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);text-decoration:none;transition:border-color 0.12s;" onmouseover="this.style.borderColor='var(--data)'" onmouseout="this.style.borderColor='var(--border-subtle)'">
            <div style="font-size:20px;margin-bottom:6px;">📜</div>
            <div style="font-size:13px;font-weight:600;color:var(--text-primary);">Missions</div>
            <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);margin-top:2px;">Contrats & Quêtes</div>
        </a>
        <a href="{{ route('station.cantina') }}" style="display:block;padding:14px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);text-decoration:none;transition:border-color 0.12s;" onmouseover="this.style.borderColor='var(--data)'" onmouseout="this.style.borderColor='var(--border-subtle)'">
            <div style="font-size:20px;margin-bottom:6px;">🍺</div>
            <div style="font-size:13px;font-weight:600;color:var(--text-primary);">Cantina</div>
            <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);margin-top:2px;">Social & Rumeurs</div>
        </a>
    </div>
</div>

{{-- Actualités --}}
<div class="hud-panel" style="border-color:rgba(167,139,250,0.3);">
    <div class="hud-panel-title" style="color:rgba(167,139,250,0.8);">Actualités</div>
    <p style="color:var(--text-muted);font-style:italic;font-size:12px;">Système d'actualités en développement...</p>
</div>
@endsection
