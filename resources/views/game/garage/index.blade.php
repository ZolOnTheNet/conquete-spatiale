@extends('layouts.game-hud')

@section('title', 'Garage')

@section('hud-content')
@php
    $nomVaisseau   = $vaisseau->objetSpatial->nom ?? 'Vaisseau sans nom';
    $typePropLabel = match((string)($vaisseau->type_propulsion ?? '')) {
        '1'     => 'Micro-panneaux solaires',
        '2'     => 'Voile solaire',
        '3'     => 'Matière noire',
        default => (string)($vaisseau->type_propulsion ?? '—'),
    };
    $modePropLabel = match($vaisseau->mode ?? '') {
        'energetique' => 'Type B — Extraction énergétique',
        'combustible' => 'Type A — À combustible',
        default       => $vaisseau->mode ?? '—',
    };
    $scanFormula    = $vaisseau->getDiceFormula();
    $pctEnergie     = ($vaisseau->reserve ?? 0) > 0 ? round(($vaisseau->energie_actuelle / $vaisseau->reserve) * 100) : 0;
    $bouclierMax    = $vaisseau->bouclier?->points_max ?? 0;
    $pctBouclier    = $bouclierMax > 0 ? round(($vaisseau->bouclier_actuel / $bouclierMax) * 100) : 0;
    $armesSlot10    = implode(' / ', array_filter([$vaisseau->arme2?->nom, $vaisseau->arme3?->nom]));
    $slots = [
        ['num' => 1,  'nom' => 'Poste de pilotage',      'icone' => '🎯', 'etat' => 'ok',
         'info' => 'Poste standard', 'detail' => null],
        ['num' => 2,  'nom' => 'Moteur conventionnel',   'icone' => '⚙️', 'etat' => 'ok',
         'info' => 'Vitesse ' . ($vaisseau->vitesse_conventionnelle ?? '?'),
         'detail' => 'Init ' . ($vaisseau->init_conventionnel ?? 0) . ' UE'],
        ['num' => 3,  'nom' => 'Moteur Hyper-Espace',    'icone' => '🚀', 'etat' => 'ok',
         'info' => 'Vitesse ' . ($vaisseau->vitesse_saut ?? '?'),
         'detail' => 'Init ' . ($vaisseau->init_hyperespace ?? 200) . ' UE'],
        ['num' => 4,  'nom' => 'Boucliers',              'icone' => '🛡️',
         'etat' => $vaisseau->bouclier ? 'ok' : 'vide',
         'info' => $vaisseau->bouclier ? $vaisseau->bouclier->nom : 'Aucun bouclier',
         'detail' => $vaisseau->bouclier ? ($vaisseau->bouclier->points_max . ' pts max') : null],
        ['num' => 5,  'nom' => 'Système informatique',  'icone' => '💻', 'etat' => 'ok',
         'info' => 'SI ' . ($vaisseau->system_informatique ?? 0),
         'detail' => count($vaisseau->programmes ?? []) . ' programme(s)'],
        ['num' => 6,  'nom' => "Réserve d'énergie",     'icone' => '🔋', 'etat' => 'ok',
         'info' => ($vaisseau->energie_actuelle ?? 0) . ' / ' . ($vaisseau->reserve ?? 0) . ' UE',
         'detail' => null],
        ['num' => 7,  'nom' => 'Réserve de combustible', 'icone' => '⛽',
         'etat' => $vaisseau->mode === 'combustible' ? 'ok' : 'na',
         'info' => $vaisseau->mode === 'combustible'
             ? (($vaisseau->combustible ?? 0) . ' u — ' . ($vaisseau->type_combustible ?? '?'))
             : 'N/A — Propulsion énergétique',
         'detail' => null],
        ['num' => 8,  'nom' => 'Soute (cargo)',          'icone' => '📦', 'etat' => 'ok',
         'info' => ($vaisseau->max_soutes ?? 0) . ' cargo(s)',
         'detail' => ($vaisseau->place_soute ?? 0) . ' tonnes'],
        ['num' => 9,  'nom' => 'Armement lié',           'icone' => '🔫',
         'etat' => $vaisseau->arme1 ? 'ok' : 'vide',
         'info' => $vaisseau->arme1 ? $vaisseau->arme1->nom : 'Vide',
         'detail' => $vaisseau->arme1 ? ($vaisseau->arme1->degats_min . '–' . $vaisseau->arme1->degats_max . ' dégâts') : null],
        ['num' => 10, 'nom' => "Système d'armements",    'icone' => '⚔️',
         'etat' => ($vaisseau->arme2 || $vaisseau->arme3) ? 'ok' : 'vide',
         'info' => $armesSlot10 ?: 'Vide',
         'detail' => null],
        ['num' => 11, 'nom' => 'Système de survie',      'icone' => '🩺', 'etat' => 'vide',
         'info' => 'Non configuré', 'detail' => null],
        ['num' => 12, 'nom' => 'Structure & blindage',   'icone' => '🏗️', 'etat' => 'ok',
         'info' => 'Coque ' . ($vaisseau->coque_max ?? 0) . ' US',
         'detail' => 'Vétusté ' . ($vaisseau->vetuste ?? 0) . '%'],
    ];
