@extends('layouts.app')

@section('title', 'Timonerie')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
html { overflow: hidden !important; }
body {
  font-family: 'Rajdhani', sans-serif !important;
  background: #05070C !important;
  color: #E6EDF3;
  font-size: 14px;
  letter-spacing: 0.02em;
  min-width: 1440px;
  min-height: 820px;
  overflow: auto !important;
}
body.stars { background-image: none !important; }

:root {
  --bg-void: #05070C;
  --bg-panel: rgba(13, 17, 23, 0.85);
  --bg-elevated: rgba(21, 27, 36, 0.95);
  --border-subtle: rgba(125, 165, 200, 0.15);
  --border-strong: rgba(125, 165, 200, 0.35);
  --text-primary: #E6EDF3;
  --text-secondary: #8B96A8;
  --text-muted: #4F5B6F;
  --accent: #FF8A3D;
  --data: #7FD4FF;
  --success: #4ADE80;
  --warning: #FBBF24;
  --danger: #EF4444;
  --mono: 'JetBrains Mono', monospace;
  --sans: 'Rajdhani', sans-serif;
}
* { box-sizing: border-box; margin: 0; padding: 0; }

.viewport-notice {
  display: none;
  position: fixed; top: 0; left: 0; right: 0;
  padding: 8px 20px; z-index: 1000;
  background: rgba(251, 191, 36, 0.95); color: #1a1410;
  font-family: var(--mono); font-size: 11px; letter-spacing: 0.1em;
  text-align: center; text-transform: uppercase;
}
@media (max-width: 1439px) { .viewport-notice { display: block; } }

