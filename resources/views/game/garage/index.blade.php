@extends('layouts.game-hud')

@section('title', 'Garage')

@section('hud-content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <div class="hud-page-title" style="margin:0;">Garage — {{ $station->nom }}</div>
    <a href="{{ route('station.hangar') }}" style="font-family:var(--mono);font-size:10px;letter-spacing:0.1em;text-transform:uppercase;padding:5px 12px;background:transparent;color:var(--text-muted);border:1px solid var(--border-subtle);text-decoration:none;transition:all 0.15s;" onmouseover="this.style.color='var(--text-primary)';this.style.borderColor='var(--border-strong)'" onmouseout="this.style.color='var(--text-muted)';this.style.borderColor='var(--border-subtle)'">
        ← Hangar
    </a>
</div>

{{-- État du vaisseau --}}
<div class="hud-panel" style="margin-bottom:16px;border-color:rgba(255,138,61,0.3);">
    <div class="hud-panel-title" style="color:var(--accent);">État du Vaisseau</div>
    @php $nomVaisseau = $vaisseau->objetSpatial->nom ?? 'Vaisseau sans nom'; @endphp
    <div style="padding:14px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);">
        <div style="font-size:14px;font-weight:700;color:var(--text-primary);margin-bottom:12px;">{{ $nomVaisseau }}</div>

        {{-- Barre coque --}}
        <div style="margin-bottom:10px;">
            <div style="display:flex;justify-content:space-between;font-family:var(--mono);font-size:10px;color:var(--text-muted);margin-bottom:4px;">
                <span>Intégrité Coque</span>
                <span>{{ $diagnostics['coque_actuelle'] }}/{{ $diagnostics['coque_max'] }} ({{ round($diagnostics['pourcentage_coque']) }}%)</span>
            </div>
            <div style="height:5px;background:rgba(125,165,200,0.1);border:1px solid var(--border-subtle);">
                <div style="height:100%;width:{{ $diagnostics['pourcentage_coque'] }}%;background:{{ $diagnostics['pourcentage_coque'] > 60 ? 'var(--success)' : ($diagnostics['pourcentage_coque'] > 30 ? 'var(--warning)' : 'var(--danger)') }};"></div>
            </div>
        </div>

        @if($diagnostics['dommages_coque'] > 0)
        <div style="padding:8px 10px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.3);margin-bottom:8px;">
            <span style="color:var(--danger);font-family:var(--mono);font-size:10px;">⚠ {{ $diagnostics['dommages_coque'] }} points de dommages structurels</span>
        </div>
        @endif

        @if($diagnostics['nb_pannes'] > 0)
        <div style="padding:8px 10px;background:rgba(251,191,36,0.06);border:1px solid rgba(251,191,36,0.3);">
            <div style="color:var(--warning);font-family:var(--mono);font-size:10px;margin-bottom:6px;">⚠ {{ $diagnostics['nb_pannes'] }} panne(s) système</div>
            @foreach($diagnostics['pannes'] as $panneId => $panne)
            <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">• {{ $panne['nom'] ?? 'Panne inconnue' }} — Complexité {{ $panne['complexite'] ?? 1 }}</div>
            @endforeach
        </div>
        @endif

        @if($diagnostics['dommages_coque'] == 0 && $diagnostics['nb_pannes'] == 0)
        <div style="padding:8px 10px;background:rgba(74,222,128,0.06);border:1px solid rgba(74,222,128,0.3);">
            <span style="color:var(--success);font-family:var(--mono);font-size:10px;">✓ Vaisseau en parfait état — Aucune réparation nécessaire</span>
        </div>
        @endif
    </div>
</div>

{{-- Services de réparation --}}
<div class="hud-panel" style="margin-bottom:16px;">
    <div class="hud-panel-title">Services de Réparation</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">

        {{-- Coque --}}
        <div style="padding:14px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);">
            <div style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:6px;">🛡️ Coque</div>
            <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);margin-bottom:10px;">Répare tous les dommages structurels</div>
            @if($diagnostics['dommages_coque'] > 0)
            <div style="color:var(--warning);font-family:var(--mono);font-size:11px;margin-bottom:8px;">{{ number_format($diagnostics['cout_coque'], 0, ',', ' ') }} CR</div>
            <form method="POST" action="{{ route('garage.reparer-coque') }}">
                @csrf
                <button type="submit" style="width:100%;font-family:var(--mono);font-size:10px;letter-spacing:0.1em;text-transform:uppercase;padding:6px;background:transparent;color:var(--success);border:1px solid var(--success);cursor:pointer;transition:all 0.15s;" onmouseover="this.style.background='rgba(74,222,128,0.1)'" onmouseout="this.style.background='transparent'">Réparer la coque</button>
            </form>
            @else
            <div style="color:var(--success);font-family:var(--mono);font-size:10px;">✓ Coque en parfait état</div>
            @endif
        </div>

        {{-- Systèmes --}}
        <div style="padding:14px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);">
            <div style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:6px;">⚙️ Systèmes</div>
            <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);margin-bottom:10px;">Répare les pannes système</div>
            @if($diagnostics['nb_pannes'] > 0)
            <div style="color:var(--warning);font-family:var(--mono);font-size:11px;margin-bottom:8px;">{{ number_format($diagnostics['cout_pannes'], 0, ',', ' ') }} CR</div>
            @foreach($diagnostics['pannes'] as $panneId => $panne)
            <form method="POST" action="{{ route('garage.reparer-panne') }}" style="margin-bottom:4px;">
                @csrf
                <input type="hidden" name="panne_id" value="{{ $panneId }}">
                <button type="submit" style="width:100%;font-family:var(--mono);font-size:10px;letter-spacing:0.08em;padding:5px;background:transparent;color:var(--warning);border:1px solid var(--warning);cursor:pointer;transition:all 0.15s;" onmouseover="this.style.background='rgba(251,191,36,0.1)'" onmouseout="this.style.background='transparent'">{{ $panne['nom'] ?? 'Panne' }}</button>
            </form>
            @endforeach
            @else
            <div style="color:var(--success);font-family:var(--mono);font-size:10px;">✓ Systèmes fonctionnels</div>
            @endif
        </div>
    </div>
</div>

{{-- Réparation complète --}}
@if($diagnostics['cout_total'] > 0)
<div style="padding:20px 24px;background:linear-gradient(135deg,rgba(167,139,250,0.08),rgba(96,165,250,0.06));border:1px solid rgba(167,139,250,0.3);display:flex;justify-content:space-between;align-items:center;">
    <div>
        <div style="font-size:14px;font-weight:700;color:#a78bfa;margin-bottom:4px;">⚡ Réparation Complète</div>
        <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);margin-bottom:8px;">Répare tous les dommages et pannes en une seule intervention</div>
        <div style="color:var(--warning);font-family:var(--mono);font-size:14px;font-weight:700;">{{ number_format($diagnostics['cout_total'], 0, ',', ' ') }} CR</div>
    </div>
    <form method="POST" action="{{ route('garage.reparer-tout') }}">
        @csrf
        <button type="submit" style="font-family:var(--mono);font-size:11px;letter-spacing:0.1em;text-transform:uppercase;padding:10px 20px;background:transparent;color:#a78bfa;border:1px solid #a78bfa;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.background='rgba(167,139,250,0.1)'" onmouseout="this.style.background='transparent'">🔧 Tout Réparer</button>
    </form>
</div>
@endif
@endsection
