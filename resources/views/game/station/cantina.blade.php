@extends('layouts.game-hud')

@section('title', 'Cantina')

@section('hud-content')
<div class="hud-page-title">Cantina</div>

<div class="hud-panel" style="margin-bottom:16px;border-color:rgba(251,146,60,0.3);">
    <div class="hud-panel-title" style="color:var(--accent);">Pilotes présents</div>
    <div style="display:flex;align-items:center;gap:12px;padding:12px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);">
        <div style="font-size:24px;">👤</div>
        <div>
            <div style="font-size:13px;font-weight:600;color:var(--text-primary);">{{ $personnage->nom }}</div>
            <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);margin-top:2px;">C'est vous</div>
        </div>
    </div>
    <p style="margin-top:10px;color:var(--text-muted);font-style:italic;font-size:12px;">Système multijoueur en développement...</p>
</div>

<div class="hud-panel" style="margin-bottom:16px;">
    <div class="hud-panel-title">Rumeurs & Informations</div>
    <p style="color:var(--text-muted);font-style:italic;font-size:12px;">Système de rumeurs en développement...</p>
</div>

<div class="hud-panel" style="border-color:rgba(167,139,250,0.3);">
    <div class="hud-panel-title" style="color:rgba(167,139,250,0.8);">Chat Local</div>
    <p style="color:var(--text-muted);font-style:italic;font-size:12px;">Système de chat en développement...</p>
</div>
@endsection