.hud-header {
  position: absolute; top: 0; left: 0; width: 1440px; height: 56px;
  background: linear-gradient(180deg, rgba(5,7,12,0.98), rgba(5,7,12,0.75));
  border-bottom: 1px solid var(--border-subtle);
  display: flex; align-items: center; padding: 0 20px; gap: 28px;
  z-index: 100; backdrop-filter: blur(6px);
  flex-wrap: nowrap; white-space: nowrap;
}
.hud-brand { font-family: var(--mono); font-size: 11px; letter-spacing: 0.25em; color: var(--accent); font-weight: 700; }
.hud-sep { width: 1px; height: 28px; background: var(--border-subtle); flex-shrink: 0; }
.hud-block { display: flex; flex-direction: column; gap: 2px; }
.hud-label { font-family: var(--mono); font-size: 9px; letter-spacing: 0.2em; color: var(--text-muted); text-transform: uppercase; }
.hud-value { font-family: var(--mono); font-size: 13px; color: var(--text-primary); font-weight: 500; }
.hud-value.data { color: var(--data); }
.hud-value.accent { color: var(--accent); }
.gauge-group { display: flex; gap: 16px; }
.gauge { display: flex; align-items: center; gap: 8px; cursor: default; }
.gauge-icon { font-family: var(--mono); font-size: 11px; color: var(--text-muted); letter-spacing: 0.1em; }
.gauge-bar { width: 60px; height: 6px; background: rgba(125, 165, 200, 0.1); border: 1px solid var(--border-subtle); position: relative; }
.gauge-fill { height: 100%; transition: width 0.3s; }
.gauge-fill.ok { background: linear-gradient(90deg, var(--success), #86efac); box-shadow: 0 0 6px rgba(74, 222, 128, 0.4); }
.gauge-fill.mid { background: linear-gradient(90deg, var(--warning), #fcd34d); box-shadow: 0 0 6px rgba(251, 191, 36, 0.4); }
.gauge-fill.low { background: linear-gradient(90deg, var(--danger), #fca5a5); box-shadow: 0 0 6px rgba(239, 68, 68, 0.5); }
.gauge-val { font-family: var(--mono); font-size: 10px; color: var(--text-secondary); min-width: 36px; }
.hud-spacer { flex: 1; }
.hud-btn { font-family: var(--mono); font-size: 10px; letter-spacing: 0.15em; color: var(--text-muted); text-transform: uppercase; background: none; border: 1px solid var(--border-subtle); padding: 5px 10px; cursor: pointer; transition: all 0.15s; text-decoration: none; display: inline-flex; align-items: center; }
.hud-btn:hover { color: var(--text-primary); border-color: var(--border-strong); }

.stage {
  position: absolute; top: 56px; left: 0;
  width: 1440px; height: calc(100vh - 56px);
  min-height: 764px;
  display: grid; grid-template-columns: 260px 1fr 360px;
  overflow: hidden;
}

.nav {
  background: var(--bg-panel);
  border-right: 1px solid var(--border-subtle);
  padding: 20px 0;
  overflow-y: auto;
  backdrop-filter: blur(6px);
}
.nav-section { margin-bottom: 24px; }
.nav-section-head { padding: 4px 20px; font-family: var(--mono); font-size: 9px; letter-spacing: 0.2em; color: var(--text-muted); text-transform: uppercase; display: flex; align-items: center; gap: 8px; }
.nav-section-head::before { content: ''; width: 4px; height: 4px; background: var(--text-muted); border-radius: 50%; }
.nav-item {
  display: flex; justify-content: space-between; align-items: center;
  padding: 7px 20px; color: var(--text-secondary); font-size: 13px; cursor: pointer;
  transition: all 0.12s; border-left: 2px solid transparent; font-weight: 500;
  text-decoration: none;
}
.nav-item:hover { color: var(--text-primary); background: rgba(127, 212, 255, 0.04); }
.nav-item.active { color: var(--accent); background: rgba(255, 138, 61, 0.06); border-left-color: var(--accent); }
.nav-item.sub { padding-left: 36px; font-size: 12px; }
.nav-cmd { font-family: var(--mono); font-size: 10px; color: var(--text-muted); opacity: 0; transition: opacity 0.15s; }
.nav-item:hover .nav-cmd { opacity: 1; }
.nav-item.disabled { opacity: 0.35; cursor: default; pointer-events: none; }

.starmap-wrap { position: relative; background: radial-gradient(ellipse at center, #0a0f1a 0%, #050709 70%); overflow: hidden; min-width: 0; }
#starmap { width: 100%; height: 100%; display: block; }
.starmap-title {
  position: absolute; top: 16px; left: 20px;
  font-family: var(--mono); font-size: 10px; letter-spacing: 0.25em;
  color: var(--text-muted); text-transform: uppercase;
  display: flex; align-items: center; gap: 10px;
}
.starmap-title span.accent { color: var(--data); }
.pulse { width: 6px; height: 6px; background: var(--accent); border-radius: 50%; box-shadow: 0 0 8px var(--accent); animation: pulse 2s infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

.compass {
  position: absolute; bottom: 16px; left: 20px;
  width: 100px; height: 100px; border: 1px solid var(--border-subtle);
  background: rgba(5, 7, 12, 0.7); backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center;
  font-family: var(--mono); font-size: 10px; color: var(--text-muted);
}
.compass-inner { position: relative; width: 100%; height: 100%; }
.compass-label { position: absolute; font-size: 9px; color: var(--text-muted); }
.compass-label.n { top: 4px; left: 50%; transform: translateX(-50%); color: var(--accent); }
.compass-label.s { bottom: 4px; left: 50%; transform: translateX(-50%); }
.compass-label.e { right: 4px; top: 50%; transform: translateY(-50%); }
.compass-label.w { left: 4px; top: 50%; transform: translateY(-50%); }
.compass-dot { position: absolute; top: 50%; left: 50%; width: 4px; height: 4px; background: var(--accent); border-radius: 50%; transform: translate(-50%, -50%); box-shadow: 0 0 6px var(--accent); }
.compass-ring { position: absolute; inset: 14px; border: 1px solid var(--border-subtle); border-radius: 50%; }

.starmap-controls {
  position: absolute; bottom: 16px; right: 16px;
  display: flex; flex-direction: column; gap: 4px;
}
.ctrl-btn {
  width: 32px; height: 32px; background: rgba(5, 7, 12, 0.8);
  border: 1px solid var(--border-subtle); color: var(--text-secondary);
  font-family: var(--mono); font-size: 14px; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  backdrop-filter: blur(4px); transition: all 0.15s;
}
.ctrl-btn:hover { color: var(--data); border-color: var(--border-strong); }

.legend {
  position: absolute; top: 16px; right: 16px;
  background: rgba(5, 7, 12, 0.75); backdrop-filter: blur(4px);
  border: 1px solid var(--border-subtle); padding: 10px 14px;
  font-family: var(--mono); font-size: 10px; color: var(--text-secondary);
  min-width: 160px;
}
.legend-head { font-size: 9px; letter-spacing: 0.2em; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; }
.legend-row { display: flex; align-items: center; gap: 8px; margin: 3px 0; }
.legend-glyph { width: 12px; text-align: center; }

.sys-tooltip {
  position: absolute; pointer-events: none;
  background: rgba(5, 7, 12, 0.9); backdrop-filter: blur(4px);
  border: 1px solid var(--data); padding: 10px 14px;
  font-family: var(--mono); font-size: 11px; color: var(--text-primary);
  box-shadow: 0 0 12px rgba(127, 212, 255, 0.3);
  opacity: 0; transition: opacity 0.15s; min-width: 180px;
}
.sys-tooltip.show { opacity: 1; }
.sys-tooltip h4 { font-size: 13px; color: var(--data); margin-bottom: 6px; letter-spacing: 0.08em; }
.sys-tooltip .row { display: flex; justify-content: space-between; margin: 2px 0; color: var(--text-secondary); }
.sys-tooltip .cost.ok { color: var(--success); }
.sys-tooltip .cost.mid { color: var(--warning); }
.sys-tooltip .cost.ko { color: var(--danger); }

.right-col { display: flex; flex-direction: column; background: var(--bg-panel); border-left: 1px solid var(--border-subtle); backdrop-filter: blur(6px); min-height: 0; overflow: hidden; }

.instruments { padding: 16px; border-bottom: 1px solid var(--border-subtle); flex-shrink: 0; }
.inst-head {
  font-family: var(--mono); font-size: 9px; letter-spacing: 0.25em;
  color: var(--text-muted); text-transform: uppercase; margin-bottom: 12px;
  display: flex; justify-content: space-between;
}
.inst-head .live { color: var(--success); display: flex; align-items: center; gap: 6px; }
.inst-head .live::before { content: ''; width: 5px; height: 5px; background: var(--success); border-radius: 50%; box-shadow: 0 0 6px var(--success); animation: pulse 2s infinite; }

.inst-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 16px; }
.inst { display: flex; flex-direction: column; gap: 2px; }
.inst-label { font-family: var(--mono); font-size: 9px; letter-spacing: 0.15em; color: var(--text-muted); text-transform: uppercase; }
.inst-val { font-family: var(--mono); font-size: 14px; color: var(--text-primary); font-weight: 500; }
.inst-val.data { color: var(--data); }

.destination { padding: 14px 16px; border-bottom: 1px solid var(--border-subtle); flex-shrink: 0; }
.dest-head { font-family: var(--mono); font-size: 9px; letter-spacing: 0.25em; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; }
.dest-name { font-size: 16px; font-weight: 600; color: var(--data); margin-bottom: 2px; letter-spacing: 0.03em; }
.dest-name.empty { color: var(--text-muted); font-size: 13px; font-style: italic; }
.dest-meta { font-family: var(--mono); font-size: 11px; color: var(--text-secondary); margin-bottom: 12px; }
.dest-cost { display: flex; justify-content: space-between; font-family: var(--mono); font-size: 11px; margin-bottom: 10px; padding: 8px 10px; background: rgba(127, 212, 255, 0.04); border-left: 2px solid var(--data); }
.dest-cost .val { color: var(--warning); }
.dest-actions { display: flex; gap: 8px; }
.btn {
  flex: 1; font-family: var(--mono); font-size: 11px; letter-spacing: 0.12em;
  text-transform: uppercase; padding: 9px 12px; cursor: pointer;
  background: transparent; color: var(--text-secondary);
  border: 1px solid var(--border-subtle); transition: all 0.15s;
  position: relative; display: flex; align-items: center; justify-content: center; gap: 6px;
}
.btn:hover:not(:disabled) { color: var(--text-primary); border-color: var(--border-strong); }
.btn:disabled { opacity: 0.35; cursor: default; }
.btn.primary { color: var(--accent); border-color: var(--accent); }
.btn.primary:hover:not(:disabled) { background: rgba(255, 138, 61, 0.1); box-shadow: 0 0 12px rgba(255, 138, 61, 0.2); }
.btn.primary::before { content: '▶'; font-size: 8px; }
.btn.danger { color: var(--danger); border-color: var(--danger); }
.btn.danger:hover { background: rgba(239, 68, 68, 0.1); }
.btn-cmd { position: absolute; bottom: -16px; left: 0; right: 0; font-size: 9px; color: var(--text-muted); text-align: center; opacity: 0; transition: opacity 0.15s; letter-spacing: 0.05em; }
.btn:hover .btn-cmd { opacity: 1; }

.scanner { padding: 14px 16px; border-bottom: 1px solid var(--border-subtle); flex-shrink: 0; }
.scanner-head { font-family: var(--mono); font-size: 9px; letter-spacing: 0.25em; color: var(--text-muted); text-transform: uppercase; margin-bottom: 10px; display: flex; justify-content: space-between; }
.scanner-head .range { color: var(--data); }
.scan-list { display: flex; flex-direction: column; gap: 5px; max-height: 140px; overflow-y: auto; }
.scan-item { display: flex; align-items: center; gap: 10px; padding: 5px 8px; cursor: pointer; transition: all 0.12s; border-left: 2px solid transparent; font-family: var(--mono); font-size: 11px; }
.scan-item:hover { background: rgba(127, 212, 255, 0.04); border-left-color: var(--data); }
.scan-item.active { background: rgba(127, 212, 255, 0.08); border-left-color: var(--data); }
.scan-glyph { width: 12px; text-align: center; color: var(--text-muted); }
.scan-glyph.star { color: var(--warning); }
.scan-glyph.station { color: var(--data); }
.scan-glyph.planet { color: var(--success); }
.scan-name { flex: 1; color: var(--text-primary); }
.scan-dist { color: var(--text-muted); font-size: 10px; }

.console-panel { flex: 1; display: flex; flex-direction: column; min-height: 180px; overflow: hidden; }
.console-head {
  padding: 10px 16px; font-family: var(--mono); font-size: 9px;
  letter-spacing: 0.25em; color: var(--text-muted); text-transform: uppercase;
  border-bottom: 1px solid var(--border-subtle);
  display: flex; justify-content: space-between; align-items: center;
  background: rgba(5, 7, 12, 0.5); flex-shrink: 0;
}
.console-head .status { color: var(--success); display: flex; gap: 6px; align-items: center; }
.console-head .status::before { content: ''; width: 5px; height: 5px; background: var(--success); border-radius: 50%; animation: pulse 2s infinite; }
.console-out {
  flex: 1; padding: 12px 16px; overflow-y: auto;
  font-family: var(--mono); font-size: 11.5px; line-height: 1.7;
  min-height: 0;
}
.console-line { margin: 1px 0; }
.console-line.cmd { color: var(--accent); }
.console-line.sys { color: var(--text-muted); }
.console-line.ok { color: var(--success); }
.console-line.data { color: var(--data); }
.console-line.warn { color: var(--warning); }
.console-line.err { color: var(--danger); }
.console-line.dim { color: var(--text-muted); font-style: italic; font-size: 10.5px; }

.console-input {
  border-top: 1px solid var(--border-subtle);
  padding: 10px 16px; display: flex; align-items: center; gap: 10px;
  background: rgba(5, 7, 12, 0.5); flex-shrink: 0;
}
.prompt { font-family: var(--mono); font-size: 12px; color: var(--accent); font-weight: 700; }
.console-input input {
  flex: 1; background: transparent; border: none; outline: none;
  color: var(--text-primary); font-family: var(--mono); font-size: 12px;
  caret-color: var(--accent);
}
.shortcuts {
  padding: 8px 16px; border-top: 1px solid var(--border-subtle);
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;
  background: rgba(5, 7, 12, 0.3); flex-shrink: 0;
}
.sc {
  font-family: var(--mono); font-size: 10px; letter-spacing: 0.1em;
  padding: 7px 4px; text-transform: uppercase; cursor: pointer;
  background: transparent; color: var(--text-secondary);
  border: 1px solid var(--border-subtle); transition: all 0.15s;
}
.sc:hover { color: var(--data); border-color: var(--data); background: rgba(127, 212, 255, 0.05); }
.sc-cmd { display: block; font-size: 8px; color: var(--text-muted); letter-spacing: 0.05em; margin-top: 2px; text-transform: lowercase; }

::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border-subtle); }
::-webkit-scrollbar-thumb:hover { background: var(--border-strong); }

.alerts-bar {
  position: absolute; bottom: 0; left: 260px; width: 820px;
  height: 28px; background: rgba(5, 7, 12, 0.8);
  border-top: 1px solid var(--border-subtle);
  display: flex; align-items: center; padding: 0 16px; gap: 20px;
  font-family: var(--mono); font-size: 10px; letter-spacing: 0.15em; text-transform: uppercase;
  z-index: 50; backdrop-filter: blur(4px);
}
.alert-label { color: var(--text-muted); }
.alert-val { color: var(--success); }
.alert-val.warn { color: var(--warning); }
.alert-val.err { color: var(--danger); }
.clock { margin-left: auto; color: var(--data); font-family: var(--mono); }

.calcul-banner {
  position: absolute; top: 0; left: 260px; width: 820px;
  background: rgba(127, 212, 255, 0.08);
  border-bottom: 1px solid rgba(127, 212, 255, 0.3);
  padding: 6px 16px; z-index: 60;
  font-family: var(--mono); font-size: 10px;
  display: flex; align-items: center; gap: 16px;
}
.calcul-banner .calc-label { color: var(--data); font-weight: 700; letter-spacing: 0.15em; }
.calcul-banner .calc-dest { color: var(--text-primary); }
.calcul-banner .calc-meta { color: var(--text-secondary); }
.calcul-banner .calc-actions { margin-left: auto; display: flex; gap: 8px; }
.calc-btn { font-family: var(--mono); font-size: 9px; letter-spacing: 0.1em; text-transform: uppercase; padding: 3px 8px; cursor: pointer; background: transparent; border: 1px solid; transition: all 0.15s; }
.calc-btn.cancel { color: var(--danger); border-color: var(--danger); }
.calc-btn.cancel:hover { background: rgba(239, 68, 68, 0.1); }
.calc-btn.improve { color: var(--data); border-color: var(--data); }
.calc-btn.improve:hover { background: rgba(127, 212, 255, 0.1); }
</style>
@endpush

@php
$paMax = $personnage->pa_max ?? 50;
$paVal = $personnage->points_action ?? 0;
$paPct = $paMax > 0 ? round($paVal / $paMax * 100) : 0;
$paClass = $paPct > 60 ? 'ok' : ($paPct > 30 ? 'mid' : 'low');

$enVal = $vaisseau->energie_actuelle ?? 0;
$enMax = $vaisseau->reserve ?? 100;
$enPct = $enMax > 0 ? round($enVal / $enMax * 100) : 0;
$enClass = $enPct > 60 ? 'ok' : ($enPct > 30 ? 'mid' : 'low');

$coqVal = $vaisseau->coque_actuelle ?? 100;
$coqMax = $vaisseau->coque_max ?? 100;
$coqPct = $coqMax > 0 ? round($coqVal / $coqMax * 100) : 100;
$coqClass = $coqPct > 60 ? 'ok' : ($coqPct > 30 ? 'mid' : 'low');

$bouVal = (int)($vaisseau->bouclier_actuel ?? 0);
$bouClass = $bouVal > 60 ? 'ok' : ($bouVal > 30 ? 'mid' : 'low');

$cargoVal = $vaisseau->cargaison_actuelle ?? $vaisseau->cargo_actuel ?? 0;
$cargoMax = $vaisseau->cargaison_max ?? $vaisseau->cargo_max ?? 20;
$cargoPct = $cargoMax > 0 ? round($cargoVal / $cargoMax * 100) : 0;
$cargoClass = $cargoPct < 80 ? 'ok' : ($cargoPct < 95 ? 'mid' : 'low');

$credits = $personnage->credits ?? 0;
$shipName = strtoupper($objetSpatial->nom ?? 'VAISSEAU');
$cmdName = strtoupper(trim(($personnage->prenom ?? '') . ' ' . ($personnage->nom ?? 'CMDR')));
$systemeName = $systemeActuel ? strtoupper($systemeActuel->nom) : 'ESPACE PROFOND';
$sectorLabel = "({$objetSpatial->secteur_x}, {$objetSpatial->secteur_y}, {$objetSpatial->secteur_z}) · {$systemeName}";
$scanCount = count($poisSecteur) + count($sautsDisponibles);

// Build starmap JSON for Three.js
$starmapSystems = [];
$starmapSystems[] = [
    'name' => $systemeActuel->nom ?? 'Étoile',
    'x' => 0, 'y' => 0, 'z' => 0,
    'type' => 'star', 'colorHex' => '#ffe680', 'size' => 1.8, 'dist' => 0,
    'id' => 0
];
foreach ($poisSecteur as $i => $poi) {
    // Angle équi-réparti pour l'affichage schématique
    $angle = $i * (M_PI * 2 / max(count($poisSecteur), 6));
    // Rayon proportionnel à la distance (échelle puissance 0.4 pour compresser les grandes distances)
    // Terre (1 UA) → r≈5, Mars (1.5 UA) → r≈5.8, Jupiter (5.2 UA) → r≈8.7, Neptune (30 UA) → r≈13
    $r = max(2.0, pow(max(0.1, $poi->distance) + 0.01, 0.4) * 5.0);
    $starmapSystems[] = [
        'name' => $poi->nom,
        'x' => round(cos($angle) * $r, 2),
        'y' => round(sin($i * 1.7) * 0.2, 2),
        'z' => round(sin($angle) * $r, 2),
        'type' => $poi->type_poi,
        'colorHex' => $poi->type_poi === 'station' ? '#7fd4ff' : '#4ade80',
        'size' => $poi->type_poi === 'station' ? 0.45 : 0.38,
        'dist' => round($poi->distance / 63241, 3),
        'id' => $poi->id, 'poiType' => $poi->type_poi,
        'distanceUA' => round($poi->distance, 1),
    ];
}
foreach ($sautsDisponibles as $dest) {
    $dx = ($dest->secteur_x - $objetSpatial->secteur_x);
    $dy = ($dest->secteur_y - $objetSpatial->secteur_y);
    $dz = ($dest->secteur_z - $objetSpatial->secteur_z);
    $x3 = ($dx == 0 && $dy == 0) ? ($dest->position_x ?? 1) * 2 : $dx * 9;
    $z3 = ($dx == 0 && $dy == 0) ? ($dest->position_y ?? 1) * 2 : $dy * 9;
    $y3 = $dz * 1.8;
    $starmapSystems[] = [
        'name' => $dest->nom,
        'x' => round($x3, 2), 'y' => round($y3, 2), 'z' => round($z3, 2),
        'type' => 'star',
        'colorHex' => $dest->accessible ? '#ffe680' : '#8b96a8',
        'size' => 1.2, 'dist' => round($dest->distance, 2),
        'id' => $dest->id,
        'accessible' => (bool)$dest->accessible,
        'energieRequise' => $dest->energieRequise,
        'paRequis' => $dest->paRequis,
        'isJumpTarget' => true,
    ];
}

$calculSaut = session('dernier_calcul_saut');
@endphp

@section('content')
<div class="viewport-notice">⚠ Interface conçue pour 1440×820px minimum</div>

<!-- HUD HEADER -->
<header class="hud-header">
  <div class="hud-brand">CONQUÊTE SPATIALE</div>
  <div class="hud-sep"></div>
  <div class="hud-block">
    <span class="hud-label">CMDR</span>
    <span class="hud-value">{{ $cmdName }}</span>
  </div>
  <div class="hud-block">
    <span class="hud-label">Secteur</span>
    <span class="hud-value data">{{ $sectorLabel }}</span>
  </div>
  <div class="hud-block">
    <span class="hud-label">Vaisseau</span>
    <span class="hud-value">{{ $shipName }}</span>
  </div>
  <div class="hud-sep"></div>
  <div class="gauge-group">
    <div class="gauge" title="Points d'action : {{ $paVal }}/{{ $paMax }}">
      <span class="gauge-icon">PA</span>
      <div class="gauge-bar"><div class="gauge-fill {{ $paClass }}" style="width: {{ $paPct }}%"></div></div>
      <span class="gauge-val">{{ $paVal }}/{{ $paMax }}</span>
    </div>
    <div class="gauge" title="Énergie : {{ $enVal }}/{{ $enMax }}">
      <span class="gauge-icon">⚡</span>
      <div class="gauge-bar"><div class="gauge-fill {{ $enClass }}" style="width: {{ $enPct }}%"></div></div>
      <span class="gauge-val">{{ $enPct }}%</span>
    </div>
    <div class="gauge" title="Coque : {{ $coqVal }}/{{ $coqMax }}">
      <span class="gauge-icon">◈</span>
      <div class="gauge-bar"><div class="gauge-fill {{ $coqClass }}" style="width: {{ $coqPct }}%"></div></div>
      <span class="gauge-val">{{ $coqPct }}%</span>
    </div>
    @if($vaisseau->bouclier_id)
    <div class="gauge" title="Bouclier : {{ $bouVal }}/100">
      <span class="gauge-icon">◎</span>
      <div class="gauge-bar"><div class="gauge-fill {{ $bouClass }}" style="width: {{ $bouVal }}%"></div></div>
      <span class="gauge-val">{{ $bouVal }}%</span>
    </div>
    @endif
    <div class="gauge" title="Cargaison : {{ $cargoVal }}/{{ $cargoMax }}t">
      <span class="gauge-icon">▣</span>
      <div class="gauge-bar"><div class="gauge-fill {{ $cargoClass }}" style="width: {{ $cargoPct }}%"></div></div>
      <span class="gauge-val">{{ $cargoVal }}/{{ $cargoMax }}t</span>
    </div>
  </div>
  <div class="hud-sep"></div>
  <div class="hud-block">
    <span class="hud-label">Crédits</span>
    <span class="hud-value accent">{{ number_format($credits, 0, ',', ' ') }} CR</span>
  </div>
  <div class="hud-spacer"></div>
  <form method="POST" action="{{ route('logout') }}" style="display:inline">
    @csrf
    <button type="submit" class="hud-btn">Déco</button>
  </form>
</header>

<!-- CALCUL EN COURS (si session) -->
@if($calculSaut)
<div class="calcul-banner">
  <span class="calc-label">⟡ CALCUL EN COURS</span>
  <span class="calc-dest">{{ $calculSaut['destination_nom'] }}
    @if($calculSaut['poi_cible'] !== 'systeme') → {{ $calculSaut['poi_nom'] }} @endif
  </span>
  <span class="calc-meta">
    {{ number_format($calculSaut['distance'], 1) }} AL ·
    {{ $calculSaut['energie_requise'] }} E ·
    {{ $calculSaut['pa_requis'] }} PA ·
    Jet {{ $calculSaut['jet_navigation'] }} ·
    Erreur {{ $calculSaut['score_erreur'] }}
    @if($calculSaut['est_critique']) · <span style="color:var(--warning)">★ CRITIQUE</span> @endif
    @if($calculSaut['est_espoir']) · <span style="color:var(--success)">↑ ESPOIR</span> @endif
    @if($calculSaut['est_peur']) · <span style="color:var(--danger)">↓ PEUR</span> @endif
  </span>
  <div class="calc-actions">
    <button class="calc-btn cancel" onclick="annulerCalculSaut()">✕ Annuler</button>
    <button class="calc-btn improve" onclick="ameliorerCalculSaut()">↑ Améliorer (1 PA)</button>
  </div>
</div>
@endif

<!-- MAIN STAGE -->
<div class="stage">

  <!-- LEFT NAV -->
  <nav class="nav">
    <div class="nav-section">
      <div class="nav-section-head">Vaisseau</div>
      <a href="{{ route('navire.timonerie') }}" class="nav-item active">Timonerie <span class="nav-cmd">→ timonerie</span></a>
      <a href="#" class="nav-item sub disabled">Position <span class="nav-cmd">→ position</span></a>
      <a href="#" class="nav-item sub disabled">Scanner <span class="nav-cmd">→ scan</span></a>
      <a href="{{ route('navire.ingenierie') }}" class="nav-item">Ingénierie <span class="nav-cmd">→ ingenierie</span></a>
      <a href="#" class="nav-item sub disabled">État systèmes <span class="nav-cmd">→ etat</span></a>
      <a href="#" class="nav-item sub disabled">Réparations <span class="nav-cmd">→ reparer</span></a>
      <a href="{{ route('navire.soute') }}" class="nav-item">Soute <span class="nav-cmd">→ soute</span></a>
      <a href="{{ route('navire.soute') }}" class="nav-item sub">Cargaison <span class="nav-cmd">→ cargaison</span></a>
      <a href="{{ route('navire.equipage') }}" class="nav-item">Équipage <span class="nav-cmd">→ equipage</span></a>
    </div>
    <div class="nav-section">
      <div class="nav-section-head">Navigation</div>
      <a href="{{ route('carte') }}" class="nav-item">Carte galactique <span class="nav-cmd">→ carte</span></a>
      <a href="#" class="nav-item disabled">Secteur actuel <span class="nav-cmd">→ secteur</span></a>
    </div>
    <div class="nav-section">
      <div class="nav-section-head">Communications</div>
      <a href="{{ route('navire.com') }}" class="nav-item">COM <span class="nav-cmd">→ com</span></a>
      <a href="#" class="nav-item sub disabled">Prix marchés <span class="nav-cmd">→ com prix</span></a>
      <a href="#" class="nav-item sub disabled">Messages <span class="nav-cmd">→ com msg</span></a>
    </div>
    <div class="nav-section">
      <div class="nav-section-head">Personnage</div>
      <a href="{{ route('personnage.dossier') }}" class="nav-item">Dossier <span class="nav-cmd">→ fiche</span></a>
      <a href="{{ route('jeu.profil') }}" class="nav-item">Profil <span class="nav-cmd">→ profil</span></a>
    </div>
    @if(auth()->user()->is_admin ?? false)
    <div class="nav-section">
      <div class="nav-section-head" style="color: var(--danger)">Administration</div>
      <a href="{{ route('admin.index') }}" class="nav-item" style="color: #ff6b6b;">Univers</a>
    </div>
    @endif
  </nav>

  <!-- CENTER — STARMAP -->
  <div class="starmap-wrap">
    <canvas id="starmap"></canvas>

    <div class="starmap-title">
      <span class="pulse"></span>
      <span>Timonerie · Carte perspective 60°</span>
      <span>—</span>
      <span class="accent">{{ $scanCount }} objets détectés</span>
    </div>

    <div class="legend">
      <div class="legend-head">Légende</div>
      <div class="legend-row"><span class="legend-glyph" style="color: var(--accent)">▲</span> Vaisseau</div>
      <div class="legend-row"><span class="legend-glyph" style="color: var(--warning)">★</span> Étoile / Système</div>
      <div class="legend-row"><span class="legend-glyph" style="color: var(--data)">⊙</span> Station</div>
      <div class="legend-row"><span class="legend-glyph" style="color: var(--success)">◇</span> Planète</div>
      <div class="legend-row"><span class="legend-glyph" style="color: var(--text-muted)">·</span> Inaccessible</div>
      <div class="legend-row" style="margin-top: 6px; padding-top: 6px; border-top: 1px solid var(--border-subtle);">
        <span style="color: var(--success)">━</span> Saut accessible
      </div>
      <div class="legend-row"><span style="color: var(--warning)">━</span> Saut coûteux</div>
      <div class="legend-row"><span style="color: var(--danger)">━</span> Saut impossible</div>
    </div>

    <div class="compass">
      <div class="compass-inner">
        <div class="compass-ring"></div>
        <span class="compass-label n">+Y</span>
        <span class="compass-label s">-Y</span>
        <span class="compass-label e">+X</span>
        <span class="compass-label w">-X</span>
        <div class="compass-dot"></div>
      </div>
    </div>

    <div class="starmap-controls">
      <button class="ctrl-btn" id="zoom-in" title="Zoom +">+</button>
      <button class="ctrl-btn" id="zoom-out" title="Zoom -">−</button>
      <button class="ctrl-btn" id="reset-view" title="Vue par défaut">⌂</button>
      <button class="ctrl-btn" id="toggle-grid" title="Grille">#</button>
    </div>

    <div class="sys-tooltip" id="tooltip">
      <h4 id="tt-name">—</h4>
      <div class="row"><span>Distance</span><span id="tt-dist">—</span></div>
      <div class="row"><span>Coût énergie</span><span id="tt-cost" class="cost">—</span></div>
      <div class="row"><span>Type</span><span id="tt-type">—</span></div>
      <div class="row" style="margin-top: 6px; font-size: 9px; color: var(--text-muted);">Cliquer pour cibler</div>
    </div>
  </div>

  <!-- RIGHT — INSTRUMENTS + CONSOLE -->
  <div class="right-col">

    <!-- Instruments -->
    <div class="instruments">
      <div class="inst-head">
        <span>Instruments</span>
        <span class="live">LIVE</span>
      </div>
      <div class="inst-grid">
        <div class="inst">
          <span class="inst-label">Mode nav.</span>
          <span class="inst-val data" id="inst-mode">Stationnaire</span>
        </div>
        <div class="inst">
          <span class="inst-label">Secteur</span>
          <span class="inst-val">{{ $objetSpatial->secteur_x }},{{ $objetSpatial->secteur_y }},{{ $objetSpatial->secteur_z }}</span>
        </div>
        <div class="inst">
          <span class="inst-label">Énergie</span>
          <span class="inst-val" id="inst-energie">{{ $enVal }}/{{ $enMax }}</span>
        </div>
        <div class="inst">
          <span class="inst-label">PA</span>
          <span class="inst-val data" id="inst-pa">{{ $paVal }}/{{ $paMax }}</span>
        </div>
      </div>
    </div>

    <!-- Destination verrouillée -->
    <div class="destination" id="dest-panel">
      @if($calculSaut)
      <div class="dest-head" id="dest-head">Destination verrouillée</div>
      <div class="dest-name" id="dest-name">{{ $calculSaut['destination_nom'] }}</div>
      <div class="dest-meta" id="dest-meta">
        {{ number_format($calculSaut['distance'], 2) }} AL ·
        Jet {{ $calculSaut['jet_navigation'] }} ·
        Erreur {{ $calculSaut['score_erreur'] }}
      </div>
      <div class="dest-cost">
        <span>Coût du saut</span>
        <span class="val" id="dest-cost">{{ $calculSaut['energie_requise'] }} E · {{ $calculSaut['pa_requis'] }} PA</span>
      </div>
      <div class="dest-actions" id="dest-actions">
        <button class="btn danger" id="btn-annuler" onclick="annulerCalculSaut()">Annuler</button>
        <button class="btn primary" id="btn-action" onclick="effectuerSaut({{ $calculSaut['destination_id'] }})">
          Initier saut<span class="btn-cmd">→ saut effectuer</span>
        </button>
      </div>
      @else
      <div class="dest-head" id="dest-head">Destination</div>
      <div class="dest-name empty" id="dest-name">Aucune cible sélectionnée</div>
      <div class="dest-meta" id="dest-meta" style="color: var(--text-muted); font-size: 11px;">
        Cliquez sur la carte ou le scanner
      </div>
      <div class="dest-cost" style="display:none;" id="dest-cost-row">
        <span>Coût</span>
        <span class="val" id="dest-cost">—</span>
      </div>
      <div class="dest-actions" id="dest-actions">
        <button class="btn" id="btn-annuler" onclick="clearTarget()" disabled>Annuler</button>
        <button class="btn primary" id="btn-action" disabled>
          Sélectionner cible<span class="btn-cmd">→ cible [nom]</span>
        </button>
      </div>
      @endif
    </div>

    <!-- Scanner courte portée -->
    <div class="scanner">
      <div class="scanner-head">
        <span>Scanner — secteur actuel</span>
        <span class="range">{{ $systemeActuel ? $systemeActuel->nom : 'Espace profond' }}</span>
      </div>
      <div class="scan-list" id="scan-list">
        @if($systemeActuel)
        <div class="scan-item" onclick="selectScanItem(this, 'star', 0, '{{ e($systemeActuel->nom) }}', 0, false)">
          <span class="scan-glyph star">★</span>
          <span class="scan-name">{{ $systemeActuel->nom }}</span>
          <span class="scan-dist">étoile</span>
        </div>
        @endif
        @forelse($poisSecteur as $poi)
        @php
          $distGm = $poi->distance * 149.6;
          $distDisplay = $distGm >= 1000
            ? number_format($distGm/1000, 2).' G km'
            : number_format($distGm, 2).' Gm';
          $glyphClass = $poi->type_poi === 'station' ? 'station' : 'planet';
          $glyphChar = $poi->type_poi === 'station' ? '⊙' : '◇';
        @endphp
        <div class="scan-item" onclick="selectScanItem(this, '{{ $poi->type_poi }}', {{ $poi->id }}, '{{ e($poi->nom) }}', {{ $poi->distance }}, false)">
          <span class="scan-glyph {{ $glyphClass }}">{{ $glyphChar }}</span>
          <span class="scan-name">{{ $poi->nom }}</span>
          <span class="scan-dist">{{ $distDisplay }}</span>
        </div>
        @empty
        <div style="font-family: var(--mono); font-size: 10px; color: var(--text-muted); padding: 8px; text-align: center;">
          Aucun POI détecté
        </div>
        @endforelse
      </div>
    </div>

    <!-- Console -->
    <div class="console-panel">
      <div class="console-head">
        <span>Console</span>
        <span class="status">Connecté</span>
      </div>
      <div class="console-out" id="console-out">
        <div class="console-line sys">> Timonerie initialisée</div>
        <div class="console-line ok">> Connexion établie · {{ $shipName }}</div>
        <div class="console-line sys">> Position : {{ $sectorLabel }}</div>
        <div class="console-line dim">> Tapez 'help' pour les commandes.</div>
        <div class="console-line sys">────────────────────────────</div>
        @if($calculSaut)
        <div class="console-line data">> Calcul de saut en cours → {{ $calculSaut['destination_nom'] }}</div>
        <div class="console-line warn">>  Jet {{ $calculSaut['jet_navigation'] }} · Erreur {{ $calculSaut['score_erreur'] }} · Précision {{ number_format(100 - $calculSaut['score_erreur'] * 0.5, 1) }}%</div>
        @endif
      </div>
      <div class="console-input">
        <span class="prompt">{{ $cmdName }}@{{ $shipName }} ▸</span>
        <input type="text" id="cmd-input" placeholder="commande..." autocomplete="off">
      </div>
      <div class="shortcuts">
        <button class="sc" onclick="handleShortcut('scan')">Scan<span class="sc-cmd">scan</span></button>
        <button class="sc" onclick="handleShortcut('saut')">Saut<span class="sc-cmd">saut init</span></button>
        <button class="sc" onclick="handleShortcut('recharger')">Recharge<span class="sc-cmd">recharger</span></button>
        <button class="sc" onclick="handleShortcut('position')">Position<span class="sc-cmd">position</span></button>
        <button class="sc" onclick="handleShortcut('inv')">Soute<span class="sc-cmd">inv</span></button>
        <button class="sc" onclick="handleShortcut('help')">Aide<span class="sc-cmd">help</span></button>
      </div>
    </div>

  </div>
</div>

<!-- ALERTS BAR -->
<div class="alerts-bar">
  <span class="alert-label">Alertes</span>
  <span class="alert-val" id="alert-val">
    @if($enPct < 20) <span class="err">ÉNERGIE CRITIQUE</span>
    @elseif($coqPct < 30) <span class="warn">COQUE ENDOMMAGÉE</span>
    @else Aucune @endif
  </span>
  <span class="alert-label">· PA</span>
  <span class="alert-val {{ $paVal < 5 ? 'warn' : '' }}">{{ $paVal }}</span>
  <span class="alert-label">· Secteur</span>
  <span class="alert-val">{{ $objetSpatial->secteur_x }},{{ $objetSpatial->secteur_y }},{{ $objetSpatial->secteur_z }}</span>
  <span class="clock" id="local-clock">--:--:-- UTC</span>
</div>

<!-- Three.js importmap -->
<script type="importmap">
{
  "imports": {
    "three": "https://unpkg.com/three@0.160.0/build/three.module.js"
  }
}
</script>

<script type="module">
import * as THREE from 'three';

const canvas = document.getElementById('starmap');
const wrap = canvas.parentElement;

const scene = new THREE.Scene();
scene.fog = new THREE.FogExp2(0x05070c, 0.008);

const camera = new THREE.PerspectiveCamera(60, wrap.clientWidth / wrap.clientHeight, 0.1, 2000);
let theta = 0, phi = 1, radius = 80;
function updateCamera() {
  camera.position.x = radius * Math.sin(phi) * Math.sin(theta);
  camera.position.y = radius * Math.cos(phi);
  camera.position.z = radius * Math.sin(phi) * Math.cos(theta);
  camera.lookAt(0, 0, 0);
}
updateCamera();

const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
renderer.setSize(wrap.clientWidth, wrap.clientHeight);
renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

// Starfield lointain
const sfGeo = new THREE.BufferGeometry();
const sfCount = 3000;
const sfPos = new Float32Array(sfCount * 3);
const sfCol = new Float32Array(sfCount * 3);
for (let i = 0; i < sfCount; i++) {
  const r = 400 + Math.random() * 600;
  const theta2 = Math.random() * Math.PI * 2;
  const phi2 = Math.acos(2 * Math.random() - 1);
  sfPos[i*3] = r * Math.sin(phi2) * Math.cos(theta2);
  sfPos[i*3+1] = r * Math.sin(phi2) * Math.sin(theta2);
  sfPos[i*3+2] = r * Math.cos(phi2);
  const b = 0.3 + Math.random() * 0.7;
  sfCol[i*3] = b; sfCol[i*3+1] = b; sfCol[i*3+2] = b + Math.random() * 0.2;
}
sfGeo.setAttribute('position', new THREE.BufferAttribute(sfPos, 3));
sfGeo.setAttribute('color', new THREE.BufferAttribute(sfCol, 3));
scene.add(new THREE.Points(sfGeo, new THREE.PointsMaterial({ size: 0.8, vertexColors: true, transparent: true, opacity: 0.8, sizeAttenuation: false })));

// Grille galactique
const gridGroup = new THREE.Group();
const gMat = new THREE.LineBasicMaterial({ color: 0x1f2937, transparent: true, opacity: 0.6 });
const gMatM = new THREE.LineBasicMaterial({ color: 0x2d3b4f, transparent: true, opacity: 0.8 });
for (let i = -20; i <= 20; i++) {
  const x = i * 5;
  const isMaj = i % 5 === 0;
  gridGroup.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints([new THREE.Vector3(x,0,-100), new THREE.Vector3(x,0,100)]), isMaj ? gMatM : gMat));
  gridGroup.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints([new THREE.Vector3(-100,0,x), new THREE.Vector3(100,0,x)]), isMaj ? gMatM : gMat));
}
scene.add(gridGroup);

// Cercle de portée scanner
const scanRing = new THREE.Mesh(
  new THREE.RingGeometry(14.85, 15, 64),
  new THREE.MeshBasicMaterial({ color: 0x7fd4ff, transparent: true, opacity: 0.3, side: THREE.DoubleSide })
);
scanRing.rotation.x = -Math.PI / 2;
scene.add(scanRing);

// Glow texture helper
function makeGlowTexture(colorInput) {
  const size = 128;
  const c2 = document.createElement('canvas');
  c2.width = c2.height = size;
  const ctx = c2.getContext('2d');
  const grad = ctx.createRadialGradient(size/2,size/2,0, size/2,size/2,size/2);
  const col = new THREE.Color(colorInput);
  const rgb = `${Math.floor(col.r*255)},${Math.floor(col.g*255)},${Math.floor(col.b*255)}`;
  grad.addColorStop(0, `rgba(${rgb},1)`);
  grad.addColorStop(0.2, `rgba(${rgb},0.5)`);
  grad.addColorStop(0.5, `rgba(${rgb},0.15)`);
  grad.addColorStop(1, `rgba(${rgb},0)`);
  ctx.fillStyle = grad; ctx.fillRect(0,0,size,size);
  return new THREE.CanvasTexture(c2);
}

// Systèmes depuis les données Laravel
const systemsData = @json($starmapSystems);
const systemMeshes = [];
const systemGroup = new THREE.Group();
scene.add(systemGroup);

systemsData.forEach(sys => {
  const group = new THREE.Group();
  group.position.set(sys.x, sys.y, sys.z);
  group.userData = sys;

  const core = new THREE.Mesh(
    new THREE.SphereGeometry(sys.size * 0.5, 16, 16),
    new THREE.MeshBasicMaterial({ color: new THREE.Color(sys.colorHex) })
  );
  group.add(core);

  const glow = new THREE.Sprite(new THREE.SpriteMaterial({
    map: makeGlowTexture(sys.colorHex),
    blending: THREE.AdditiveBlending,
    transparent: true, opacity: 0.8
  }));
  glow.scale.set(sys.size * 3, sys.size * 3, 1);
  group.add(glow);

  if (sys.y !== 0) {
    const lineMat = new THREE.LineBasicMaterial({ color: new THREE.Color(sys.colorHex), transparent: true, opacity: 0.2 });
    const lineGeo = new THREE.BufferGeometry().setFromPoints([new THREE.Vector3(0,0,0), new THREE.Vector3(0,-sys.y,0)]);
    group.add(new THREE.Line(lineGeo, lineMat));
    const marker = new THREE.Mesh(
      new THREE.CircleGeometry(sys.size * 0.3, 16),
      new THREE.MeshBasicMaterial({ color: new THREE.Color(sys.colorHex), transparent: true, opacity: 0.4, side: THREE.DoubleSide })
    );
    marker.rotation.x = -Math.PI / 2;
    marker.position.y = -sys.y;
    group.add(marker);
  }

  systemGroup.add(group);
  systemMeshes.push({ group, core, glow, data: sys });
});

// Vaisseau (marqueur au centre)
const shipGroup = new THREE.Group();
const shipMesh = new THREE.Mesh(
  new THREE.ConeGeometry(0.4, 1, 4),
  new THREE.MeshBasicMaterial({ color: 0xff8a3d })
);
shipMesh.rotation.x = Math.PI / 2;
shipMesh.rotation.z = Math.PI / 4;
shipGroup.add(shipMesh);
const halo = new THREE.Sprite(new THREE.SpriteMaterial({
  map: makeGlowTexture('#ff8a3d'),
  blending: THREE.AdditiveBlending, transparent: true, opacity: 0.9
}));
halo.scale.set(4, 4, 1);
shipGroup.add(halo);
scene.add(shipGroup);

// Ligne de saut
let destLine = null;
function setDestinationLine(sys) {
  if (destLine) scene.remove(destLine);
  if (!sys) return;
  const costRatio = sys.dist / 25;
  const color = costRatio <= 0.3 ? 0x4ade80 : costRatio <= 0.7 ? 0xfbbf24 : 0xef4444;
  const curve = new THREE.CatmullRomCurve3([
    new THREE.Vector3(0,0,0),
    new THREE.Vector3(sys.x*0.4, Math.abs(sys.y)+2, sys.z*0.4),
    new THREE.Vector3(sys.x, sys.y, sys.z)
  ]);
  const geo = new THREE.BufferGeometry().setFromPoints(curve.getPoints(50));
  const mat = new THREE.LineDashedMaterial({ color, dashSize: 0.4, gapSize: 0.2, transparent: true, opacity: 0.8 });
  destLine = new THREE.Line(geo, mat);
  destLine.computeLineDistances();
  scene.add(destLine);
}

// Contrôles caméra
let isDragging = false, prevMouse = { x: 0, y: 0 };
canvas.addEventListener('mousedown', e => { isDragging = true; prevMouse = { x: e.clientX, y: e.clientY }; });
window.addEventListener('mouseup', () => { isDragging = false; canvas.style.cursor = 'grab'; });
window.addEventListener('mousemove', e => {
  if (!isDragging) return;
  theta -= (e.clientX - prevMouse.x) * 0.005;
  phi = Math.max(0.2, Math.min(Math.PI - 0.2, phi + (e.clientY - prevMouse.y) * 0.005));
  prevMouse = { x: e.clientX, y: e.clientY };
  updateCamera();
  canvas.style.cursor = 'grabbing';
});
canvas.addEventListener('wheel', e => {
  e.preventDefault();
  radius = Math.max(20, Math.min(200, radius + e.deltaY * 0.08));
  updateCamera();
}, { passive: false });
document.getElementById('zoom-in').addEventListener('click', () => { radius = Math.max(20, radius - 10); updateCamera(); });
document.getElementById('zoom-out').addEventListener('click', () => { radius = Math.min(200, radius + 10); updateCamera(); });
document.getElementById('reset-view').addEventListener('click', () => { theta = 0; phi = 1; radius = 80; updateCamera(); });
let gridVisible = true;
document.getElementById('toggle-grid').addEventListener('click', () => { gridVisible = !gridVisible; gridGroup.visible = gridVisible; });
canvas.style.cursor = 'grab';

// Raycaster hover / click
const raycaster = new THREE.Raycaster();
const mouse = new THREE.Vector2();
const tooltip = document.getElementById('tooltip');
const cores = systemMeshes.map(m => m.core);

canvas.addEventListener('mousemove', e => {
  if (isDragging) return;
  const rect = canvas.getBoundingClientRect();
  mouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
  mouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;
  raycaster.setFromCamera(mouse, camera);
  const hits = raycaster.intersectObjects(cores);
  if (hits.length > 0) {
    const sys = hits[0].object.parent.userData;
    tooltip.classList.add('show');
    tooltip.style.left = (e.clientX - rect.left + 14) + 'px';
    tooltip.style.top = (e.clientY - rect.top + 14) + 'px';
    document.getElementById('tt-name').textContent = sys.name;
    document.getElementById('tt-dist').textContent = sys.dist > 0 ? sys.dist.toFixed(2) + ' AL' : 'Système actuel';
    const el = document.getElementById('tt-cost');
    if (sys.isJumpTarget) {
      el.textContent = sys.energieRequise + ' E · ' + sys.paRequis + ' PA';
      el.className = 'cost ' + (sys.accessible ? 'ok' : 'ko');
    } else {
      el.textContent = sys.distanceUA ? sys.distanceUA + ' UA' : '—';
      el.className = 'cost ok';
    }
    document.getElementById('tt-type').textContent = sys.isJumpTarget ? 'Saut hyperspace' : (sys.poiType || sys.type);
    canvas.style.cursor = 'pointer';
  } else {
    tooltip.classList.remove('show');
    canvas.style.cursor = 'grab';
  }
});

canvas.addEventListener('click', e => {
  const rect = canvas.getBoundingClientRect();
  mouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
  mouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;
  raycaster.setFromCamera(mouse, camera);
  const hits = raycaster.intersectObjects(cores);
  if (hits.length > 0) {
    const sys = hits[0].object.parent.userData;
    if (sys.id === 0) return; // current star, no action
    if (sys.isJumpTarget) {
      lockJumpTarget(sys);
    } else {
      lockLocalTarget(sys);
    }
    setDestinationLine(sys);
    consoleLog('> cible ' + sys.name.toLowerCase().replace(/[^a-z0-9]/g, '-'), 'cmd');
    consoleLog('  Cible verrouillée : ' + sys.name, 'data');
  }
});

// Resize
window.addEventListener('resize', () => {
  camera.aspect = wrap.clientWidth / wrap.clientHeight;
  camera.updateProjectionMatrix();
  renderer.setSize(wrap.clientWidth, wrap.clientHeight);
});

// Animate
function animate(t) {
  requestAnimationFrame(animate);
  shipGroup.rotation.y = t * 0.0005;
  systemMeshes.forEach(m => {
    const p = 1 + Math.sin(t * 0.002 + m.data.x) * 0.05;
    m.glow.scale.set(m.data.size * 3 * p, m.data.size * 3 * p, 1);
  });
  scanRing.rotation.z = t * 0.0002;
  renderer.render(scene, camera);
}
animate(0);
</script>

<script>
// ============ CONSTANTES ============
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
const consoleOut = document.getElementById('console-out');
const cmdInput = document.getElementById('cmd-input');
let lockedTargetId = null;
let lockedTargetType = null; // 'jump' | 'local'

// ============ CONSOLE ============
function consoleLog(text, type = 'sys') {
  if (!consoleOut) return;
  const div = document.createElement('div');
  div.className = 'console-line ' + type;
  div.textContent = text;
  consoleOut.appendChild(div);
  consoleOut.scrollTop = consoleOut.scrollHeight;
}

function appendToConsole(text, cssClass) {
  // Mapping from old Tailwind classes to new CSS classes
  const map = {
    'text-green-400': 'ok', 'text-cyan-400': 'data', 'text-red-400': 'err',
    'text-yellow-400': 'warn', 'text-gray-300': 'sys', 'text-gray-500': 'dim',
  };
  const type = map[cssClass] || 'sys';
  consoleLog(text, type);
}

// Commande texte via input
cmdInput?.addEventListener('keydown', e => {
  if (e.key === 'Enter' && cmdInput.value.trim()) {
    const cmd = cmdInput.value.trim();
    cmdInput.value = '';
    sendCommand(cmd);
  }
});

function sendCommand(cmd) {
  consoleLog('> ' + cmd, 'cmd');
  fetch('{{ route("command") }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    body: JSON.stringify({ command: cmd })
  })
  .then(r => r.json())
  .then(data => {
    if (data.message) {
      data.message.split('\n').forEach(line => {
        if (line.trim()) consoleLog(line, data.success ? 'ok' : 'err');
      });
    }
    if (data.energie_actuelle !== undefined) updateGaugeEnergy(data.energie_actuelle);
    if (data.pa_restants !== undefined) updateGaugePA(data.pa_restants);
  })
  .catch(err => consoleLog('[ERREUR] ' + err.message, 'err'));
}

function updateGameInfo(data) {
  if (data.energie_actuelle !== undefined) updateGaugeEnergy(data.energie_actuelle);
  if (data.pa_restants !== undefined) updateGaugePA(data.pa_restants);
}

function updateGaugeEnergy(val) {
  document.getElementById('inst-energie').textContent = val + '/{{ $enMax }}';
}
function updateGaugePA(val) {
  document.getElementById('inst-pa').textContent = val + '/{{ $paMax }}';
}

// ============ CIBLES ============
function lockJumpTarget(sys) {
  lockedTargetId = sys.id;
  lockedTargetType = 'jump';
  document.getElementById('dest-head').textContent = 'Destination verrouillée';
  document.getElementById('dest-name').textContent = sys.name;
  document.getElementById('dest-name').className = 'dest-name';
  document.getElementById('dest-meta').textContent = sys.dist.toFixed(2) + ' AL · ' + (sys.energieRequise || '?') + ' E · ' + (sys.paRequis || '?') + ' PA';
  const costRow = document.getElementById('dest-cost-row');
  if (costRow) costRow.style.display = 'flex';
  const costEl = document.getElementById('dest-cost');
  if (costEl) costEl.textContent = (sys.energieRequise || '?') + ' E · ' + (sys.paRequis || '?') + ' PA';

  const btnAnnuler = document.getElementById('btn-annuler');
  const btnAction = document.getElementById('btn-action');
  btnAnnuler.disabled = false;
  btnAnnuler.onclick = clearTarget;
  btnAnnuler.className = 'btn';
  btnAnnuler.textContent = 'Annuler';

  btnAction.disabled = !sys.accessible;
  btnAction.className = 'btn primary';
  btnAction.innerHTML = 'Calculer saut<span class="btn-cmd">→ saut calculer</span>';
  btnAction.onclick = () => calculerSaut(sys.id);
}

function lockLocalTarget(sys) {
  lockedTargetId = sys.id;
  lockedTargetType = 'local';
  document.getElementById('dest-head').textContent = 'Cible locale';
  document.getElementById('dest-name').textContent = sys.name;
  document.getElementById('dest-name').className = 'dest-name';
  document.getElementById('dest-meta').textContent = sys.distanceUA ? sys.distanceUA + ' UA' : 'Position locale';
  const costRow = document.getElementById('dest-cost-row');
  if (costRow) costRow.style.display = 'none';

  const btnAnnuler = document.getElementById('btn-annuler');
  const btnAction = document.getElementById('btn-action');
  btnAnnuler.disabled = false;
  btnAnnuler.onclick = clearTarget;
  btnAnnuler.textContent = 'Annuler';
  btnAnnuler.className = 'btn';

  btnAction.disabled = false;
  btnAction.className = 'btn primary';
  btnAction.innerHTML = 'S\'approcher<span class="btn-cmd">→ approcher</span>';
  btnAction.onclick = () => sApprocher(sys.id, sys.poiType);
}

function selectScanItem(el, type, id, name, distUA, isJump) {
  document.querySelectorAll('.scan-item.active').forEach(i => i.classList.remove('active'));
  el.classList.add('active');
  const sysData = { id, name, type, distanceUA: distUA, poiType: type, isJumpTarget: isJump, dist: distUA / 63241 };
  if (isJump) {
    lockJumpTarget(sysData);
  } else if (id > 0) {
    lockLocalTarget(sysData);
  }
  consoleLog('> cible ' + name.toLowerCase().replace(/[^a-z0-9]/g, '-'), 'cmd');
  consoleLog('  Cible : ' + name, 'data');
}

function clearTarget() {
  lockedTargetId = null;
  lockedTargetType = null;
  document.getElementById('dest-head').textContent = 'Destination';
  document.getElementById('dest-name').textContent = 'Aucune cible sélectionnée';
  document.getElementById('dest-name').className = 'dest-name empty';
  document.getElementById('dest-meta').textContent = 'Cliquez sur la carte ou le scanner';
  const costRow = document.getElementById('dest-cost-row');
  if (costRow) costRow.style.display = 'none';
  document.getElementById('btn-annuler').disabled = true;
  document.getElementById('btn-action').disabled = true;
  document.getElementById('btn-action').innerHTML = 'Sélectionner cible<span class="btn-cmd">→ cible [nom]</span>';
  document.querySelectorAll('.scan-item.active').forEach(i => i.classList.remove('active'));
}

// ============ NAVIGATION ============
async function calculerSaut(destinationId, poiId = 'systeme') {
  consoleLog('> calculer-saut ' + destinationId, 'cmd');
  try {
    const r = await fetch('{{ route("navire.timonerie.calculer-saut") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ destination_id: destinationId, poi_id: poiId })
    });
    const data = await r.json();
    if (data.error) {
      consoleLog('[ERREUR] ' + data.error, 'err');
      return;
    }
    consoleLog('  Calcul vers ' + data.destination, 'data');
    consoleLog('  Distance : ' + data.distance + ' AL · Énergie : ' + data.energieRequise + ' · PA : ' + data.paRequis, 'sys');
    consoleLog('  Jet nav : ' + data.jetNavigation + ' · Erreur : ' + data.scoreErreur + ' · Précision : ' + data.precision + '%', 'sys');
    if (!data.accessible) {
      consoleLog('  ⚠ Ressources insuffisantes', 'warn');
    } else {
      consoleLog('  ✓ Saut calculé — prêt à initier', 'ok');
      // Mettre à jour le bouton pour initier le saut
      const btnAction = document.getElementById('btn-action');
      btnAction.innerHTML = 'Initier saut<span class="btn-cmd">→ saut effectuer</span>';
      btnAction.onclick = () => effectuerSaut(destinationId, poiId);
      btnAction.disabled = false;
    }
    location.reload(); // Recharger pour afficher le banner calcul
  } catch (e) {
    consoleLog('[ERREUR] ' + e.message, 'err');
  }
}

