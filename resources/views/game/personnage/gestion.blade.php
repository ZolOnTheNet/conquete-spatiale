@extends('layouts.game-hud')

@section('title', 'Gestion Personnage')

@section('hud-content')
<div class="hud-page-title">Gestion Personnage</div>

{{-- Paramètres --}}
<div class="hud-panel" style="margin-bottom:16px;">
    <div class="hud-panel-title">Paramètres du compte</div>
    <div style="margin-bottom:12px;">
        <div style="font-family:var(--mono);font-size:9px;letter-spacing:0.1em;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Nom du personnage</div>
        <input type="text" value="{{ $personnage->nom }}" disabled
               style="width:100%;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);color:var(--text-primary);font-family:var(--mono);font-size:12px;padding:6px 10px;outline:none;" />
    </div>
    <p style="font-family:var(--mono);font-size:10px;color:var(--text-muted);font-style:italic;">La gestion du nom et des paramètres sera disponible prochainement.</p>
</div>

{{-- Vaisseaux --}}
<div class="hud-panel" style="margin-bottom:16px;border-color:rgba(251,191,36,0.3);">
    <div class="hud-panel-title" style="color:var(--warning);">Vaisseaux possédés</div>
    @if($personnage->vaisseauActif)
    <div style="padding:12px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);display:flex;justify-content:space-between;align-items:center;">
        <div>
            <div style="font-size:13px;font-weight:700;color:var(--text-primary);">{{ $personnage->vaisseauActif->nom ?? 'Vaisseau sans nom' }}</div>
            <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);margin-top:2px;">{{ $personnage->vaisseauActif->modele ?? 'Modèle inconnu' }}</div>
        </div>
        <span style="font-family:var(--mono);font-size:9px;letter-spacing:0.1em;color:var(--success);padding:3px 8px;border:1px solid rgba(74,222,128,0.4);">✓ ACTIF</span>
    </div>
    @else
    <p style="color:var(--text-muted);font-style:italic;font-size:12px;">Aucun vaisseau actif</p>
    @endif
</div>

{{-- Zone dangereuse --}}
<div class="hud-panel" style="border-color:rgba(239,68,68,0.3);">
    <div class="hud-panel-title" style="color:var(--danger);">Zone dangereuse</div>
    <p style="color:var(--text-muted);font-size:12px;margin-bottom:12px;">Ces actions sont irréversibles.</p>
    <button disabled style="font-family:var(--mono);font-size:10px;letter-spacing:0.1em;text-transform:uppercase;padding:6px 14px;background:rgba(239,68,68,0.1);color:rgba(239,68,68,0.4);border:1px solid rgba(239,68,68,0.2);cursor:not-allowed;">
        Supprimer le personnage (désactivé)
    </button>
</div>
@endsection
