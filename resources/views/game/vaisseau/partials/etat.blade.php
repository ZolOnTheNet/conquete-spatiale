@php
    $nomVaisseau   = $vaisseau->objetSpatial->nom ?? 'Vaisseau sans nom';
    $typePropLabel = match((string)($vaisseau->type_propulsion ?? '')) {
        '1'     => 'Micro-panneaux solaires',
        '2'     => 'Voile solaire',
        '3'     => 'Matière noire',
        default => (string)($vaisseau->type_propulsion ?? '—'),
    };
    $modePropLabel = $vaisseau->mode === 'combustible' ? 'Type A — Combustible' : 'Type B — Énergétique';
    $pctEnergie    = ($vaisseau->reserve ?? 0) > 0 ? round(($vaisseau->energie_actuelle / $vaisseau->reserve) * 100) : 0;
    $pctCoque      = ($vaisseau->coque_max ?? 0) > 0 ? round(($vaisseau->coque_actuelle / $vaisseau->coque_max) * 100) : 100;
    $bouclierMax   = $vaisseau->bouclier?->points_max ?? 0;
    $pctBouclier   = $bouclierMax > 0 ? round(($vaisseau->bouclier_actuel / $bouclierMax) * 100) : 0;
    $pannes        = is_array($vaisseau->pannes_actuelles) ? $vaisseau->pannes_actuelles : [];
    $nbPannes      = count($pannes);
    $cgMax         = $vaisseau->place_soute ?? 0;
    $cgVal         = $vaisseau->masse_variable ?? 0;
    $cgPct         = $cgMax > 0 ? round($cgVal / $cgMax * 100) : 0;
    $cgColor       = $cgPct < 80 ? 'var(--success)' : ($cgPct < 95 ? 'var(--warning)' : 'var(--danger)');
    $emplacements  = is_array($vaisseau->emplacements) ? $vaisseau->emplacements : [];
    $nbModulesCargo = count(array_filter($emplacements, fn($e) => ($e['type'] ?? '') === 'module'));
    $slotsLibres   = max(0, ($vaisseau->max_soutes ?? 0) - $nbModulesCargo);
    $scanFormula   = $vaisseau->getDiceFormula();
    $scanEnCours   = ($vaisseau->scan_niveau_actuel ?? 0) > 0;
@endphp

{{-- Header: identité + alerte pannes --}}
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;">
    <div>
        <div class="hud-page-title" style="margin:0;">Ingénierie</div>
        <div style="font-family:var(--mono);font-size:11px;color:var(--text-muted);margin-top:3px;">
            {{ $nomVaisseau }}
            <span style="color:var(--border-strong);margin:0 6px;">·</span>
            <span style="color:var(--accent);">{{ $vaisseau->modele ?? '—' }}</span>
            <span style="color:var(--border-strong);margin:0 6px;">·</span>
            {{ $modePropLabel }} / {{ $typePropLabel }}
        </div>
    </div>
    @if($nbPannes > 0)
    <div style="padding:6px 14px;background:rgba(251,191,36,0.08);border:1px solid rgba(251,191,36,0.4);font-family:var(--mono);font-size:10px;letter-spacing:0.08em;color:var(--warning);">
        ⚠ {{ $nbPannes }} PANNE(S) ACTIVE(S)
    </div>
    @else
    <div style="padding:6px 14px;background:rgba(74,222,128,0.05);border:1px solid rgba(74,222,128,0.3);font-family:var(--mono);font-size:10px;letter-spacing:0.06em;color:var(--success);">
        ● TOUS SYSTÈMES OPÉRATIONNELS
    </div>
    @endif
</div>

