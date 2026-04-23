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
    $angle = $i * (M_PI * 2 / max(count($poisSecteur), 6));
    $r = 1.8 + $i * 0.9;
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
  consoleLog('> amarrer ' + stationId, 'cmd');
  try {
    const r = await fetch('{{ route("navire.timonerie.s-amarrer") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ station_id: stationId })
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

async function annulerCalculSaut() {
  if (!confirm('Annuler ce calcul de saut ?')) return;
  const r = await fetch('{{ route("navire.timonerie.annuler-calcul") }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
  });
  const data = await r.json();
  if (data.success) { consoleLog('  Calcul annulé', 'sys'); location.reload(); }
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

// ============ HORLOGE ============
function updateClock() {
  const d = new Date();
  const t = d.toTimeString().slice(0, 8);
  const el = document.getElementById('local-clock');
  if (el) el.textContent = t + ' UTC';
}
setInterval(updateClock, 1000);
updateClock();
</script>
@endsection