async function effectuerSaut(destinationId, poiId = 'systeme') {
  consoleLog('> saut-effectuer ' + destinationId, 'cmd');
  try {
    const r = await fetch('{{ route("navire.timonerie.effectuer-saut") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ destination_id: destinationId, poi_id: poiId })
    });
    const data = await r.json();
    if (data.error) {
      consoleLog('[ERREUR] ' + data.error, 'err');
      if (data.action === 'calculer_requis') {
        consoleLog('  → Calculez d\'abord le saut', 'warn');
      }
      return;
    }
    consoleLog('  ✓ ' + data.message, 'ok');
    consoleLog('  Jet : ' + data.jetNavigation + ' · Erreur : ' + data.scoreErreur + ' · Précision : ' + data.precision + '%', 'sys');
    consoleLog('  Énergie restante : ' + data.energieRestante + ' · PA : ' + data.paRestants, 'sys');
    updateGameInfo({ energie_actuelle: data.energieRestante, pa_restants: data.paRestants });
    setTimeout(() => location.reload(), 2000);
  } catch (e) {
    consoleLog('[ERREUR] ' + e.message, 'err');
  }
}

async function sApprocher(poiId, poiType) {
  consoleLog('> approcher ' + poiId, 'cmd');
  try {
    const r = await fetch('{{ route("navire.timonerie.s-approcher") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ poi_id: poiId, poi_type: poiType })
    });
    const data = await r.json();
    if (data.error) { consoleLog('[ERREUR] ' + data.error, 'err'); return; }
    consoleLog('  ✓ ' + data.message, 'ok');
    if (data.pourcentageTrajet) consoleLog('  ⚠ Trajet partiel : ' + data.pourcentageTrajet + '%', 'warn');
    const distGm = (data.distanceParcourue * 149.6);
    consoleLog('  Parcouru : ' + distGm.toFixed(0) + ' Gm', 'sys');
    consoleLog('  Énergie : -' + data.energieConsommee + ' (reste : ' + data.energieRestante + ')', 'sys');
    updateGameInfo({ energie_actuelle: data.energieRestante, pa_restants: data.paRestants });
    setTimeout(() => location.reload(), 2000);
  } catch (e) {
    consoleLog('[ERREUR] ' + e.message, 'err');
  }
}

