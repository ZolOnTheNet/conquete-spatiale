@extends('layouts.game-hud')

@section('title', 'Marché')

@section('hud-content')
<div class="hud-page-title">Marché</div>

{{-- Crédits --}}
<div class="hud-panel" style="margin-bottom:16px;border-color:rgba(251,191,36,0.3);">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <span style="color:var(--text-muted);font-family:var(--mono);font-size:10px;letter-spacing:0.1em;text-transform:uppercase;">Vos crédits</span>
        <span style="color:var(--accent);font-family:var(--mono);font-size:18px;font-weight:700;">{{ number_format($personnage->credits ?? 0, 0, ',', ' ') }} CR</span>
    </div>
</div>

{{-- Marchandises --}}
<div class="hud-panel" style="margin-bottom:16px;border-color:rgba(74,222,128,0.3);">
    <div class="hud-panel-title" style="color:var(--success);">Marchandises Disponibles</div>
    <p style="color:var(--text-muted);font-style:italic;font-size:12px;">Système de commerce en développement...</p>
</div>

{{-- Cargaison --}}
<div class="hud-panel">
    <div class="hud-panel-title">Votre Cargaison</div>
    <p style="color:var(--text-secondary);font-size:12px;">Accédez à votre soute depuis le menu Vaisseau.</p>
</div>
@endsection
