@extends('layouts.game-hud')

@section('title', 'Bureau des Missions')

@section('hud-content')
<div class="hud-page-title">Bureau des Missions</div>

<div class="hud-panel" style="margin-bottom:16px;border-color:rgba(251,191,36,0.3);">
    <div class="hud-panel-title" style="color:var(--warning);">Missions en cours</div>
    <p style="color:var(--text-muted);font-style:italic;font-size:12px;">Aucune mission active</p>
</div>

<div class="hud-panel" style="margin-bottom:16px;border-color:rgba(167,139,250,0.3);">
    <div class="hud-panel-title" style="color:rgba(167,139,250,0.8);">Missions disponibles</div>
    <p style="color:var(--text-muted);font-style:italic;font-size:12px;">Système de missions en développement...</p>
</div>

<div class="hud-panel">
    <div class="hud-panel-title">Missions complétées</div>
    <p style="color:var(--text-muted);font-style:italic;font-size:12px;">Historique non disponible</p>
</div>
@endsection