async function sAmarrer(stationId) {
    try {
        const response = await fetch('{{ route("navire.timonerie.s-amarrer") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ station_id: stationId })
        });
        const data = await response.json();

        if (data.error) {
            // Afficher l'erreur dans la console
            sendCommand(`s-amarrer ${stationId}`);
            appendToConsole('[ERREUR] ' + data.error + (data.message ? '\n' + data.message : ''), 'text-red-400');
        } else if (data.success) {
            // Convert distance from UA to Gm/G km
            const distanceGm = data.distance * 149.6;
            const distanceDisplay = distanceGm >= 1000
                ? (distanceGm / 1000).toFixed(2) + ' G km'
                : distanceGm.toFixed(2) + ' Gm';

            // Afficher le résultat dans la console
            sendCommand(`s-amarrer ${stationId}`);
            appendToConsole('✓ ' + data.message, 'text-green-400');
            appendToConsole('Station: ' + data.station.nom, 'text-gray-300');
            appendToConsole('Distance: ' + data.distance + ' UA (' + distanceDisplay + ')', 'text-gray-300');
            appendToConsole('Ressources restantes:', 'text-gray-300');
            appendToConsole('  Énergie: ' + data.energieRestante, 'text-gray-300');
            appendToConsole('  PA: ' + data.paRestants, 'text-gray-300');
            appendToConsole('---', 'text-gray-500');
            appendToConsole('📢 Vous avez maintenant accès au menu Station !', 'text-cyan-400');
            appendToConsole('→ <a href="{{ route('station.hall') }}" class="text-cyan-400 underline font-bold">Accéder au Hall de la Station</a>', 'text-cyan-400');

            // Mettre à jour les informations du jeu
            updateGameInfo({
                energie_actuelle: data.energieRestante,
                pa_restants: data.paRestants
            });

            // Rediriger vers le hall de la station après un court délai
            setTimeout(() => {
                window.location.href = '{{ route('station.hall') }}';
            }, 2000);
        } else {
            sendCommand(`s-amarrer ${stationId}`);
            appendToConsole(data.message || 'Amarrage effectué', 'text-gray-300');
        }
    } catch (error) {
        sendCommand(`s-amarrer ${stationId}`);
        appendToConsole('[ERREUR] Erreur: ' + error.message, 'text-red-400');
    }
}
</script>
{{-- Inclure le calculateur orbital JavaScript --}}
<script src="{{ asset('js/orbital-calculator.js') }}"></script>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

