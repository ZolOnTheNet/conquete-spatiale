@extends('layouts.game-hud')

@section('title', 'Équipage')

@section('hud-content')
<div class="hud-page-title">Équipage</div>

{{-- Capitaine --}}
<div class="hud-panel" style="margin-bottom:16px;border-color:rgba(251,191,36,0.3);">
    <div class="hud-panel-title" style="color:var(--warning);">Capitaine</div>
    <div style="display:flex;align-items:center;gap:16px;padding:12px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);">
        <div style="font-size:32px;">👤</div>
        <div>
            <div style="font-size:15px;font-weight:700;color:var(--text-primary);">{{ $personnage->nom }}</div>
            <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);margin-top:2px;">Commandant · Niveau {{ $personnage->niveau ?? 1 }}</div>
        </div>
    </div>
</div>

{{-- Membres --}}
<div class="hud-panel" style="margin-bottom:16px;">
    <div class="hud-panel-title">Membres d'équipage</div>
    <p style="color:var(--text-secondary);font-style:italic;font-size:12px;">Système de recrutement en développement...</p>
    <p style="margin-top:6px;font-family:var(--mono);font-size:10px;color:var(--text-muted);">Les membres pourront être recrutés en station et amélioreront les capacités du vaisseau.</p>
</div>

{{-- Capacité --}}
<div class="hud-panel" style="border-color:rgba(167,139,250,0.3);">
    <div class="hud-panel-title" style="color:rgba(167,139,250,0.8);">Capacité du vaisseau</div>
    @php $cap = $vaisseau->capacite_equipage ?? 10; @endphp
    <div style="margin-bottom:8px;">
        <div style="display:flex;justify-content:space-between;font-family:var(--mono);font-size:10px;color:var(--text-muted);margin-bottom:4px;">
            <span>Équipage</span>
            <span>1 / {{ $cap }}</span>
        </div>
        <div style="height:4px;background:rgba(125,165,200,0.1);border:1px solid var(--border-subtle);">
            <div style="height:100%;width:{{ round(1/$cap*100) }}%;background:var(--success);"></div>
        </div>
    </div>
    <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);">{{ $cap - 1 }} place(s) disponible(s)</div>
</div>
@endsection
