@extends('layouts.game-hud')

@section('title', 'Dossier Personnage')

@section('hud-content')
<div class="hud-page-title">Dossier Personnage</div>

{{-- Infos générales --}}
<div class="hud-panel" style="margin-bottom:16px;">
    <div class="hud-panel-title">Informations générales</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-family:var(--mono);font-size:11px;">
        <div><span style="color:var(--text-muted);">Nom</span><div style="font-size:13px;font-weight:700;color:var(--text-primary);">{{ $personnage->nom }}</div></div>
        <div><span style="color:var(--text-muted);">Crédits</span><div style="color:var(--accent);">{{ number_format($personnage->credits ?? 0, 0, ',', ' ') }} CR</div></div>
        <div><span style="color:var(--text-muted);">Points d'action</span><div style="color:var(--data);">{{ $personnage->points_action ?? 0 }} PA</div></div>
        <div><span style="color:var(--text-muted);">Niveau</span><div style="color:var(--success);">{{ $personnage->niveau ?? 1 }}</div></div>
    </div>
</div>

{{-- Compétences --}}
<div class="hud-panel" style="margin-bottom:16px;border-color:rgba(251,191,36,0.3);">
    <div class="hud-panel-title" style="color:var(--warning);">Compétences</div>
    <p style="color:var(--text-muted);font-style:italic;font-size:12px;">Système de compétences en développement...</p>
</div>

{{-- Historique --}}
<div class="hud-panel" style="border-color:rgba(167,139,250,0.3);">
    <div class="hud-panel-title" style="color:rgba(167,139,250,0.8);">Historique</div>
    <p style="color:var(--text-muted);font-style:italic;font-size:12px;">Journal de bord en développement...</p>
</div>
@endsection