// ============================================================================
// SYSTÈME DE CALCUL ORBITAL CLIENT-SIDE
// ============================================================================

// Données pour calculs orbitaux
// IMPORTANT: Tous les objets sont dans le même secteur, donc on utilise uniquement position_ en cUA
@if($systemeActuel)
const systemeData = {
    id: {{ $systemeActuel->id }},
    nom: "{{ $systemeActuel->nom }}",
    // Position système en cUA (intra-secteur uniquement)
    position_x: {{ $systemeActuel->position_x ?? 0 }},
    position_y: {{ $systemeActuel->position_y ?? 0 }},
    position_z: {{ $systemeActuel->position_z ?? 0 }}
};
@else
const systemeData = null;
@endif

const vaisseauData = {
    // Position vaisseau en cUA (intra-secteur uniquement)
    position_x: {{ $objetSpatial->position_x ?? 0 }},
    position_y: {{ $objetSpatial->position_y ?? 0 }},
    position_z: {{ $objetSpatial->position_z ?? 0 }},
};

const dateJeu = new Date('{{ $dateJeuActuelle->toIso8601String() }}');

// Fonction pour mettre à jour les distances des planètes en temps réel
function mettreAJourDistancesPlanetes() {
    if (!systemeData) return; // Espace profond

    // DEBUG: Afficher les données système et vaisseau
    console.log('=== DEBUG POSITIONS ===');
    console.log('Système:', systemeData);
    console.log('Vaisseau:', vaisseauData);
    console.log('Date jeu:', dateJeu);

    const timestampJours = OrbitalCalculator.dateToJours(dateJeu);
    console.log('Timestamp jours:', timestampJours);

    // Pour chaque planète dans la liste
    document.querySelectorAll('[data-planete-id]').forEach(element => {
        const planeteId = element.dataset.planeteId;
        const planeteData = JSON.parse(element.dataset.planeteOrbital || '{}');

        if (!planeteData.distance_etoile) return; // Pas de données orbitales

        try {
            // DEBUG: Afficher les données de la première planète
            if (planeteId === document.querySelector('[data-planete-id]').dataset.planeteId) {
                console.log('Planète exemple:', planeteData);
            }

            // Calculer distance en temps réel avec OrbitalCalculator
            const distance = OrbitalCalculator.calculerDistance(
                planeteData,
                systemeData,
                vaisseauData,
                timestampJours
            );

            // Mettre à jour l'affichage
            const distanceElement = element.querySelector('.planete-distance');
            if (distanceElement) {
                distanceElement.textContent = distance.toFixed(2) + ' UA';

                // Ajouter indicateur que c'est calculé côté client
                distanceElement.title = 'Distance calculée en temps réel (client-side)';
            }

            // Debug: afficher dans la console
            console.log(`Planète ${planeteData.nom} (ID ${planeteData.id}): ${distance.toFixed(2)} UA (calculé client-side)`);
        } catch (error) {
            console.error(`Erreur calcul orbital planète ${planeteId}:`, error);
        }
    });
    const data = await r.json();
    if (data.error) { consoleLog('[ERREUR] ' + data.error, 'err'); return; }
    consoleLog('  ✓ ' + data.message, 'ok');
    consoleLog('  Accès station activé — redirection...', 'data');
    setTimeout(() => window.location.href = '{{ route("station.hall") }}', 2000);
  } catch (e) {
    consoleLog('[ERREUR] ' + e.message, 'err');
  }
}

// Initialiser les calculs au chargement
if (typeof OrbitalCalculator !== 'undefined') {
    console.log('✓ OrbitalCalculator chargé, calculs orbitaux disponibles');
    // Mettre à jour les distances au chargement (calcul client-side en temps réel)
    mettreAJourDistancesPlanetes();
    console.log('✓ Positions des planètes recalculées côté client');
} else {
    console.error('✗ OrbitalCalculator non disponible');
}

async function ameliorerCalculSaut() {
  if (!confirm('Améliorer ce calcul coûte 1 PA. Continuer ?')) return;
  const r = await fetch('{{ route("navire.timonerie.ameliorer-calcul") }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
  });
  const data = await r.json();
  if (data.error) { consoleLog('[ERREUR] ' + data.error, 'err'); return; }
  consoleLog('  ✓ Calcul amélioré · Nouveau score : ' + data.nouveau_score + ' · Précision : ' + data.precision + '%', 'ok');
  location.reload();
}

// ============ RACCOURCIS ============
function handleShortcut(cmd) {
  switch (cmd) {
    case 'scan':
      consoleLog('> scan', 'cmd');
      consoleLog('  Scanner actif · {{ $scanCount }} objets détectés dans le secteur', 'data');
      break;
    case 'saut':
      if (!lockedTargetId || lockedTargetType !== 'jump') {
        consoleLog('> saut', 'cmd');
        consoleLog('  ⚠ Aucune destination de saut sélectionnée', 'warn');
      } else {
        calculerSaut(lockedTargetId);
      }
      break;
    case 'recharger':
      sendCommand('recharger 1');
      break;
    case 'position':
      consoleLog('> position', 'cmd');
      consoleLog('  Secteur : {{ $sectorLabel }}', 'data');
      consoleLog('  Position locale : ({{ $objetSpatial->position_x }}, {{ $objetSpatial->position_y }}, {{ $objetSpatial->position_z }}) AL', 'sys');
      break;
    case 'inv':
      window.location.href = '{{ route("navire.soute") }}';
      break;
    case 'help':
      consoleLog('> help', 'cmd');
      consoleLog('  Commandes : scan, saut, approcher, amarrer, position, recharger, help', 'sys');
      consoleLog('  → Cliquez sur la carte ou le scanner pour cibler', 'dim');
      break;
  }
}

