@extends('layouts.game-hud')

@section('title', 'Profil Joueur')

@section('hud-content')
<div class="hud-page-title">Profil Joueur</div>

{{-- Compte --}}
<div class="hud-panel" style="margin-bottom:16px;">
    <div class="hud-panel-title">Compte</div>
    <div style="display:flex;flex-direction:column;gap:8px;font-family:var(--mono);font-size:11px;">
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border-subtle);">
            <span style="color:var(--text-muted);">Email</span>
            <span style="color:var(--text-primary);">{{ $compte->email }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border-subtle);">
            <span style="color:var(--text-muted);">Inscription</span>
            <span style="color:var(--text-primary);">{{ $compte->created_at->format('d/m/Y') }}</span>
        </div>
        @if($compte->is_admin)
        <div style="display:flex;justify-content:space-between;padding:6px 0;">
            <span style="color:var(--text-muted);">Rôle</span>
            <span style="color:var(--warning);font-weight:700;">⭐ Administrateur</span>
        </div>
        @endif
    </div>
</div>

{{-- Personnages --}}
<div class="hud-panel" style="margin-bottom:16px;border-color:rgba(251,191,36,0.3);">
    <div class="hud-panel-title" style="color:var(--warning);">Personnages</div>
    @if($compte->personnages && $compte->personnages->count() > 0)
    <div style="display:flex;flex-direction:column;gap:8px;">
        @foreach($compte->personnages as $perso)
        <div style="padding:12px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <div style="font-size:13px;font-weight:700;color:var(--text-primary);">{{ $perso->nom }}</div>
                <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);margin-top:3px;">
                    Nvl {{ $perso->niveau ?? 1 }} · {{ number_format($perso->credits ?? 0, 0, ',', ' ') }} CR · {{ $perso->points_action ?? 0 }}/{{ $perso->max_points_action ?? 36 }} PA
                </div>
            </div>
            @if($perso->id === $personnage->id)
            <span style="font-family:var(--mono);font-size:9px;letter-spacing:0.1em;color:var(--success);padding:3px 8px;border:1px solid rgba(74,222,128,0.4);">✓ ACTIF</span>
            @endif
        </div>
        @endforeach
    </div>
    @else
    <p style="color:var(--text-muted);font-style:italic;font-size:12px;">Aucun personnage</p>
    @endif
</div>

{{-- Statistiques --}}
<div class="hud-panel" style="margin-bottom:16px;border-color:rgba(167,139,250,0.3);">
    <div class="hud-panel-title" style="color:rgba(167,139,250,0.8);">Statistiques</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div style="padding:14px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);text-align:center;">
            <div style="font-family:var(--mono);font-size:9px;color:var(--text-muted);text-transform:uppercase;margin-bottom:6px;">Systèmes découverts</div>
            <div style="font-size:24px;font-weight:700;color:var(--data);">{{ $personnage->decouvertes()->count() ?? 0 }}</div>
        </div>
        <div style="padding:14px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);text-align:center;">
            <div style="font-family:var(--mono);font-size:9px;color:var(--text-muted);text-transform:uppercase;margin-bottom:6px;">Distance parcourue</div>
            <div style="font-size:24px;font-weight:700;color:var(--warning);">—</div>
            <div style="font-family:var(--mono);font-size:9px;color:var(--text-muted);">En développement</div>
        </div>
    </div>
</div>
@endsection