{{-- Jauges de statut --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:16px;">
    @php
    $gauges = [
        ['label' => 'Énergie',  'icon' => '⚡', 'val' => $vaisseau->energie_actuelle ?? 0, 'max' => $vaisseau->reserve ?? 0,   'pct' => $pctEnergie, 'unit' => 'UE',  'color' => null],
        ['label' => 'Coque',    'icon' => '◈',  'val' => $vaisseau->coque_actuelle ?? 0,   'max' => $vaisseau->coque_max ?? 0, 'pct' => $pctCoque,   'unit' => 'US',  'color' => null],
        ['label' => 'Bouclier', 'icon' => '◎',  'val' => $vaisseau->bouclier_actuel ?? 0,  'max' => $bouclierMax,              'pct' => $pctBouclier,'unit' => 'pts', 'color' => null],
        ['label' => 'Cargo',    'icon' => '▣',  'val' => $cgVal,                           'max' => $cgMax,                    'pct' => $cgPct,      'unit' => 't',   'color' => $cgColor],
    ];
    @endphp
    @foreach($gauges as $g)
    @php $gColor = $g['color'] ?? ($g['pct'] > 60 ? 'var(--success)' : ($g['pct'] > 30 ? 'var(--warning)' : 'var(--danger)')); @endphp
    <div style="padding:12px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);">
        <div style="display:flex;justify-content:space-between;align-items:center;font-family:var(--mono);font-size:9px;color:var(--text-muted);margin-bottom:6px;">
            <span>{{ $g['icon'] }} {{ $g['label'] }}</span>
            <span style="color:{{ $gColor }};">{{ $g['pct'] }}%</span>
        </div>
        <div style="height:4px;background:rgba(125,165,200,0.1);border:1px solid rgba(125,165,200,0.08);margin-bottom:6px;">
            <div style="height:100%;width:{{ $g['pct'] }}%;background:{{ $gColor }};"></div>
        </div>
        <div style="font-family:var(--mono);font-size:10px;color:var(--text-primary);">
            {{ $g['val'] }} / {{ $g['max'] }} <span style="color:var(--text-muted);">{{ $g['unit'] }}</span>
        </div>
    </div>
    @endforeach
</div>

{{-- 3 colonnes : Propulsion | Structure & Pannes | Scanner + Armement --}}
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">

    {{-- Propulsion --}}
    <div class="hud-panel">
        <div class="hud-panel-title">Propulsion</div>
        <div style="padding:12px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);">
            <div style="font-size:9px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.12em;margin-bottom:8px;">{{ $typePropLabel }}</div>
            <div style="display:grid;grid-template-columns:max-content 1fr;gap:5px 10px;font-family:var(--mono);font-size:10px;margin-bottom:10px;">
                <span style="color:var(--text-muted);">V. conv.</span>
                <span style="color:var(--text-primary);">{{ $vaisseau->vitesse_conventionnelle ?? '—' }}</span>
                <span style="color:var(--text-muted);">V. saut HE</span>
                <span style="color:var(--text-primary);">{{ $vaisseau->vitesse_saut ?? '—' }}</span>
                <span style="color:var(--text-muted);">Init HE</span>
                <span style="color:var(--text-primary);">{{ $vaisseau->init_hyperespace ?? 200 }} UE</span>
                <span style="color:var(--text-muted);">Coef HE</span>
                <span style="color:var(--text-primary);">{{ $vaisseau->coef_hyperespace ?? '—' }}</span>
                <span style="color:var(--text-muted);">Coef PA/HE</span>
                <span style="color:var(--text-primary);">{{ $vaisseau->coef_pa_he ?? '—' }}</span>
            </div>
            <div style="border-top:1px solid var(--border-subtle);padding-top:8px;">
                <div style="display:flex;justify-content:space-between;font-family:var(--mono);font-size:9px;color:var(--text-muted);margin-bottom:3px;">
                    <span>Réserve</span>
                    <span>{{ $vaisseau->energie_actuelle ?? 0 }} / {{ $vaisseau->reserve ?? 0 }} UE</span>
                </div>
                <div style="height:3px;background:rgba(125,165,200,0.1);">
                    <div style="height:100%;width:{{ $pctEnergie }}%;background:var(--accent);"></div>
                </div>
            </div>
            @if($vaisseau->mode === 'combustible')
            <div style="border-top:1px solid var(--border-subtle);padding-top:8px;margin-top:8px;">
                <div style="display:grid;grid-template-columns:max-content 1fr;gap:4px 10px;font-family:var(--mono);font-size:10px;">
                    <span style="color:var(--text-muted);">Combustible</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->combustible ?? 0 }} u</span>
                    <span style="color:var(--text-muted);">Type</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->type_combustible ?? '—' }}</span>
                    <span style="color:var(--text-muted);">Efficacité</span>
                    <span style="color:var(--text-primary);">{{ round(($vaisseau->efficacite ?? 0) * 100) }}%</span>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Structure & Pannes --}}
    <div class="hud-panel">
        <div class="hud-panel-title" style="{{ $nbPannes > 0 ? 'color:var(--warning);' : '' }}">
            Structure{{ $nbPannes > 0 ? ' — '.$nbPannes.' PANNE(S)' : '' }}
        </div>
        <div style="padding:12px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);">
            <div style="margin-bottom:10px;">
                <div style="display:flex;justify-content:space-between;font-family:var(--mono);font-size:9px;color:var(--text-muted);margin-bottom:3px;">
                    <span>Intégrité coque</span>
                    <span style="color:{{ $pctCoque > 60 ? 'var(--success)' : ($pctCoque > 30 ? 'var(--warning)' : 'var(--danger)') }};">{{ $vaisseau->coque_actuelle ?? 0 }}/{{ $vaisseau->coque_max ?? 0 }} US</span>
                </div>
                <div style="height:4px;background:rgba(125,165,200,0.1);border:1px solid rgba(125,165,200,0.08);">
                    <div style="height:100%;width:{{ $pctCoque }}%;background:{{ $pctCoque > 60 ? 'var(--success)' : ($pctCoque > 30 ? 'var(--warning)' : 'var(--danger)') }};"></div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:max-content 1fr;gap:5px 10px;font-family:var(--mono);font-size:10px;margin-bottom:10px;">
                <span style="color:var(--text-muted);">Vétusté</span>
                <span style="color:{{ ($vaisseau->vetuste ?? 0) > 50 ? 'var(--danger)' : (($vaisseau->vetuste ?? 0) > 25 ? 'var(--warning)' : 'var(--success)') }};">{{ $vaisseau->vetuste ?? 0 }}%</span>
                <span style="color:var(--text-muted);">Score panne</span>
                <span style="color:var(--text-primary);">{{ $vaisseau->score_panne ?? 0 }}</span>
                <span style="color:var(--text-muted);">Entretien</span>
                <span style="color:var(--success);">{{ $vaisseau->score_entretien ?? 0 }}</span>
            </div>
            <div style="border-top:1px solid var(--border-subtle);padding-top:8px;">
                @if($nbPannes > 0)
                    @foreach($pannes as $pId => $panne)
                    <div style="padding:6px 8px;background:rgba(251,191,36,0.05);border:1px solid rgba(251,191,36,0.25);margin-bottom:4px;">
                        <div style="font-family:var(--mono);font-size:10px;color:var(--warning);">⚠ {{ $panne['nom'] ?? 'Panne inconnue' }}</div>
                        <div style="font-family:var(--mono);font-size:9px;color:var(--text-muted);margin-top:2px;">Complexité {{ $panne['complexite'] ?? 1 }} — réparable en station</div>
                    </div>
                    @endforeach
                @else
                <div style="padding:6px 8px;background:rgba(74,222,128,0.04);border:1px solid rgba(74,222,128,0.2);">
                    <span style="font-family:var(--mono);font-size:10px;color:var(--success);">✓ Aucune panne active</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Scanner + Armement --}}
    <div>
        <div class="hud-panel" style="margin-bottom:12px;">
            <div class="hud-panel-title">Scanner</div>
            <div style="padding:12px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);">
                <div style="display:grid;grid-template-columns:max-content 1fr;gap:5px 10px;font-family:var(--mono);font-size:10px;margin-bottom:8px;">
                    <span style="color:var(--text-muted);">Portée</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->portee_scan ?? '—' }} AL</span>
                    <span style="color:var(--text-muted);">Puissance</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->puissance_scan ?? 0 }}{{ ($vaisseau->bonus_scan ?? 0) > 0 ? ' +'.($vaisseau->bonus_scan) : '' }}</span>
                    <span style="color:var(--text-muted);">Dés</span>
                    <span style="color:var(--accent);">{{ $scanFormula['formula'] }}</span>
                </div>
                @if($scanEnCours)
                <div style="padding:5px 8px;background:rgba(127,212,255,0.06);border:1px solid rgba(127,212,255,0.3);font-family:var(--mono);font-size:10px;color:var(--data);">
                    Scan en cours — Niv. {{ $vaisseau->scan_niveau_actuel }}
                </div>
                @endif
            </div>
        </div>
        <div class="hud-panel">
            <div class="hud-panel-title">Armement</div>
            <div style="padding:10px 12px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);">
                @foreach([1 => $vaisseau->arme1, 2 => $vaisseau->arme2, 3 => $vaisseau->arme3] as $slot => $arme)
                <div style="display:flex;justify-content:space-between;align-items:center;font-family:var(--mono);font-size:10px;padding:4px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border-subtle);' : '' }}">
                    <span style="color:var(--text-muted);">Slot {{ $slot }}</span>
                    @if($arme)
                    <span style="color:var(--text-primary);">{{ $arme->nom }} <span style="color:var(--text-muted);font-size:9px;">({{ $arme->degats_min }}–{{ $arme->degats_max }})</span></span>
                    @else
                    <span style="color:var(--text-muted);opacity:0.4;">Vide</span>
                    @endif
                </div>
                @endforeach
                @if($vaisseau->bouclier)
                <div style="display:flex;justify-content:space-between;align-items:center;font-family:var(--mono);font-size:10px;padding:5px 0 0;border-top:1px solid var(--border-subtle);margin-top:2px;">
                    <span style="color:var(--text-muted);">Bouclier</span>
                    <span style="color:#60a5fa;">{{ $vaisseau->bouclier->nom }} <span style="font-size:9px;">({{ $vaisseau->bouclier_actuel ?? 0 }}/{{ $vaisseau->bouclier->points_max }})</span></span>
                </div>
                @else
                <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);opacity:0.4;padding:4px 0 0;border-top:1px solid var(--border-subtle);margin-top:2px;">Aucun bouclier</div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- Allocation des soutes --}}