async function sOrbiter(planeteId) {
    try {
        const response = await fetch('{{ route("navire.timonerie.s-orbiter") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ planete_id: planeteId })
        });
        const data = await response.json();

        if (data.error) {
            afficherResultat(data.error + (data.message ? '<br>' + data.message : ''), 'error');
        } else if (data.success) {
            const message = `<div class="space-y-2">
                <p class="text-green-400 font-bold text-xl">✓ ${data.message}</p>
                <p><strong>Planète:</strong> ${data.planete.nom}</p>
                <p><strong>Type:</strong> ${data.planete.type}</p>
                <p><strong>Distance:</strong> ${data.distance} UA</p>
                <p><strong>Ressources restantes:</strong></p>
                <ul class="ml-4">
                    <li>Énergie: ${data.energieRestante}</li>
                    <li>PA: ${data.paRestants}</li>
                </ul>
                <p class="mt-4 text-purple-400">Vous êtes maintenant en orbite autour de ${data.planete.nom} !</p>
                <p><a href="{{ route('navire.timonerie') }}" class="text-gray-400 underline text-sm">Recharger la timonerie</a></p>
            </div>`;

            afficherResultat(message, 'success');
        } else {
            afficherResultat(data.message || 'Mise en orbite effectuée', 'info');
        }
    } catch (error) {
        afficherResultat('Erreur: ' + error.message, 'error');
    }
}