@endphp

{{-- Header --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <div class="hud-page-title" style="margin:0;">Garage — {{ $station->nom }}</div>
    <a href="{{ route('station.hangar') }}" style="font-family:var(--mono);font-size:10px;letter-spacing:0.1em;text-transform:uppercase;padding:5px 12px;background:transparent;color:var(--text-muted);border:1px solid var(--border-subtle);text-decoration:none;transition:all 0.15s;" onmouseover="this.style.color='var(--text-primary)';this.style.borderColor='var(--border-strong)'" onmouseout="this.style.color='var(--text-muted)';this.style.borderColor='var(--border-subtle)'">
        ← Hangar
    </a>
</div>

{{-- Flash messages --}}
@if(session('success'))
<div style="margin-bottom:12px;padding:10px 14px;background:rgba(74,222,128,0.06);border:1px solid rgba(74,222,128,0.3);">
    <span style="color:var(--success);font-family:var(--mono);font-size:10px;">✓ {{ session('success') }}</span>
</div>
@endif
@if(session('error'))
<div style="margin-bottom:12px;padding:10px 14px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.3);">
    <span style="color:var(--danger);font-family:var(--mono);font-size:10px;">✕ {{ session('error') }}</span>
</div>
@endif
@if(session('info'))
<div style="margin-bottom:12px;padding:10px 14px;background:rgba(96,165,250,0.06);border:1px solid rgba(96,165,250,0.3);">
    <span style="color:#60a5fa;font-family:var(--mono);font-size:10px;">ℹ {{ session('info') }}</span>
</div>
@endif

{{-- Tab bar --}}
<div style="display:flex;border-bottom:1px solid var(--border-subtle);margin-bottom:20px;gap:0;">
    @foreach([['reparations','Réparations'],['technique','Fiche Technique'],['emplacements','Emplacements']] as [$tid,$tlabel])
    <button id="tab-{{ $tid }}" onclick="switchTab('{{ $tid }}')" style="font-family:var(--mono);font-size:10px;letter-spacing:0.08em;text-transform:uppercase;padding:8px 18px;background:transparent;color:var(--text-muted);border:none;border-bottom:2px solid transparent;cursor:pointer;transition:all 0.15s;">{{ $tlabel }}</button>
    @endforeach
</div>

{{-- ========================================================= --}}
{{-- PANEL: RÉPARATIONS                                        --}}
{{-- ========================================================= --}}
<div id="panel-reparations">

    {{-- État du vaisseau --}}
    <div class="hud-panel" style="margin-bottom:16px;border-color:rgba(255,138,61,0.3);">
        <div class="hud-panel-title" style="color:var(--accent);">État du Vaisseau</div>
        <div style="padding:14px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);">
            <div style="font-size:14px;font-weight:700;color:var(--text-primary);margin-bottom:12px;">{{ $nomVaisseau }}</div>
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
</div>

{{-- ========================================================= --}}
{{-- PANEL: FICHE TECHNIQUE                                    --}}
{{-- ========================================================= --}}
<div id="panel-technique" style="display:none;">

    {{-- Identification --}}
    <div class="hud-panel" style="margin-bottom:12px;">
        <div class="hud-panel-title">Identification</div>
        <div style="padding:14px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);">
            <div style="display:grid;grid-template-columns:160px 1fr;gap:6px 0;font-family:var(--mono);font-size:11px;">
                <span style="color:var(--text-muted);">Nom</span>
                <span style="color:var(--text-primary);font-weight:700;">{{ $nomVaisseau }}</span>
                <span style="color:var(--text-muted);">Modèle</span>
                <span style="color:var(--accent);">{{ $vaisseau->modele ?? '—' }}</span>
                <span style="color:var(--text-muted);">Mode propulsion</span>
                <span style="color:var(--text-secondary);">{{ $modePropLabel }}</span>
                <span style="color:var(--text-muted);">Type</span>
                <span style="color:var(--text-secondary);">{{ $typePropLabel }}</span>
            </div>
        </div>
    </div>

    {{-- Propulsion --}}
    <div class="hud-panel" style="margin-bottom:12px;">
        <div class="hud-panel-title">Propulsion & Énergie</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;border:1px solid var(--border-subtle);background:rgba(5,7,12,0.4);">
            <div style="padding:14px;border-right:1px solid var(--border-subtle);">
                <div style="font-size:10px;font-weight:700;color:var(--text-secondary);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.1em;">Mode conventionnel</div>
                <div style="display:grid;grid-template-columns:max-content 1fr;gap:4px 12px;font-family:var(--mono);font-size:10px;">
                    <span style="color:var(--text-muted);">Vitesse</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->vitesse_conventionnelle ?? '—' }}</span>
                    <span style="color:var(--text-muted);">Init UE</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->init_conventionnel ?? 0 }}</span>
                    <span style="color:var(--text-muted);">Coef énergie</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->coef_conventionnel ?? '—' }}</span>
                    <span style="color:var(--text-muted);">Coef PA/MN</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->coef_pa_mn ?? '—' }}</span>
                </div>
            </div>
            <div style="padding:14px;">
                <div style="font-size:10px;font-weight:700;color:var(--text-secondary);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.1em;">Mode hyper-espace</div>
                <div style="display:grid;grid-template-columns:max-content 1fr;gap:4px 12px;font-family:var(--mono);font-size:10px;">
                    <span style="color:var(--text-muted);">Vitesse saut</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->vitesse_saut ?? '—' }}</span>
                    <span style="color:var(--text-muted);">Init HE (UE)</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->init_hyperespace ?? 200 }}</span>
                    <span style="color:var(--text-muted);">Coef énergie</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->coef_hyperespace ?? '—' }}</span>
                    <span style="color:var(--text-muted);">Coef PA/HE</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->coef_pa_he ?? '—' }}</span>
                </div>
            </div>
        </div>
        <div style="padding:10px 14px;border:1px solid var(--border-subtle);border-top:none;background:rgba(5,7,12,0.2);">
            <div style="display:flex;justify-content:space-between;font-family:var(--mono);font-size:10px;color:var(--text-muted);margin-bottom:4px;">
                <span>Réserve d'énergie</span>
                <span>{{ $vaisseau->energie_actuelle ?? 0 }} / {{ $vaisseau->reserve ?? 0 }} UE</span>
            </div>
            <div style="height:5px;background:rgba(125,165,200,0.1);border:1px solid var(--border-subtle);">
                <div style="height:100%;width:{{ $pctEnergie }}%;background:var(--accent);"></div>
            </div>
        </div>
        @if($vaisseau->mode === 'combustible')
        <div style="padding:10px 14px;border:1px solid var(--border-subtle);border-top:none;background:rgba(5,7,12,0.2);">
            <div style="display:grid;grid-template-columns:160px 1fr;gap:4px 0;font-family:var(--mono);font-size:10px;">
                <span style="color:var(--text-muted);">Combustible</span>
                <span style="color:var(--text-primary);">{{ $vaisseau->combustible ?? 0 }} unités</span>
                <span style="color:var(--text-muted);">Type</span>
                <span style="color:var(--text-primary);">{{ $vaisseau->type_combustible ?? '—' }}</span>
                <span style="color:var(--text-muted);">Efficacité</span>
                <span style="color:var(--text-primary);">{{ round(($vaisseau->efficacite ?? 0) * 100) }}%</span>
                <span style="color:var(--text-muted);">Récupération</span>
                <span style="color:var(--text-primary);">{{ $vaisseau->recuperation ?? '—' }} u/cargo</span>
            </div>
        </div>
        @endif
    </div>

    {{-- Structure + Soutes + Scanner --}}
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px;">
        {{-- Structure --}}
        <div class="hud-panel">
            <div class="hud-panel-title">Structure</div>
            <div style="padding:12px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);">
                <div style="display:grid;grid-template-columns:max-content 1fr;gap:5px 10px;font-family:var(--mono);font-size:10px;">
                    <span style="color:var(--text-muted);">Coque max</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->coque_max ?? 0 }} US</span>
                    <span style="color:var(--text-muted);">Vétusté</span>
                    <span style="color:{{ ($vaisseau->vetuste ?? 0) > 50 ? 'var(--danger)' : (($vaisseau->vetuste ?? 0) > 25 ? 'var(--warning)' : 'var(--success)') }};">{{ $vaisseau->vetuste ?? 0 }}%</span>
                    <span style="color:var(--text-muted);">Complexité</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->complexite_fct ?? '—' }}</span>
                    <span style="color:var(--text-muted);">Score panne</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->score_panne ?? 0 }}</span>
                    <span style="color:var(--text-muted);">Entretien</span>
                    <span style="color:var(--success);">{{ $vaisseau->score_entretien ?? 0 }}</span>
                    <span style="color:var(--text-muted);">Part panne</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->part_panne ?? 0 }}%</span>
                </div>
            </div>
        </div>
        {{-- Soutes --}}
        <div class="hud-panel">
            <div class="hud-panel-title">Soutes</div>
            <div style="padding:12px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);">
                <div style="display:grid;grid-template-columns:max-content 1fr;gap:5px 10px;font-family:var(--mono);font-size:10px;">
                    <span style="color:var(--text-muted);">Cargos max</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->max_soutes ?? 0 }}</span>
                    <span style="color:var(--text-muted);">Capacité</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->place_soute ?? 0 }} t</span>
                    <span style="color:var(--text-muted);">Masse variable</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->masse_variable ?? 0 }} t</span>
                </div>
            </div>
        </div>
        {{-- Scanner --}}
        <div class="hud-panel">
            <div class="hud-panel-title">Scanner</div>
            <div style="padding:12px;background:rgba(5,7,12,0.4);border:1px solid var(--border-subtle);">
                <div style="display:grid;grid-template-columns:max-content 1fr;gap:5px 10px;font-family:var(--mono);font-size:10px;">
                    <span style="color:var(--text-muted);">Portée</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->portee_scan ?? '—' }} AL</span>
                    <span style="color:var(--text-muted);">Puissance</span>
                    <span style="color:var(--text-primary);">{{ $vaisseau->puissance_scan ?? 0 }}{{ ($vaisseau->bonus_scan ?? 0) > 0 ? ' +'.($vaisseau->bonus_scan) : '' }}</span>
                    <span style="color:var(--text-muted);">Formule dés</span>
                    <span style="color:var(--accent);">{{ $scanFormula['formula'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Armement & Défense --}}
    <div class="hud-panel">
        <div class="hud-panel-title">Armement & Défense</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;border:1px solid var(--border-subtle);background:rgba(5,7,12,0.4);">
            {{-- Armes --}}
            <div style="padding:14px;border-right:1px solid var(--border-subtle);">
                <div style="font-size:10px;font-weight:700;color:var(--text-secondary);margin-bottom:10px;text-transform:uppercase;letter-spacing:0.1em;">Armes</div>
                @foreach([1 => $vaisseau->arme1, 2 => $vaisseau->arme2, 3 => $vaisseau->arme3] as $slot => $arme)
                @if($arme)
                <div style="margin-bottom:8px;padding:8px;background:rgba(239,68,68,0.04);border:1px solid rgba(239,68,68,0.2);">
                    <div style="font-family:var(--mono);font-size:10px;color:var(--danger);margin-bottom:4px;font-weight:600;">Slot {{ $slot }} — {{ $arme->nom }}</div>
                    <div style="display:grid;grid-template-columns:max-content 1fr;gap:2px 8px;font-family:var(--mono);font-size:9px;">
                        <span style="color:var(--text-muted);">Type</span><span style="color:var(--text-primary);">{{ $arme->type }}</span>
                        <span style="color:var(--text-muted);">Dégâts</span><span style="color:var(--text-primary);">{{ $arme->degats_min }}–{{ $arme->degats_max }}</span>
                        <span style="color:var(--text-muted);">Portée</span><span style="color:var(--text-primary);">{{ $arme->portee }}</span>
                        <span style="color:var(--text-muted);">Énergie/tir</span><span style="color:var(--text-primary);">{{ $arme->energie_tir }} UE</span>
                    </div>
                </div>
                @else
                <div style="margin-bottom:6px;padding:8px;border:1px dashed rgba(125,165,200,0.15);font-family:var(--mono);font-size:10px;color:var(--text-muted);">Slot {{ $slot }} — Vide</div>
                @endif
                @endforeach
                <div style="display:grid;grid-template-columns:max-content 1fr;gap:4px 12px;font-family:var(--mono);font-size:10px;margin-top:6px;padding-top:6px;border-top:1px solid var(--border-subtle);">
                    <span style="color:var(--text-muted);">Esquive</span><span style="color:var(--text-primary);">{{ $vaisseau->esquive ?? 0 }}</span>
                    <span style="color:var(--text-muted);">Bonus précision</span><span style="color:var(--text-primary);">+{{ $vaisseau->bonus_precision ?? 0 }}</span>
                </div>
            </div>
            {{-- Bouclier --}}
            <div style="padding:14px;">
                <div style="font-size:10px;font-weight:700;color:var(--text-secondary);margin-bottom:10px;text-transform:uppercase;letter-spacing:0.1em;">Bouclier</div>
                @if($vaisseau->bouclier)
                <div style="padding:10px;background:rgba(96,165,250,0.04);border:1px solid rgba(96,165,250,0.25);">
                    <div style="font-family:var(--mono);font-size:11px;color:#60a5fa;margin-bottom:8px;font-weight:700;">{{ $vaisseau->bouclier->nom }}</div>
                    <div style="margin-bottom:8px;">
                        <div style="display:flex;justify-content:space-between;font-family:var(--mono);font-size:9px;color:var(--text-muted);margin-bottom:3px;">
                            <span>Intégrité</span>
                            <span>{{ $vaisseau->bouclier_actuel ?? 0 }}/{{ $vaisseau->bouclier->points_max }}</span>
                        </div>
                        <div style="height:4px;background:rgba(125,165,200,0.1);border:1px solid var(--border-subtle);">
                            <div style="height:100%;width:{{ $pctBouclier }}%;background:#60a5fa;"></div>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:max-content 1fr;gap:3px 10px;font-family:var(--mono);font-size:9px;">
                        <span style="color:var(--text-muted);">Type</span><span style="color:var(--text-primary);">{{ $vaisseau->bouclier->type ?? '—' }}</span>
                        <span style="color:var(--text-muted);">Résistance</span><span style="color:var(--text-primary);">{{ $vaisseau->bouclier->resistance ?? 0 }}%</span>
                        <span style="color:var(--text-muted);">Régénération</span><span style="color:var(--text-primary);">{{ $vaisseau->bouclier->regeneration ?? 0 }}/tour</span>
                        <span style="color:var(--text-muted);">Énergie/maintien</span><span style="color:var(--text-primary);">{{ $vaisseau->bouclier->energie_maintien ?? 0 }} UE</span>
                    </div>
                </div>
                @else
                <div style="padding:20px;border:1px dashed rgba(125,165,200,0.15);text-align:center;">
                    <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);">Aucun bouclier installé</div>
                    <div style="font-family:var(--mono);font-size:9px;color:var(--text-muted);margin-top:4px;opacity:0.6;">Slot disponible à l'amélioration</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- PANEL: EMPLACEMENTS                                       --}}
{{-- ========================================================= --}}
<div id="panel-emplacements" style="display:none;">
    <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);margin-bottom:16px;padding:10px 14px;border:1px solid var(--border-subtle);background:rgba(5,7,12,0.3);">
        Le vaisseau <span style="color:var(--text-secondary);">{{ $nomVaisseau }}</span> (modèle <span style="color:var(--accent);">{{ $vaisseau->modele ?? '—' }}</span>) dispose de 12 emplacements fonctionnels.
        <span style="color:rgba(125,165,200,0.5);margin-left:8px;">● Installé &nbsp; ○ Vide &nbsp; — N/A</span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
        @foreach($slots as $slot)
        @php
            $border = match($slot['etat']) {
                'ok'   => '1px solid rgba(74,222,128,0.35)',
                'vide' => '1px dashed rgba(125,165,200,0.2)',
                'na'   => '1px solid rgba(125,165,200,0.1)',
                default => '1px solid var(--border-subtle)',
            };
            $infoColor = $slot['etat'] === 'ok' ? 'var(--text-primary)' : 'var(--text-muted)';
        @endphp
        <div style="padding:12px;background:rgba(5,7,12,0.4);border:{{ $border }};position:relative;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                <span style="font-family:var(--mono);font-size:8px;color:var(--text-muted);opacity:0.5;min-width:18px;">{{ str_pad($slot['num'], 2, '0', STR_PAD_LEFT) }}</span>
                <span style="font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.06em;flex:1;line-height:1.2;">{{ $slot['nom'] }}</span>
                <span style="font-size:13px;flex-shrink:0;">{{ $slot['icone'] }}</span>
            </div>
            <div style="font-family:var(--mono);font-size:10px;color:{{ $infoColor }};">{{ $slot['info'] }}</div>
            @if($slot['detail'])
            <div style="font-family:var(--mono);font-size:9px;color:var(--text-muted);margin-top:2px;">{{ $slot['detail'] }}</div>
            @endif
            @if($slot['etat'] === 'ok')
            <div style="position:absolute;top:8px;right:8px;width:5px;height:5px;border-radius:50%;background:var(--success);box-shadow:0 0 4px rgba(74,222,128,0.6);"></div>
            @elseif($slot['etat'] === 'vide')
            <div style="position:absolute;top:8px;right:8px;width:5px;height:5px;border-radius:50%;border:1px solid rgba(125,165,200,0.4);"></div>
            @endif
        </div>
        @endforeach
    </div>
    <div style="margin-top:16px;padding:10px 14px;border:1px solid rgba(167,139,250,0.2);background:rgba(167,139,250,0.04);">
        <div style="font-family:var(--mono);font-size:9px;color:var(--text-muted);">
            Les améliorations d'emplacements (boucliers, armes, systèmes) seront disponibles via le catalogue de la station.
        </div>
    </div>
</div>

@endsection

@push('hud-scripts')
<script>
(function() {
    const TABS = ['reparations', 'technique', 'emplacements'];

    function switchTab(name) {
        TABS.forEach(t => {
            const btn   = document.getElementById('tab-' + t);
            const panel = document.getElementById('panel-' + t);
            const active = (t === name);
            panel.style.display  = active ? 'block' : 'none';
            btn.style.color       = active ? 'var(--accent)' : 'var(--text-muted)';
            btn.style.borderBottom = active ? '2px solid var(--accent)' : '2px solid transparent';
            btn.style.background  = active ? 'rgba(255,138,61,0.06)' : 'transparent';
        });
        history.replaceState(null, '', '#' + name);
    }

    window.switchTab = switchTab;

    document.addEventListener('DOMContentLoaded', function() {
        const hash = location.hash.replace('#', '');
        switchTab(TABS.includes(hash) ? hash : 'reparations');
    });
})();
</script>
@endpush
