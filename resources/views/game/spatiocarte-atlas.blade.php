@extends('layouts.game-hud')

@section('title', 'Atlas Spatial')

@section('hud-content')
<div class="hud-page-title">Spatiocarte</div>

{{-- Onglets --}}
<div style="display:flex;margin-bottom:16px;border:1px solid var(--border-subtle);">
    <a href="{{ route('carte.3d') }}" style="padding:8px 20px;font-family:var(--mono);font-size:10px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);text-decoration:none;border-right:1px solid var(--border-subtle);transition:color 0.12s;" onmouseover="this.style.color='var(--data)'" onmouseout="this.style.color='var(--text-muted)'">◈ Carte 3D</a>
    <a href="{{ route('carte') }}" style="padding:8px 20px;font-family:var(--mono);font-size:10px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);text-decoration:none;border-right:1px solid var(--border-subtle);transition:color 0.12s;" onmouseover="this.style.color='var(--data)'" onmouseout="this.style.color='var(--text-muted)'">🗺 Carte</a>
    <span style="padding:8px 20px;font-family:var(--mono);font-size:10px;letter-spacing:0.1em;text-transform:uppercase;color:var(--data);border-right:1px solid var(--border-subtle);">📚 Atlas</span>
</div>

{{-- Tableau des découvertes --}}
<div class="hud-panel">
    <div class="hud-panel-title" style="display:flex;justify-content:space-between;align-items:center;">
        <span>Systèmes découverts</span>
        <span style="color:var(--data);font-family:var(--mono);font-size:12px;">{{ $decouvertes->count() }}</span>
    </div>

    @if($decouvertes->count() > 0)
    <div style="overflow-x:auto;">
        <table style="width:100%;font-family:var(--mono);font-size:10px;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--border-subtle);">
                    <th style="padding:6px 10px;text-align:left;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Nom</th>
                    <th style="padding:6px 10px;text-align:left;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Type</th>
                    <th style="padding:6px 10px;text-align:left;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Coordonnées</th>
                    <th style="padding:6px 10px;text-align:left;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Distance</th>
                    <th style="padding:6px 10px;text-align:left;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Puissance</th>
                    <th style="padding:6px 10px;text-align:left;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Planètes</th>
                    <th style="padding:6px 10px;text-align:left;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Ressources</th>
                </tr>
            </thead>
            <tbody>
                @foreach($decouvertes as $data)
                    @php
                        $systeme = $data['systeme'];
                        $decouverte = $data['decouverte'];
                        $ressources = $systeme->planetes->flatMap->gisements->pluck('ressource.nom')->unique();
                    @endphp
                    <tr style="border-bottom:1px solid rgba(125,165,200,0.05);" onmouseover="this.style.background='rgba(125,165,200,0.03)'" onmouseout="this.style.background='transparent'">
                        <td style="padding:7px 10px;">
                            <a href="{{ route('carte', ['x' => $systeme->secteur_x, 'y' => $systeme->secteur_y, 'z' => $systeme->secteur_z]) }}"
                               style="color:var(--warning);text-decoration:none;font-weight:700;"
                               onmouseover="this.style.color='var(--data)'" onmouseout="this.style.color='var(--warning)'">{{ $systeme->nom }}</a>
                        </td>
                        <td style="padding:7px 10px;">
                            @if($decouverte->type_etoile_connu)
                                <span style="padding:2px 6px;background:rgba(167,139,250,0.1);color:rgba(167,139,250,0.85);border:1px solid rgba(167,139,250,0.25);font-size:9px;">{{ $systeme->type_etoile ?? 'N/A' }}</span>
                            @else
                                <span style="color:var(--text-muted);">Inconnu</span>
                            @endif
                        </td>
                        <td style="padding:7px 10px;color:var(--text-secondary);">{{ $data['coords_secteur'] }}</td>
                        <td style="padding:7px 10px;color:var(--data);">{{ number_format($data['distance'], 2) }} AL</td>
                        <td style="padding:7px 10px;color:var(--warning);">{{ $systeme->puissance_solaire ?? '—' }}</td>
                        <td style="padding:7px 10px;">
                            @if($decouverte->nb_planetes_connu)
                                <span style="color:var(--success);">{{ $systeme->planetes->count() }}</span>
                            @else
                                <span style="color:var(--text-muted);">?</span>
                            @endif
                        </td>
                        <td style="padding:7px 10px;">
                            @if($ressources->count() > 0)
                                <span style="color:var(--success);">{{ $ressources->take(2)->implode(', ') }}{{ $ressources->count() > 2 ? ' +'.($ressources->count()-2) : '' }}</span>
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="text-align:center;padding:48px 20px;color:var(--text-muted);">
        <div style="font-size:32px;margin-bottom:12px;">🌌</div>
        <div style="font-size:13px;margin-bottom:6px;">Aucun système découvert</div>
        <div style="font-family:var(--mono);font-size:11px;">Explorez l'univers pour découvrir de nouveaux systèmes stellaires</div>
    </div>
    @endif
</div>
@endsection