function afficherResultat(message, type = 'info') {
    const resultatDiv = document.getElementById('resultat-navigation');
    const contenuDiv = document.getElementById('resultat-contenu');
    const styles = { info: 'border-cyan-500/30', success: 'border-green-500/30', error: 'border-red-500/30' };
    resultatDiv.className = `mt-6 bg-gray-800/50 border rounded-lg p-4 ${styles[type] || styles.info}`;
    contenuDiv.innerHTML = message;
    resultatDiv.style.display = 'block';
    resultatDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// ============================================================================
// SYSTÈME RADAR 360°
// ============================================================================

// Variables globales du radar
let radarZoom = 1; // Facteur de zoom actuel
const radarZoomLevels = [0.5, 1, 2, 5, 10, 20, 30];
let radarZoomIndex = 1; // Index dans radarZoomLevels (1 = x1 par défaut)
const radarMaxDistance = 20; // Distance max affichée en UA (vue de dessus)
const radarWidth = 1000; // Largeur du radar en pixels
const radarHeight = 500; // Hauteur du radar en pixels
const radarCenterX = 500; // Centre horizontal
const radarCenterY = 250; // Centre vertical

// Mode d'affichage actuel ('vue', 'dessus', 'distance')
let radarViewMode = 'vue';

// Azimut du vaisseau (mis à jour après rotation)
let azimutVaisseau = {{ $objetSpatial->azimut ?? 0 }};

// Données POI (combinées des deux listes)
const radarPOIs = [];

// Switcher entre les vues du radar
function switchRadarView(mode) {
    radarViewMode = mode;

    // Sauvegarder le mode de vue dans localStorage
    localStorage.setItem('radarViewMode', mode);

    // Masquer tous les SVG et légendes
    document.getElementById('radar-vue').classList.add('hidden');
    document.getElementById('radar-dessus').classList.add('hidden');
    document.getElementById('radar-distance').classList.add('hidden');
    document.getElementById('legend-vue').classList.add('hidden');
    document.getElementById('legend-dessus').classList.add('hidden');
    document.getElementById('legend-distance').classList.add('hidden');

    // Réinitialiser les boutons
    document.getElementById('tab-vue').className = 'px-4 py-2 bg-gray-700 text-gray-300 rounded transition hover:bg-gray-600';
    document.getElementById('tab-dessus').className = 'px-4 py-2 bg-gray-700 text-gray-300 rounded transition hover:bg-gray-600';
    document.getElementById('tab-distance').className = 'px-4 py-2 bg-gray-700 text-gray-300 rounded transition hover:bg-gray-600';

    // Activer la vue sélectionnée
    if (mode === 'vue') {
        document.getElementById('radar-vue').classList.remove('hidden');
        document.getElementById('legend-vue').classList.remove('hidden');
        document.getElementById('tab-vue').className = 'px-4 py-2 bg-purple-600 text-white rounded transition hover:bg-purple-500';
        drawRadarVuePerspective();
    } else if (mode === 'dessus') {
        document.getElementById('radar-dessus').classList.remove('hidden');
        document.getElementById('legend-dessus').classList.remove('hidden');
        document.getElementById('tab-dessus').className = 'px-4 py-2 bg-purple-600 text-white rounded transition hover:bg-purple-500';
        drawRadarVueDessus();
    } else if (mode === 'distance') {
        document.getElementById('radar-distance').classList.remove('hidden');
        document.getElementById('legend-distance').classList.remove('hidden');
        document.getElementById('tab-distance').className = 'px-4 py-2 bg-purple-600 text-white rounded transition hover:bg-purple-500';
        drawRadarVueDistance();
    }
}

// ============================================================================
// VUE DESSUS - Projection 2D (X,Y)
// ============================================================================

function drawRadarVueDessus() {
    const contentGroup = document.getElementById('radar-dessus-content');
    contentGroup.innerHTML = '';

    // Distance max visible (adaptée au zoom)
    const maxDist = radarMaxDistance / radarZoom;

    // Dessiner les cercles concentriques de distance (échelle max 20 UA)
    const circles = [0.1, 0.5, 1, 5, 10, 20];
    circles.forEach(dist => {
        if (dist <= maxDist) {
            const radius = (dist / maxDist) * (radarHeight / 2 - 20);

            const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            circle.setAttribute('cx', radarCenterX);
            circle.setAttribute('cy', radarCenterY);
            circle.setAttribute('r', radius);
            circle.setAttribute('fill', 'none');
            circle.setAttribute('stroke', dist === 1 ? '#00FF00' : '#444');
            circle.setAttribute('stroke-width', dist === 1 ? '2' : '1');
            circle.setAttribute('stroke-dasharray', '5,5');
            contentGroup.appendChild(circle);

            // Label de distance
            const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            label.setAttribute('x', radarCenterX);
            label.setAttribute('y', radarCenterY - radius - 5);
            label.setAttribute('fill', dist === 1 ? '#00FF00' : '#888');
            label.setAttribute('font-size', '10');
            label.setAttribute('text-anchor', 'middle');
            label.textContent = dist + ' UA';
            contentGroup.appendChild(label);
        }
    });

    // Axes de référence
    // Axe vertical (direction 0° = devant)
    const axeAvant = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    axeAvant.setAttribute('x1', radarCenterX);
    axeAvant.setAttribute('y1', radarCenterY);
    axeAvant.setAttribute('x2', radarCenterX);
    axeAvant.setAttribute('y2', '20');
    axeAvant.setAttribute('stroke', '#00FFFF');
    axeAvant.setAttribute('stroke-width', '2');
    axeAvant.setAttribute('stroke-dasharray', '10,5');
    contentGroup.appendChild(axeAvant);

    // Graduations d'angle (tous les 45°) - Afficher azimut absolu
    for (let angleRelatif = 0; angleRelatif < 360; angleRelatif += 45) {
        const rad = (angleRelatif - 90) * Math.PI / 180; // -90 pour que 0° soit en haut
        const x1 = radarCenterX + Math.cos(rad) * (radarHeight / 2 - 40);
        const y1 = radarCenterY + Math.sin(rad) * (radarHeight / 2 - 40);
        const x2 = radarCenterX + Math.cos(rad) * (radarHeight / 2 - 20);
        const y2 = radarCenterY + Math.sin(rad) * (radarHeight / 2 - 20);

        const tick = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        tick.setAttribute('x1', x1);
        tick.setAttribute('y1', y1);
        tick.setAttribute('x2', x2);
        tick.setAttribute('y2', y2);
        tick.setAttribute('stroke', '#666');
        tick.setAttribute('stroke-width', '2');
        contentGroup.appendChild(tick);

        // Calculer l'azimut absolu (dans le référentiel galaxie)
        const angleAbsolu = Math.round((angleRelatif + azimutVaisseau) % 360);

        // Label d'angle (azimut absolu)
        const labelAngle = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        labelAngle.setAttribute('x', radarCenterX + Math.cos(rad) * (radarHeight / 2 - 10));
        labelAngle.setAttribute('y', radarCenterY + Math.sin(rad) * (radarHeight / 2 - 10));
        labelAngle.setAttribute('fill', angleRelatif === 0 ? '#00FF00' : '#888');
        labelAngle.setAttribute('font-size', '10');
        labelAngle.setAttribute('font-weight', angleRelatif === 0 ? 'bold' : 'normal');
        labelAngle.setAttribute('text-anchor', 'middle');
        labelAngle.textContent = angleAbsolu + '°';
        contentGroup.appendChild(labelAngle);
    }

    // Dessiner les POI
    radarPOIs.forEach(poi => {
        // CORRECTION: Systèmes stellaires (sauts) toujours affichés sur les bords
        // POI locaux filtrés par distance
        if (!poi.saut && poi.distance > maxDist) return; // Filtrer seulement les POI locaux hors portée

        let x, y, size;

        // Normaliser azimut de 0-360° vers -180° à +180°
        let azimutNorm = poi.azimut_relatif;
        if (azimutNorm > 180) azimutNorm -= 360;

        if (poi.saut) {
            // SYSTÈMES STELLAIRES: Toujours sur le bord extérieur (périphérie)
            const azimutRad = (poi.azimut_relatif - 90) * Math.PI / 180; // -90 pour que 0° soit en haut
            const radiusBord = radarHeight / 2 - 30; // Rayon du bord (proche du cercle extérieur)
            x = radarCenterX + Math.cos(azimutRad) * radiusBord;
            y = radarCenterY + Math.sin(azimutRad) * radiusBord;

            // Taille selon distance (proche = plus gros)
            const tailleBase = 10;
            const facteurDistance = 50 / (poi.distance + 5); // Plus c'est proche, plus c'est gros
            size = Math.max(6, Math.min(16, tailleBase * facteurDistance));
        } else {
            // POI LOCAUX: Position selon distance réelle
            const azimutRad = (poi.azimut_relatif - 90) * Math.PI / 180; // -90 pour que 0° soit en haut
            const distPixels = (poi.distance / maxDist) * (radarHeight / 2 - 20);
            x = radarCenterX + Math.cos(azimutRad) * distPixels;
            y = radarCenterY + Math.sin(azimutRad) * distPixels;

            // Taille du point selon la distance (proche = gros)
            const baseSize = 8;
            const distanceFactor = maxDist / (poi.distance + 1);
            size = Math.max(4, Math.min(20, baseSize * distanceFactor));
        }

        // Couleur de bordure selon distance
        let strokeColor = '#FFF';
        let strokeWidth = 2;
        if (poi.saut) {
            strokeColor = '#FFD700'; // Or pour systèmes
            strokeWidth = 2;
        } else if (poi.distance < 1) {
            strokeColor = '#00FF00'; // < 1 UA
            strokeWidth = 3;
        } else if (poi.distance < 5) {
            strokeColor = '#FFFF00'; // < 5 UA
        } else if (poi.distance < 20) {
            strokeColor = '#FFA500'; // < 20 UA
        } else {
            strokeColor = '#FF4444'; // > 20 UA
        }

        // Créer le point POI
        const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        circle.setAttribute('cx', x);
        circle.setAttribute('cy', y);
        circle.setAttribute('r', size);
        circle.setAttribute('fill', poi.couleur);
        circle.setAttribute('stroke', strokeColor);
        circle.setAttribute('stroke-width', strokeWidth);
        circle.setAttribute('class', 'radar-poi-point cursor-pointer');
        circle.setAttribute('data-poi-id', poi.id);

        // Tooltip
        const title = document.createElementNS('http://www.w3.org/2000/svg', 'title');
        const action = poi.saut ? 'Saut' : 'Approcher';
        const distStr = poi.saut ? poi.distance.toFixed(1) + ' AL' : poi.distance.toFixed(2) + ' UA';
        title.textContent = `${poi.icone} ${poi.nom}\nDistance: ${distStr}\nAzimut: ${azimutNorm > 0 ? '+' : ''}${azimutNorm.toFixed(1)}°\nAction: ${action}\nCoût: ${poi.paRequis} PA, ${poi.energieRequise} énergie`;
        circle.appendChild(title);

        // Label du nom (si assez proche)
        if (size > 6) {
            const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            label.setAttribute('x', x);
            label.setAttribute('y', y - size - 3);
            label.setAttribute('fill', '#FFF');
            label.setAttribute('font-size', '9');
            label.setAttribute('text-anchor', 'middle');
            label.setAttribute('class', 'pointer-events-none');
            label.textContent = poi.nom;
            contentGroup.appendChild(label);
        }

        // Événements
        circle.addEventListener('mouseenter', () => highlightPOI(poi.id, true));
        circle.addEventListener('mouseleave', () => highlightPOI(poi.id, false));
        circle.addEventListener('dblclick', () => actionnerPOI(poi));
        circle.addEventListener('click', () => orienterVersPOI(poi));

        contentGroup.appendChild(circle);
    });

    // Indicateur du vaisseau (au centre)
    const vaisseau = document.createElementNS('http://www.w3.org/2000/svg', 'g');

    // Corps du vaisseau (triangle pointant vers le haut)
    const triangle = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
    triangle.setAttribute('points', `${radarCenterX},${radarCenterY - 15} ${radarCenterX - 10},${radarCenterY + 10} ${radarCenterX + 10},${radarCenterY + 10}`);
    triangle.setAttribute('fill', '#00FF00');
    triangle.setAttribute('stroke', '#FFF');
    triangle.setAttribute('stroke-width', '2');
    vaisseau.appendChild(triangle);

    // Label
    const vaisseauLabel = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    vaisseauLabel.setAttribute('x', radarCenterX);
    vaisseauLabel.setAttribute('y', radarCenterY + 25);
    vaisseauLabel.setAttribute('fill', '#00FF00');
    vaisseauLabel.setAttribute('font-size', '10');
    vaisseauLabel.setAttribute('text-anchor', 'middle');
    vaisseauLabel.textContent = 'VOUS';
    vaisseau.appendChild(vaisseauLabel);

    contentGroup.appendChild(vaisseau);
}

// ============================================================================
// VUE PERSPECTIVE - Répartition 80% système stellaire / 20% angle uniquement
// ============================================================================

function drawRadarVuePerspective() {
    const contentGroup = document.getElementById('radar-vue-content');
    contentGroup.innerHTML = '';

    // Répartition 80/20 (ATTENTION: dans SVG, Y augmente vers le BAS)
    const separationY = radarHeight * 0.2; // 100px = limite entre zone angle et zone système
    const systemZoneTop = separationY; // 100px
    const systemZoneBottom = radarHeight; // 500px
    const systemZoneHeight = systemZoneBottom - systemZoneTop; // 400px pour la zone système

    // Zone angle (en haut) : 0-100px - affichage par angle uniquement
    const angleZoneTop = 0;
    const angleZoneBottom = separationY; // 100px
    const angleZoneHeight = angleZoneBottom - angleZoneTop; // 100px

    // Point de fuite presque au contact de la séparation (juste en dessous)
    const vanishingPointY = systemZoneTop + 15; // 100 + 15 = 115px
    const horizonY = radarHeight - 30; // Horizon à 470px (niveau du vaisseau en BAS)

    // ========== ZONE ANGLE (20% en haut) ==========
    // Fond de la zone angle
    const angleZoneBackground = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
    angleZoneBackground.setAttribute('x', '0');
    angleZoneBackground.setAttribute('y', angleZoneTop);
    angleZoneBackground.setAttribute('width', radarWidth);
    angleZoneBackground.setAttribute('height', angleZoneHeight);
    angleZoneBackground.setAttribute('fill', '#0a0a1a');
    angleZoneBackground.setAttribute('opacity', '0.5');
    contentGroup.appendChild(angleZoneBackground);

    // Ligne de séparation entre zone angle et zone système
    const separationLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    separationLine.setAttribute('x1', '0');
    separationLine.setAttribute('y1', separationY);
    separationLine.setAttribute('x2', radarWidth);
    separationLine.setAttribute('y2', separationY);
    separationLine.setAttribute('stroke', '#FF00FF');
    separationLine.setAttribute('stroke-width', '2');
    separationLine.setAttribute('stroke-dasharray', '10,5');
    contentGroup.appendChild(separationLine);

    // Label zone angle
    const angleZoneLabel = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    angleZoneLabel.setAttribute('x', '10');
    angleZoneLabel.setAttribute('y', '20');
    angleZoneLabel.setAttribute('fill', '#FF00FF');
    angleZoneLabel.setAttribute('font-size', '11');
    angleZoneLabel.setAttribute('font-weight', 'bold');
    angleZoneLabel.textContent = 'ZONE ANGLE (détection sans distance)';
    contentGroup.appendChild(angleZoneLabel);

    // Graduations d'angle dans la zone angle (tous les 30°) - Afficher azimut absolu
    for (let angleRelatif = -180; angleRelatif <= 180; angleRelatif += 30) {
        const x = radarCenterX + (angleRelatif / 180) * (radarCenterX * 0.9);

        const tick = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        tick.setAttribute('x1', x);
        tick.setAttribute('y1', angleZoneTop + 25);
        tick.setAttribute('x2', x);
        tick.setAttribute('y2', angleZoneBottom - 5);
        tick.setAttribute('stroke', angleRelatif === 0 ? '#FF00FF' : '#444');
        tick.setAttribute('stroke-width', angleRelatif === 0 ? '2' : '1');
        tick.setAttribute('stroke-dasharray', '5,5');
        contentGroup.appendChild(tick);

        // Calculer l'azimut absolu
        const angleAbsolu = Math.round((angleRelatif + azimutVaisseau + 360) % 360);

        // Label angle (azimut absolu)
        const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        label.setAttribute('x', x);
        label.setAttribute('y', angleZoneTop + 40);
        label.setAttribute('fill', angleRelatif === 0 ? '#00FF00' : '#888');
        label.setAttribute('font-size', '9');
        label.setAttribute('font-weight', angleRelatif === 0 ? 'bold' : 'normal');
        label.setAttribute('text-anchor', 'middle');
        label.textContent = angleAbsolu + '°';
        contentGroup.appendChild(label);
    }

    // ========== ZONE SYSTÈME STELLAIRE (80% en bas) ==========
    // Label zone système
    const systemZoneLabel = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    systemZoneLabel.setAttribute('x', '10');
    systemZoneLabel.setAttribute('y', systemZoneTop + 20);
    systemZoneLabel.setAttribute('fill', '#FFD700');
    systemZoneLabel.setAttribute('font-size', '10');
    systemZoneLabel.textContent = 'SYSTÈME STELLAIRE (avec distance)';
    contentGroup.appendChild(systemZoneLabel);

    // Horizon dans la zone système
    const horizon = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    horizon.setAttribute('x1', '0');
    horizon.setAttribute('y1', horizonY);
    horizon.setAttribute('x2', radarWidth);
    horizon.setAttribute('y2', horizonY);
    horizon.setAttribute('stroke', '#00FFFF');
    horizon.setAttribute('stroke-width', '2');
    horizon.setAttribute('stroke-dasharray', '10,5');
    contentGroup.appendChild(horizon);

    // Label horizon
    const horizonLabel = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    horizonLabel.setAttribute('x', radarWidth - 80);
    horizonLabel.setAttribute('y', horizonY - 5);
    horizonLabel.setAttribute('fill', '#00FFFF');
    horizonLabel.setAttribute('font-size', '11');
    horizonLabel.setAttribute('font-weight', 'bold');
    horizonLabel.textContent = 'HORIZON';
    contentGroup.appendChild(horizonLabel);

    // Point de fuite (petit cercle)
    const vanishingPoint = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
    vanishingPoint.setAttribute('cx', radarCenterX);
    vanishingPoint.setAttribute('cy', vanishingPointY);
    vanishingPoint.setAttribute('r', '5');
    vanishingPoint.setAttribute('fill', '#FFFFFF');
    vanishingPoint.setAttribute('stroke', '#FF00FF');
    vanishingPoint.setAttribute('stroke-width', '2');
    contentGroup.appendChild(vanishingPoint);

    // Lignes de perspective dans la zone système (convergent vers le point de fuite)
    const perspectiveAngles = [-180, -135, -90, -45, 0, 45, 90, 135, 180];
    perspectiveAngles.forEach(angle => {
        const xBottom = radarCenterX + (angle / 180) * radarCenterX;

        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.setAttribute('x1', xBottom);
        line.setAttribute('y1', horizonY);
        line.setAttribute('x2', radarCenterX);
        line.setAttribute('y2', vanishingPointY);
        line.setAttribute('stroke', angle === 0 ? '#00FFFF' : '#333');
        line.setAttribute('stroke-width', angle === 0 ? '2' : '1');
        contentGroup.appendChild(line);
    });

    // Graduations d'angle dans la zone système (au niveau de l'horizon) - Afficher azimut absolu
    for (let angleRelatif = -180; angleRelatif <= 180; angleRelatif += 30) {
        const x = radarCenterX + (angleRelatif / 180) * radarCenterX;

        const tick = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        tick.setAttribute('x1', x);
        tick.setAttribute('y1', horizonY);
        tick.setAttribute('x2', x);
        tick.setAttribute('y2', horizonY + 10);
        tick.setAttribute('stroke', angleRelatif === 0 ? '#00FFFF' : '#666');
        tick.setAttribute('stroke-width', '2');
        contentGroup.appendChild(tick);

        // Calculer l'azimut absolu
        const angleAbsolu = Math.round((angleRelatif + azimutVaisseau + 360) % 360);

        // Label angle (azimut absolu)
        const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        label.setAttribute('x', x);
        label.setAttribute('y', horizonY + 25);
        label.setAttribute('fill', angleRelatif === 0 ? '#00FF00' : '#888');
        label.setAttribute('font-size', '10');
        label.setAttribute('font-weight', angleRelatif === 0 ? 'bold' : 'normal');
        label.setAttribute('text-anchor', 'middle');
        label.textContent = angleAbsolu + '°';
        contentGroup.appendChild(label);
    }

    // Lignes de distance dans la zone système (0.1, 0.5, 1, 5, 10, 15, 20 UA)
    const systemDistances = [0.1, 0.5, 1.0, 5.0, 10.0, 15.0, 20.0];
    const maxSystemDist = 20.0; // Distance max affichée

    systemDistances.forEach(dist => {
        // Position Y dans la zone système (depuis l'horizon en BAS vers le point de fuite en HAUT)
        const normalizedDist = Math.min(dist / maxSystemDist, 1); // 0 à 1
        // Plus la distance est grande, plus on monte vers le point de fuite
        const y = horizonY - (normalizedDist * (horizonY - vanishingPointY - 20));

        // Ligne horizontale pour marquer la distance
        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.setAttribute('x1', '0');
        line.setAttribute('y1', y);
        line.setAttribute('x2', radarWidth);
        line.setAttribute('y2', y);
        line.setAttribute('stroke', dist === 1.0 ? '#00FF00' : '#444');
        line.setAttribute('stroke-width', dist === 1.0 ? '2' : '1');
        line.setAttribute('stroke-dasharray', '5,5');
        contentGroup.appendChild(line);

        // Label distance
        const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        label.setAttribute('x', '10');
        label.setAttribute('y', y - 3);
        label.setAttribute('fill', dist === 1.0 ? '#00FF00' : '#888');
        label.setAttribute('font-size', '9');
        label.textContent = dist + ' UA';
        contentGroup.appendChild(label);
    });

    // Dessiner les POI
    radarPOIs.forEach(poi => {
        let x, y, size;

        // Normaliser azimut de 0-360° vers -180° à +180°
        let azimutNorm = poi.azimut_relatif;
        if (azimutNorm > 180) azimutNorm -= 360;

        if (poi.saut) {
            // ========== SYSTÈMES STELLAIRES : Dans la ZONE ANGLE (20% en haut) ==========
            // Position X selon l'azimut uniquement (sans perspective)
            x = radarCenterX + (azimutNorm / 180) * (radarCenterX * 0.9);

            // Position Y centrée dans la zone angle (sans tenir compte de la distance)
            y = angleZoneTop + (angleZoneHeight / 2) + 20; // Centré à ~70px

            // Taille = detectabilite * distance / 40 (divisé par 4 comme demandé)
            // Detectabilité par défaut pour les systèmes : 100
            const detectabilite = poi.detectabilite || 100;
            const taileBrute = (detectabilite * poi.distance) / 40;

            // DEBUG: Afficher les tailles calculées
            console.log(`Système ${poi.nom}: detectabilite=${detectabilite}, distance=${poi.distance.toFixed(2)} AL, taille brute=${taileBrute.toFixed(2)}px`);

            size = Math.max(8, taileBrute);
            // Limiter la taille max pour ne pas déborder
            size = Math.min(size, 30);
        } else {
            // ========== POI LOCAUX : Dans la ZONE SYSTÈME (80% en bas) ==========
            const distUA = poi.distance;

            // Position X selon l'azimut
            const xBase = radarCenterX + (azimutNorm / 180) * radarCenterX;

            if (distUA > maxSystemDist) {
                // Au-delà de la distance max, placer près du point de fuite (en haut de la zone)
                y = vanishingPointY + 20;
                size = 4;
                x = xBase;
            } else {
                // Position Y selon distance dans la zone système
                // Distance 0 = horizon (en bas, y=470)
                // Distance max = près du point de fuite (en haut, y=180)
                const normalizedDist = distUA / maxSystemDist; // 0 à 1
                y = horizonY - (normalizedDist * (horizonY - vanishingPointY - 20));

                // Taille inversement proportionnelle à la distance
                const distanceFactor = 1 / (distUA + 0.1);
                size = Math.max(4, Math.min(16, 8 * distanceFactor));

                // Position X avec perspective (convergence vers centre en s'éloignant)
                const perspectiveRatio = (horizonY - y) / (horizonY - vanishingPointY - 20);
                const convergence = 1 - (perspectiveRatio * 0.7); // Converge de 70% max vers le point de fuite
                x = radarCenterX + ((xBase - radarCenterX) * convergence);
            }
        }

        // Couleur de bordure selon distance
        let strokeColor = '#FFF';
        let strokeWidth = 2;
        if (poi.saut) {
            strokeColor = '#FFD700'; // Or pour systèmes
            strokeWidth = 2;
        } else if (poi.distance < 1) {
            strokeColor = '#00FF00'; // < 1 UA
            strokeWidth = 3;
        } else if (poi.distance < 5) {
            strokeColor = '#FFFF00'; // < 5 UA
        } else if (poi.distance < 20) {
            strokeColor = '#FFA500'; // < 20 UA
        } else {
            strokeColor = '#FF4444'; // > 20 UA
        }

        // Créer le point POI
        const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        circle.setAttribute('cx', x);
        circle.setAttribute('cy', y);
        circle.setAttribute('r', size);
        circle.setAttribute('fill', poi.couleur);
        circle.setAttribute('stroke', strokeColor);
        circle.setAttribute('stroke-width', strokeWidth);
        circle.setAttribute('class', 'radar-poi-point cursor-pointer');
        circle.setAttribute('data-poi-id', poi.id);

        // Tooltip
        const title = document.createElementNS('http://www.w3.org/2000/svg', 'title');
        const action = poi.saut ? 'Saut' : 'Approcher';
        const distStr = poi.saut ? poi.distance.toFixed(1) + ' AL' : poi.distance.toFixed(2) + ' UA';
        title.textContent = `${poi.icone} ${poi.nom}\nDistance: ${distStr}\nAzimut: ${azimutNorm > 0 ? '+' : ''}${azimutNorm.toFixed(1)}°\nAction: ${action}\nCoût: ${poi.paRequis} PA, ${poi.energieRequise} énergie`;
        circle.appendChild(title);

        // Label du nom (si assez gros)
        if (size > 5) {
            const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            label.setAttribute('x', x);
            label.setAttribute('y', y - size - 3);
            label.setAttribute('fill', '#FFF');
            label.setAttribute('font-size', '9');
            label.setAttribute('text-anchor', 'middle');
            label.setAttribute('class', 'pointer-events-none');
            label.textContent = poi.nom;
            contentGroup.appendChild(label);
        }

        // Événements
        circle.addEventListener('mouseenter', () => highlightPOI(poi.id, true));
        circle.addEventListener('mouseleave', () => highlightPOI(poi.id, false));
        circle.addEventListener('dblclick', () => actionnerPOI(poi));
        circle.addEventListener('click', () => orienterVersPOI(poi));

        contentGroup.appendChild(circle);
    });

    // Indicateur du vaisseau (au centre de l'horizon)
    const vaisseau = document.createElementNS('http://www.w3.org/2000/svg', 'g');

    // Corps du vaisseau (triangle pointant vers le haut)
    const triangle = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
    triangle.setAttribute('points', `${radarCenterX},${horizonY - 20} ${radarCenterX - 12},${horizonY + 5} ${radarCenterX + 12},${horizonY + 5}`);
    triangle.setAttribute('fill', '#00FF00');
    triangle.setAttribute('stroke', '#FFF');
    triangle.setAttribute('stroke-width', '2');
    vaisseau.appendChild(triangle);

    // Label
    const vaisseauLabel = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    vaisseauLabel.setAttribute('x', radarCenterX);
    vaisseauLabel.setAttribute('y', horizonY + 20);
    vaisseauLabel.setAttribute('fill', '#00FF00');
    vaisseauLabel.setAttribute('font-size', '10');
    vaisseauLabel.setAttribute('font-weight', 'bold');
    vaisseauLabel.setAttribute('text-anchor', 'middle');
    vaisseauLabel.textContent = 'VOUS';
    vaisseau.appendChild(vaisseauLabel);

    contentGroup.appendChild(vaisseau);
}

// ============================================================================
// VUE DISTANCE - Distance vs Angle (stub pour l'instant)
// ============================================================================

function drawRadarVueDistance() {
    const contentGroup = document.getElementById('radar-distance-content');
    contentGroup.innerHTML = '';

    // TODO: Implémenter la vue Distance
    // X = 70% zone locale (0-20 UA), 30% systèmes (échelle log)
    // Y = Angle (-180° à +180°)

    const placeholder = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    placeholder.setAttribute('x', radarCenterX);
    placeholder.setAttribute('y', radarCenterY);
    placeholder.setAttribute('fill', '#888');
    placeholder.setAttribute('font-size', '20');
    placeholder.setAttribute('text-anchor', 'middle');
    placeholder.textContent = 'Vue Distance - En développement';
    contentGroup.appendChild(placeholder);
}

// Initialiser le radar au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Collecter tous les POI depuis les deux listes
    collecterPOIs();

    // Charger le zoom depuis localStorage
    const savedZoomIndex = localStorage.getItem('radarZoomIndex');
    if (savedZoomIndex !== null) {
        radarZoomIndex = parseInt(savedZoomIndex);
        radarZoom = radarZoomLevels[radarZoomIndex];
        document.getElementById('radar-zoom-label').textContent = 'x' + radarZoom;
    }

    // Charger le mode de vue depuis localStorage (par défaut 'vue')
    const savedViewMode = localStorage.getItem('radarViewMode') || 'vue';
    switchRadarView(savedViewMode);
});

