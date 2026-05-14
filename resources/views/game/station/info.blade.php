@extends('layouts.game-hud')

@section('title', $station->nom . ' — Informations')

@section('hud-content')
{{-- En-tête station --}}
<div style="padding:20px 24px;margin-bottom:16px;background:linear-gradient(135deg,rgba(127,212,255,0.08),rgba(167,139,250,0.06));border:1px solid var(--border-subtle);">
    <div style="font-family:var(--sans);font-size:22px;font-weight:700;color:var(--data);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:4px;">🏭 {{ $station->nom }}</div>
    <div style="color:var(--text-secondary);font-size:13px;">{{ $station->description }}</div>
</div>

{{-- Infos générales --}}
<div class="hud-panel" style="margin-bottom:16px;">
    <div class="hud-panel-title">Informations Générales</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-family:var(--mono);font-size:11px;">
        <div><span style="color:var(--text-muted);">Type</span><div style="color:var(--data);">{{ ucfirst($station->type) }}</div></div>
        <div><span style="color:var(--text-muted);">Amarrage</span><div style="color:var(--success);">{{ $station->capacite_amarrage }} vaisseaux</div></div>
        @if($station->planete_id)
        <div><span style="color:var(--text-muted);">Orbite</span><div style="color:var(--warning);">{{ $station->planete->nom }}</div></div>
        <div><span style="color:var(--text-muted);">Rayon</span><div style="color:var(--text-primary);">{{ number_format($station->orbite_rayon_ua, 3) }} UA</div></div>
        @elseif($station->systeme_stellaire_id)
        <div><span style="color:var(--text-muted);">Système</span><div style="color:var(--warning);">{{ $station->systemeStellaire->nom }}</div></div>
        @endif
        @if($station->faction_id)
        <div><span style="color:var(--text-muted);">Faction</span><div style="color:rgba(167,139,250,0.9);">{{ $station->faction->nom }}</div></div>
        @if($station->reputation_requise > 0)
        <div><span style="color:var(--text-muted);">Réputation requise</span><div style="color:var(--accent);">{{ $station->reputation_requise }}</div></div>
        @endif
        @endif
    </div>
</div>

{{-- Services --}}
<div class="hud-panel" style="margin-bottom:16px;">
    <div class="hud-panel-title">Services Disponibles</div>
    <div style="display:flex;flex-wrap:wrap;gap:12px;font-size:12px;">
        @if($station->commerciale)  <div style="color:var(--success);">🛒 Marché</div> @endif
        @if($station->reparations)  <div style="color:var(--accent);">🔧 Garage</div> @endif
        @if($station->ravitaillement)<div style="color:#60a5fa;">⛽ Ravitaillement</div> @endif
        @if($station->medical)      <div style="color:var(--danger);">🏥 Hôpital</div> @endif
        @if($station->industrielle) <div style="color:#a78bfa;">🏭 Industrie</div> @endif
        @if($station->militaire)    <div style="color:var(--warning);">⚔️ Militaire</div> @endif
        <div style="color:var(--data);">📋 Comptoirs</div>
    </div>
</div>

{{-- Accessibilité --}}
<div class="hud-panel" style="margin-bottom:20px;border-color:{{ $station->accessible ? 'rgba(74,222,128,0.3)' : 'rgba(239,68,68,0.3)' }};">
    <div class="hud-panel-title" style="color:{{ $station->accessible ? 'var(--success)' : 'var(--danger)' }};">
        {{ $station->accessible ? '✅ Station Accessible' : '❌ Station Inaccessible' }}
    </div>
    @if(!$station->accessible && $station->raison_inaccessible)
    <p style="color:var(--text-secondary);font-size:12px;">{{ $station->raison_inaccessible }}</p>
    @elseif($station->accessible)
    <p style="color:var(--text-secondary);font-size:12px;">Cette station est ouverte à tous les vaisseaux.</p>
    @endif
</div>

<a href="{{ route('carte') }}" style="font-family:var(--mono);font-size:10px;letter-spacing:0.1em;color:var(--data);text-decoration:none;transition:color 0.12s;" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--data)'">
    ← Retour à la carte
</a>
@endsection