@php
    $maxSoutes   = $vaisseau->max_soutes ?? 0;
    $slotsModules = $nbModulesCargo;
    $slotsCargo   = $slotsLibres;
@endphp
<div style="margin-top:12px;padding:14px 16px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
        <span style="font-family:var(--mono);font-size:9px;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);">Allocation des soutes — {{ $maxSoutes }} baie(s) au total</span>
        <span style="font-family:var(--mono);font-size:9px;color:var(--text-muted);">Cargo : {{ $cgVal }} / {{ $cgMax }} t &nbsp;·&nbsp; {{ $cgPct }}%</span>
    </div>
    {{-- Barre de slots visuels --}}
    <div style="display:flex;gap:3px;flex-wrap:wrap;margin-bottom:8px;">
        @for($i = 0; $i < $maxSoutes; $i++)
        @php $isModule = $i < $slotsModules; @endphp
        <div style="width:28px;height:18px;border:1px solid {{ $isModule ? 'rgba(167,139,250,0.5)' : 'rgba(127,212,255,0.25)' }};background:{{ $isModule ? 'rgba(167,139,250,0.12)' : 'rgba(127,212,255,0.05)' }};display:flex;align-items:center;justify-content:center;font-family:var(--mono);font-size:7px;color:{{ $isModule ? '#a78bfa' : 'var(--data)' }};" title="{{ $isModule ? 'Module' : 'Cargo' }}">
            {{ $isModule ? 'MOD' : 'CGO' }}
        </div>
        @endfor
    </div>
    <div style="display:flex;gap:20px;font-family:var(--mono);font-size:10px;">
        <span><span style="color:#a78bfa;">■</span> <span style="color:var(--text-muted);">Modules en soute :</span> <span style="color:var(--text-primary);">{{ $slotsModules }}</span></span>
        <span><span style="color:var(--data);">■</span> <span style="color:var(--text-muted);">Baies cargo libres :</span> <span style="color:var(--text-primary);">{{ $slotsCargo }}</span></span>
        <span style="color:var(--text-muted);opacity:0.6;font-size:9px;margin-left:auto;">Chaque baie convertie en module réduit la capacité de {{ $maxSoutes > 0 ? round($cgMax / max(1, $maxSoutes)) : '—' }} t</span>
    </div>
</div>
