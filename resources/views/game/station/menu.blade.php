@extends('layouts.game-hud')

@section('title', $station->nom . ' — Station')

@section('hud-content')
{{-- En-tête station --}}
<div style="padding:20px 24px;margin-bottom:20px;background:linear-gradient(135deg,rgba(127,212,255,0.08),rgba(167,139,250,0.06));border:1px solid var(--border-subtle);">
    <div style="font-family:var(--sans);font-size:22px;font-weight:700;color:var(--data);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:4px;">🏭 {{ $station->nom }}</div>
    <div style="color:var(--text-secondary);font-size:13px;">{{ $station->description }}</div>
    <div style="margin-top:12px;display:flex;gap:20px;font-family:var(--mono);font-size:10px;color:var(--text-muted);">
        <span>TYPE: <span style="color:var(--data);">{{ strtoupper($station->type) }}</span></span>
        <span>AMARRAGE: <span style="color:var(--success);">{{ $station->capacite_amarrage }} vaisseaux</span></span>
        @if($station->planete_id)<span>ORBITE: <span style="color:var(--warning);">{{ $station->planete->nom }}</span></span>@endif
    </div>
</div>

{{-- Services --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;margin-bottom:20px;">
    @if($station->commerciale)
    <a href="{{ route('marche.index') }}" style="display:block;padding:16px;background:rgba(5,7,12,0.4);border:1px solid rgba(74,222,128,0.3);text-decoration:none;transition:border-color 0.12s;" onmouseover="this.style.borderColor='var(--success)'" onmouseout="this.style.borderColor='rgba(74,222,128,0.3)'">
        <div style="font-size:22px;margin-bottom:8px;">🛒</div>
        <div style="font-size:13px;font-weight:700;color:var(--success);margin-bottom:4px;">Marché</div>
        <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);">Commerce</div>
        <div style="margin-top:8px;font-family:var(--mono);font-size:9px;color:var(--success);text-align:right;">Accéder →</div>
    </a>
    @endif

    @if($station->reparations)
    <a href="{{ route('garage.index') }}" style="display:block;padding:16px;background:rgba(5,7,12,0.4);border:1px solid rgba(255,138,61,0.3);text-decoration:none;transition:border-color 0.12s;" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='rgba(255,138,61,0.3)'">
        <div style="font-size:22px;margin-bottom:8px;">🔧</div>
        <div style="font-size:13px;font-weight:700;color:var(--accent);margin-bottom:4px;">Garage</div>
        <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);">Réparations</div>
        <div style="margin-top:8px;font-family:var(--mono);font-size:9px;color:var(--accent);text-align:right;">Accéder →</div>
    </a>
    @endif

    @if($station->ravitaillement)
    <a href="{{ route('ravitaillement.index') }}" style="display:block;padding:16px;background:rgba(5,7,12,0.4);border:1px solid rgba(96,165,250,0.3);text-decoration:none;transition:border-color 0.12s;" onmouseover="this.style.borderColor='#60a5fa'" onmouseout="this.style.borderColor='rgba(96,165,250,0.3)'">
        <div style="font-size:22px;margin-bottom:8px;">⛽</div>
        <div style="font-size:13px;font-weight:700;color:#60a5fa;margin-bottom:4px;">Ravitaillement</div>
        <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);">Carburant</div>
        <div style="margin-top:8px;font-family:var(--mono);font-size:9px;color:#60a5fa;text-align:right;">Accéder →</div>
    </a>
    @endif

    @if($station->medical)
    <a href="{{ route('station.hopital') }}" style="display:block;padding:16px;background:rgba(5,7,12,0.4);border:1px solid rgba(239,68,68,0.3);text-decoration:none;transition:border-color 0.12s;" onmouseover="this.style.borderColor='var(--danger)'" onmouseout="this.style.borderColor='rgba(239,68,68,0.3)'">
        <div style="font-size:22px;margin-bottom:8px;">🏥</div>
        <div style="font-size:13px;font-weight:700;color:var(--danger);margin-bottom:4px;">Hôpital</div>
        <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);">Soins médicaux</div>
        <div style="margin-top:8px;font-family:var(--mono);font-size:9px;color:var(--danger);text-align:right;">Accéder →</div>
    </a>
    @endif

    @if($station->industrielle)
    <a href="{{ route('station.industrie') }}" style="display:block;padding:16px;background:rgba(5,7,12,0.4);border:1px solid rgba(167,139,250,0.3);text-decoration:none;transition:border-color 0.12s;" onmouseover="this.style.borderColor='#a78bfa'" onmouseout="this.style.borderColor='rgba(167,139,250,0.3)'">
        <div style="font-size:22px;margin-bottom:8px;">🏭</div>
        <div style="font-size:13px;font-weight:700;color:#a78bfa;margin-bottom:4px;">Industrie</div>
        <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);">Raffinage</div>
        <div style="margin-top:8px;font-family:var(--mono);font-size:9px;color:#a78bfa;text-align:right;">Accéder →</div>
    </a>
    @endif

    <a href="{{ route('station.missions') }}" style="display:block;padding:16px;background:rgba(5,7,12,0.4);border:1px solid rgba(251,191,36,0.3);text-decoration:none;transition:border-color 0.12s;" onmouseover="this.style.borderColor='var(--warning)'" onmouseout="this.style.borderColor='rgba(251,191,36,0.3)'">
        <div style="font-size:22px;margin-bottom:8px;">📋</div>
        <div style="font-size:13px;font-weight:700;color:var(--warning);margin-bottom:4px;">Comptoirs</div>
        <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);">Missions</div>
        <div style="margin-top:8px;font-family:var(--mono);font-size:9px;color:var(--warning);text-align:right;">Accéder →</div>
    </a>
</div>

{{-- Retour vaisseau --}}
<div class="hud-panel">
    <div class="hud-panel-title">Retour au vaisseau</div>
    <p style="color:var(--text-secondary);margin-bottom:14px;font-size:13px;">Embarquer dans votre vaisseau pour reprendre le voyage.</p>
    <form action="{{ route('station.embarquer') }}" method="POST">
        @csrf
        <button type="submit" style="font-family:var(--mono);font-size:11px;letter-spacing:0.1em;text-transform:uppercase;padding:8px 20px;background:transparent;color:var(--data);border:1px solid var(--data);cursor:pointer;transition:all 0.15s;" onmouseover="this.style.background='rgba(127,212,255,0.1)'" onmouseout="this.style.background='transparent'">
            ▶ Embarquer dans {{ $personnage->vaisseauActif->modele ?? 'votre vaisseau' }}
        </button>
    </form>
</div>
@endsection