// Collecter les POI depuis les listes sauts et déplacements
function collecterPOIs() {
    radarPOIs.length = 0; // Vider le tableau

    // POI de déplacement (secteur local)
    @foreach($poisSecteur as $poi)
        radarPOIs.push({
            id: {{ $poi->id }},
            nom: @json($poi->nom),
            type: @json($poi->type_poi),
            icone: @json($poi->icone),
            couleur: @json($poi->couleur ?? '#FFFFFF'),
            distance: {{ $poi->distance }},
            azimut_absolu: {{ $poi->azimut_absolu ?? 0 }},
            azimut_relatif: {{ $poi->azimut_relatif ?? 0 }},
            elevation: {{ $poi->elevation ?? 0 }},
            energieRequise: {{ $poi->energieRequise }},
            paRequis: {{ $poi->paRequis }},
        });
    @endforeach

    // POI de saut (autres secteurs) - affichés en périphérie
    @foreach($sautsDisponibles as $systeme)
        radarPOIs.push({
            id: 'saut-' + {{ $systeme->id }},
            nom: @json($systeme->nom),
            type: 'systeme',
            icone: '⭐',
            couleur: '#FDB813',
            distance: {{ $systeme->distance }},
            azimut_absolu: {{ $systeme->azimut_absolu ?? 0 }},
            azimut_relatif: {{ $systeme->azimut_relatif ?? 0 }},
            elevation: {{ $systeme->elevation ?? 0 }},
            energieRequise: {{ $systeme->energieRequise }},
            paRequis: {{ $systeme->paRequis }},
            saut: true, // Indique que c'est un saut hyperespace
            detectabilite: {{ $systeme->detectabilite ?? 100 }}, // Détectabilité par défaut = 100 pour systèmes
        });
    @endforeach

    // DEBUG: Afficher les 3 premiers POI
    console.log('=== POI COLLECTÉS ===');
    console.log('Total POI:', radarPOIs.length);
    console.log('Azimut vaisseau actuel:', {{ $objetSpatial->azimut ?? 0 }});
    radarPOIs.slice(0, 3).forEach(poi => {
        console.log(`POI "${poi.nom}":`, {
            azimut_absolu: poi.azimut_absolu,
            azimut_relatif: poi.azimut_relatif,
            distance: poi.distance,
            saut: poi.saut || false
        });
    });
}

// Surligner un POI (sur le radar et dans les listes)
function highlightPOI(poiId, highlight) {
    // Surligner le point sur le radar
    const circle = document.querySelector(`[data-poi-id="${poiId}"]`);
    if (circle) {
        circle.setAttribute('stroke-width', highlight ? '3' : '1');
        circle.setAttribute('stroke', highlight ? '#FFFF00' : '#FFF');
    }

    // Surligner le cartouche dans les listes
    const cartouche = document.querySelector(`[data-cartouche-poi-id="${poiId}"]`);
    if (cartouche) {
        if (highlight) {
            cartouche.classList.add('ring', 'ring-2', 'ring-yellow-400');
        } else {
            cartouche.classList.remove('ring', 'ring-2', 'ring-yellow-400');
        }
    }
}

// Orienter le vaisseau vers un POI (clic sur le radar)
function orienterVersPOI(poi) {
    // Calculer la rotation nécessaire pour mettre le POI en face (azimut relatif 0°)
    const azimutRelatif = poi.azimut_relatif;

    // Afficher un message
    afficherResultat(`Orientation vers ${poi.nom} (${azimutRelatif.toFixed(1)}°)...`, 'info');

    // TODO: Envoyer une requête pour tourner le vaisseau de l'angle nécessaire
    // Pour l'instant, juste un message
    console.log(`Orienter vers ${poi.nom}: rotation de ${azimutRelatif}°`);
}

// Actionner un POI (double-clic sur le radar)
async function actionnerPOI(poi) {
    if (poi.saut) {
        // Lancer le saut hyperespace
        const systemeId = poi.id.replace('saut-', '');
        await effectuerSaut(systemeId);
    } else {
        // Lancer l'approche
        await sApprocher(poi.id);
    }
}

// Mettre à jour les affichages d'azimut dans les cartouches
function mettreAJourCartouchesAzimut(nouvelAzimutVaisseau) {
    // Parcourir tous les cartouches POI
    document.querySelectorAll('[data-poi-azimut-absolu]').forEach(cartouche => {
        const azimutAbsolu = parseFloat(cartouche.dataset.poiAzimutAbsolu);

        // Calculer le nouvel azimut relatif
        let azimutRelatif = (azimutAbsolu - nouvelAzimutVaisseau + 360) % 360;

        // Normaliser en -180 à +180
        if (azimutRelatif > 180) azimutRelatif -= 360;

        // Mettre à jour l'affichage
        const azimutDisplay = cartouche.querySelector('.poi-azimut-display');
        if (azimutDisplay) {
            azimutDisplay.textContent = `Az: ${azimutRelatif.toFixed(1)}°`;
        }
    });
}

// Recalculer les azimuts relatifs de tous les POI après rotation
function recalculerAzimutsRelatifs(nouvelAzimutVaisseau) {
    console.log('=== RECALCUL AZIMUTS ===');
    console.log('Nouvel azimut vaisseau:', nouvelAzimutVaisseau);
    console.log('Nombre de POI:', radarPOIs.length);

    let debugCount = 0;
    radarPOIs.forEach(poi => {
        // L'azimut absolu ne change pas, seul l'azimut relatif change
        // azimutRelatif = azimutAbsolu - azimutVaisseau
        const azimutAbsoluOriginal = poi.azimut_absolu;
        const azimutRelatifOriginal = poi.azimut_relatif;

        if (!azimutAbsoluOriginal && azimutAbsoluOriginal !== 0) {
            console.warn(`POI ${poi.nom} n'a pas d'azimut absolu!`, poi);
            return;
        }

        let nouvelAzimutRelatif = (azimutAbsoluOriginal - nouvelAzimutVaisseau + 360) % 360;

        // Normaliser en -180 à +180 si nécessaire
        if (nouvelAzimutRelatif > 180) nouvelAzimutRelatif -= 360;

        poi.azimut_relatif = nouvelAzimutRelatif;

        // Debug pour les 3 premiers POI
        if (debugCount < 3) {
            console.log(`POI "${poi.nom}":`, {
                azimutAbsolu: azimutAbsoluOriginal,
                azimutRelatifAvant: azimutRelatifOriginal.toFixed(1),
                azimutRelatifApres: nouvelAzimutRelatif.toFixed(1),
                delta: (nouvelAzimutRelatif - azimutRelatifOriginal).toFixed(1)
            });
            debugCount++;
        }
    });

    // Mettre à jour les cartouches POI
    mettreAJourCartouchesAzimut(nouvelAzimutVaisseau);

    // Redessiner la vue active avec les nouveaux azimuts
    console.log('Azimuts relatifs recalculés, redessin du radar...');
    if (radarViewMode === 'vue') {
        drawRadarVuePerspective();
    } else if (radarViewMode === 'dessus') {
        drawRadarVueDessus();
    } else if (radarViewMode === 'distance') {
        drawRadarVueDistance();
    }
}

// Tourner le vaisseau
async function tournerVaisseau(direction) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        if (!csrfToken) {
            afficherResultat('Erreur: Token CSRF manquant', 'error');
            console.error('Token CSRF non trouvé dans la page');
            return;
        }

        const response = await fetch('{{ route("navire.timonerie.tourner") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                direction: direction,
                angle: 15
            })
        });

        // Vérifier si la réponse est OK
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Erreur serveur:', response.status, errorText);
            afficherResultat(`Erreur serveur (${response.status}): ${errorText.substring(0, 100)}`, 'error');
            return;
        }

        const data = await response.json();
        console.log('Réponse rotation:', data);

        if (data.success) {
            // Mettre à jour l'azimut du vaisseau (variable globale)
            azimutVaisseau = data.nouvelAzimut;

            // Mettre à jour l'affichage de l'azimut dans le header
            document.getElementById('radar-azimut').textContent = data.nouvelAzimut.toFixed(1) + '°';

            // Recalculer les azimuts relatifs côté client (sans reload)
            recalculerAzimutsRelatifs(data.nouvelAzimut);

            // Afficher un message de succès temporaire
            afficherResultat(data.message + ' - Radar mis à jour', 'success');

            // Masquer le message après 2 secondes
            setTimeout(() => {
                document.getElementById('resultat-navigation').style.display = 'none';
            }, 2000);
        } else {
            afficherResultat(data.error || 'Erreur lors de la rotation', 'error');
        }
    } catch (error) {
        console.error('Erreur catch:', error);
        afficherResultat('Erreur lors de la rotation: ' + error.message, 'error');
    }
}

// Gérer le zoom du radar
function zoomRadar(action) {
    if (action === 'in' && radarZoomIndex < radarZoomLevels.length - 1) {
        radarZoomIndex++;
    } else if (action === 'out' && radarZoomIndex > 0) {
        radarZoomIndex--;
    } else if (action === 'reset') {
        radarZoomIndex = 1; // x1
    }

    radarZoom = radarZoomLevels[radarZoomIndex];
    document.getElementById('radar-zoom-label').textContent = 'x' + radarZoom;

    // Sauvegarder dans localStorage
    localStorage.setItem('radarZoomIndex', radarZoomIndex);

    // Redessiner la vue active
    if (radarViewMode === 'vue') {
        drawRadarVuePerspective();
    } else if (radarViewMode === 'dessus') {
        drawRadarVueDessus();
    } else if (radarViewMode === 'distance') {
        drawRadarVueDistance();
    }
}

// Ajouter événements survol aux cartouches POI dans les listes
document.addEventListener('DOMContentLoaded', function() {
    // Attendre un peu que les listes soient chargées
    setTimeout(() => {
        document.querySelectorAll('[data-cartouche-poi-id]').forEach(cartouche => {
            const poiId = cartouche.getAttribute('data-cartouche-poi-id');

            cartouche.addEventListener('mouseenter', () => highlightPOI(poiId, true));
            cartouche.addEventListener('mouseleave', () => highlightPOI(poiId, false));
        });
    }, 500);
});
</script>
@endsection
