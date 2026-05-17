@extends('layouts.app')

@section('title', 'Timonerie')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
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
html, body { height: 100%; overflow: hidden; }
body {
  font-family: var(--sans);
  background: var(--bg-void);
  color: var(--text-primary);
  font-size: 13px;
  letter-spacing: 0.02em;
  min-width: 1200px;
}

/* HUD HEADER */
.hud-header {
  position: fixed; top: 0; left: 0; right: 0; height: 52px; min-width: 1200px;
  background: linear-gradient(180deg, rgba(5,7,12,0.98), rgba(5,7,12,0.75));
  border-bottom: 1px solid var(--border-subtle);
  display: flex; align-items: center; padding: 0 16px; gap: 22px;
  z-index: 100; backdrop-filter: blur(6px); white-space: nowrap;
}
.hud-brand { font-family: var(--mono); font-size: 11px; letter-spacing: 0.25em; color: var(--accent); font-weight: 700; }
.hud-sep { width: 1px; height: 26px; background: var(--border-subtle); flex-shrink: 0; }
.hud-block { display: flex; flex-direction: column; gap: 1px; }
.hud-label { font-family: var(--mono); font-size: 9px; letter-spacing: 0.2em; color: var(--text-muted); text-transform: uppercase; }
.hud-value { font-family: var(--mono); font-size: 12px; color: var(--text-primary); font-weight: 500; }
.hud-value.data { color: var(--data); }
.hud-value.accent { color: var(--accent); }
.gauge-group { display: flex; gap: 12px; }
.gauge { display: flex; align-items: center; gap: 6px; }
.gauge-icon { font-family: var(--mono); font-size: 10px; color: var(--text-muted); letter-spacing: 0.1em; }
.gauge-bar { width: 52px; height: 5px; background: rgba(125, 165, 200, 0.1); border: 1px solid var(--border-subtle); position: relative; }
.gauge-fill { height: 100%; transition: width 0.3s; }
.gauge-fill.ok { background: linear-gradient(90deg, var(--success), #86efac); box-shadow: 0 0 6px rgba(74,222,128,0.4); }
.gauge-fill.mid { background: linear-gradient(90deg, var(--warning), #fcd34d); box-shadow: 0 0 6px rgba(251,191,36,0.4); }
.gauge-fill.low { background: linear-gradient(90deg, var(--danger), #fca5a5); box-shadow: 0 0 6px rgba(239,68,68,0.5); }
.gauge-val { font-family: var(--mono); font-size: 10px; color: var(--text-secondary); min-width: 30px; }
.hud-spacer { flex: 1; }
.hud-btn { font-family: var(--mono); font-size: 10px; letter-spacing: 0.15em; color: var(--text-muted); text-transform: uppercase; background: none; border: 1px solid var(--border-subtle); padding: 4px 9px; cursor: pointer; transition: all 0.15s; text-decoration: none; display: inline-flex; align-items: center; }
.hud-btn:hover { color: var(--text-primary); border-color: var(--border-strong); }

/* CALCUL BANNER */
.calcul-banner {
  position: fixed; top: 52px; left: 200px; right: 320px;
  background: rgba(127, 212, 255, 0.08); border-bottom: 1px solid rgba(127,212,255,0.3);
  padding: 5px 14px; z-index: 60; font-family: var(--mono); font-size: 10px;
  display: flex; align-items: center; gap: 14px;
}
.calcul-banner .calc-label { color: var(--data); font-weight: 700; letter-spacing: 0.15em; }
.calcul-banner .calc-dest { color: var(--text-primary); }
.calcul-banner .calc-meta { color: var(--text-secondary); }
.calcul-banner .calc-actions { margin-left: auto; display: flex; gap: 6px; }
.calc-btn { font-family: var(--mono); font-size: 9px; letter-spacing: 0.1em; text-transform: uppercase; padding: 3px 8px; cursor: pointer; background: transparent; border: 1px solid; transition: all 0.15s; }
.calc-btn.cancel { color: var(--danger); border-color: var(--danger); }
.calc-btn.cancel:hover { background: rgba(239,68,68,0.1); }
.calc-btn.improve { color: var(--data); border-color: var(--data); }
.calc-btn.improve:hover { background: rgba(127,212,255,0.1); }
.calc-btn.jump { color: var(--ok); border-color: var(--ok); }
.calc-btn.jump:hover { background: rgba(74,222,128,0.15); }

/* STAGE */
.stage { position: fixed; top: 52px; left: 0; right: 0; bottom: 0; display: grid; grid-template-columns: 200px 1fr 6px var(--console-w, 320px); min-width: 1200px; }

/* LEFT NAV */
.nav {
  background: var(--bg-panel); border-right: 1px solid var(--border-subtle);
  display: flex; flex-direction: column; overflow: hidden; backdrop-filter: blur(6px);
}
.nav-scroll { flex: 1; overflow-y: auto; padding: 10px 0; }
.nav-section { margin-bottom: 12px; }
.nav-section-head { padding: 4px 14px; font-family: var(--mono); font-size: 9px; letter-spacing: 0.2em; color: var(--text-muted); text-transform: uppercase; display: flex; align-items: center; gap: 6px; }
.nav-section-head::before { content: ''; width: 4px; height: 4px; background: var(--text-muted); border-radius: 50%; flex-shrink: 0; }
.nav-item {
  display: flex; justify-content: space-between; align-items: center;
  padding: 5px 14px; color: var(--text-secondary); font-size: 12px; cursor: pointer;
  transition: all 0.12s; border-left: 2px solid transparent; font-weight: 500;
  text-decoration: none;
}
.nav-item:hover { color: var(--text-primary); background: rgba(127,212,255,0.04); }
.nav-item.active { color: var(--accent); background: rgba(255,138,61,0.06); border-left-color: var(--accent); }
.nav-item.disabled { opacity: 0.35; cursor: default; pointer-events: none; }
.nav-cmd { font-family: var(--mono); font-size: 9px; color: var(--text-muted); opacity: 0; transition: opacity 0.15s; }
.nav-item:hover .nav-cmd { opacity: 1; }

/* NAV modules */
.nav-module { margin: 4px 10px 12px; border: 1px solid var(--border-subtle); background: rgba(5,7,12,0.4); }
.nav-module-head { padding: 5px 10px; font-family: var(--mono); font-size: 9px; letter-spacing: 0.2em; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center; }
.nav-module-head .live { color: var(--success); display: flex; align-items: center; gap: 5px; }
.nav-module-head .live::before { content: ''; width: 4px; height: 4px; background: var(--success); border-radius: 50%; box-shadow: 0 0 4px var(--success); animation: pulse 2s infinite; }
.nav-module-body { padding: 7px 10px; }
.inst-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 5px 8px; }
.inst { display: flex; flex-direction: column; gap: 1px; }
.inst-label { font-family: var(--mono); font-size: 8px; letter-spacing: 0.15em; color: var(--text-muted); text-transform: uppercase; }
.inst-val { font-family: var(--mono); font-size: 11px; color: var(--text-primary); font-weight: 500; }
.inst-val.data { color: var(--data); }

/* Scanner dans nav */
.scan-mode { display: flex; gap: 0; margin-bottom: 5px; }
.scan-mode button { flex: 1; font-family: var(--mono); font-size: 9px; letter-spacing: 0.08em; padding: 3px 4px; background: transparent; color: var(--text-muted); border: 1px solid var(--border-subtle); cursor: pointer; transition: all 0.12s; text-transform: uppercase; }
.scan-mode button.active { color: var(--data); border-color: var(--data); background: rgba(127,212,255,0.06); }
.scan-mode button + button { border-left: none; }
.scan-list { display: flex; flex-direction: column; gap: 1px; max-height: 220px; overflow-y: auto; }
.scan-item { display: flex; align-items: center; gap: 6px; padding: 3px 5px; cursor: pointer; transition: all 0.12s; border-left: 2px solid transparent; font-family: var(--mono); font-size: 10px; }
.scan-item:hover { background: rgba(127,212,255,0.04); border-left-color: var(--data); }
.scan-item.active { background: rgba(127,212,255,0.08); border-left-color: var(--data); }
.scan-glyph { width: 10px; text-align: center; font-size: 9px; color: var(--text-muted); flex-shrink: 0; }
.scan-glyph.star { color: var(--warning); }
.scan-glyph.station { color: var(--data); }
.scan-glyph.planet { color: var(--success); }
.scan-glyph.moon { color: var(--text-secondary); }
.scan-name { flex: 1; color: var(--text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.scan-dist { color: var(--text-muted); font-size: 9px; white-space: nowrap; }

/* STARMAP */
.starmap-wrap { position: relative; background: radial-gradient(ellipse at center, #0a0f1a 0%, #050709 70%); overflow: hidden; min-width: 0; min-height: 0; }
#starmap { width: 100%; height: 100%; display: block; cursor: grab; }

.view-modes {
  position: absolute; top: 12px; left: 50%; transform: translateX(-50%);
  display: flex; gap: 0; background: rgba(5,7,12,0.85); border: 1px solid var(--border-subtle);
  backdrop-filter: blur(4px); z-index: 10;
}
.view-modes button { font-family: var(--mono); font-size: 10px; letter-spacing: 0.18em; padding: 6px 14px; background: transparent; color: var(--text-muted); border: none; cursor: pointer; transition: all 0.15s; text-transform: uppercase; }
.view-modes button + button { border-left: 1px solid var(--border-subtle); }
.view-modes button.active { color: var(--accent); background: rgba(255,138,61,0.08); }
.view-modes button:hover:not(.active) { color: var(--text-secondary); }

.starmap-title { position: absolute; top: 12px; left: 14px; z-index: 10; font-family: var(--mono); font-size: 10px; letter-spacing: 0.2em; color: var(--text-muted); text-transform: uppercase; display: flex; align-items: center; gap: 8px; }
.starmap-title span.data { color: var(--data); }
.pulse { width: 6px; height: 6px; background: var(--accent); border-radius: 50%; box-shadow: 0 0 8px var(--accent); animation: pulse 2s infinite; flex-shrink: 0; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

/* Destination overlay sur la carte */
.dest-overlay {
  position: absolute; top: 12px; right: 12px; z-index: 10;
  background: rgba(5,7,12,0.88); border: 1px solid var(--data);
  padding: 10px 14px; min-width: 210px; backdrop-filter: blur(4px);
  box-shadow: 0 0 12px rgba(127,212,255,0.15);
}
.dest-overlay-head { font-family: var(--mono); font-size: 9px; letter-spacing: 0.2em; color: var(--data); text-transform: uppercase; margin-bottom: 4px; display: flex; align-items: center; gap: 6px; }
.dest-overlay-head::before { content: '◎'; color: var(--data); }
.dest-name { font-size: 15px; font-weight: 600; color: var(--text-primary); margin-bottom: 2px; letter-spacing: 0.03em; }
.dest-name.empty { color: var(--text-muted); font-size: 12px; font-style: italic; }
.dest-meta { font-family: var(--mono); font-size: 10px; color: var(--text-secondary); margin-bottom: 8px; }
.dest-cost-row { display: flex; justify-content: space-between; font-family: var(--mono); font-size: 10px; margin-bottom: 8px; padding: 4px 8px; background: rgba(127,212,255,0.04); border-left: 2px solid var(--warning); }
.dest-cost-row .val { color: var(--warning); }
.dest-overlay-actions { display: flex; gap: 5px; }
.dest-overlay-actions .btn { flex: 1; font-family: var(--mono); font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; padding: 6px 8px; cursor: pointer; background: transparent; color: var(--text-secondary); border: 1px solid var(--border-subtle); transition: all 0.15s; }
.dest-overlay-actions .btn:hover:not(:disabled) { color: var(--text-primary); border-color: var(--border-strong); }
.dest-overlay-actions .btn:disabled { opacity: 0.35; cursor: default; }
.dest-overlay-actions .btn.primary { color: var(--accent); border-color: var(--accent); }
.dest-overlay-actions .btn.primary:hover:not(:disabled) { background: rgba(255,138,61,0.1); box-shadow: 0 0 10px rgba(255,138,61,0.2); }
.dest-overlay-actions .btn.danger { color: var(--danger); border-color: var(--danger); }
.dest-overlay-actions .btn.danger:hover { background: rgba(239,68,68,0.1); }

/* Tooltip */
.sys-tooltip { position: absolute; pointer-events: none; z-index: 20; background: rgba(5,7,12,0.92); backdrop-filter: blur(4px); border: 1px solid var(--data); padding: 8px 12px; font-family: var(--mono); font-size: 10px; color: var(--text-primary); box-shadow: 0 0 12px rgba(127,212,255,0.3); opacity: 0; transition: opacity 0.15s; min-width: 170px; }
.sys-tooltip.show { opacity: 1; }
.sys-tooltip h4 { font-size: 12px; color: var(--data); margin-bottom: 4px; letter-spacing: 0.06em; }
.sys-tooltip .row { display: flex; justify-content: space-between; margin: 1px 0; color: var(--text-secondary); }
.sys-tooltip .cost.ok { color: var(--success); }
.sys-tooltip .cost.mid { color: var(--warning); }
.sys-tooltip .cost.ko { color: var(--danger); }

/* Compass v2 */
.compass {
  position: absolute; bottom: 12px; left: 12px; z-index: 10;
  background: rgba(5,7,12,0.8); backdrop-filter: blur(4px);
  border: 1px solid var(--border-subtle); padding: 8px;
  display: flex; flex-direction: column; gap: 6px; align-items: center;
}
.compass-ring-wrap { position: relative; width: 72px; height: 72px; border: 1px solid var(--border-subtle); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.compass-label { position: absolute; font-family: var(--mono); font-size: 9px; color: var(--text-muted); }
.compass-label.n { top: 2px; left: 50%; transform: translateX(-50%); color: var(--accent); }
.compass-label.s { bottom: 2px; left: 50%; transform: translateX(-50%); }
.compass-label.e { right: 3px; top: 50%; transform: translateY(-50%); }
.compass-label.w { left: 3px; top: 50%; transform: translateY(-50%); }
.compass-dot { width: 5px; height: 5px; background: var(--accent); border-radius: 50%; box-shadow: 0 0 6px var(--accent); }
.compass-axes { display: flex; gap: 3px; }
.axis-btn { font-family: var(--mono); font-size: 10px; font-weight: 700; width: 22px; height: 22px; background: transparent; color: var(--text-muted); border: 1px solid var(--border-subtle); cursor: pointer; transition: all 0.15s; }
.axis-btn:hover { color: var(--text-secondary); border-color: var(--border-strong); }
.axis-btn.active { color: var(--accent); border-color: var(--accent); background: rgba(255,138,61,0.12); box-shadow: 0 0 6px rgba(255,138,61,0.3); }
.compass-caption { font-family: var(--mono); font-size: 8px; letter-spacing: 0.15em; color: var(--text-muted); text-transform: uppercase; }

/* Starmap controls */
.starmap-controls { position: absolute; bottom: 12px; right: 12px; z-index: 10; display: flex; flex-direction: column; gap: 3px; }
.ctrl-btn { width: 28px; height: 28px; background: rgba(5,7,12,0.8); border: 1px solid var(--border-subtle); color: var(--text-secondary); font-family: var(--mono); font-size: 13px; cursor: pointer; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); transition: all 0.15s; }
.ctrl-btn:hover { color: var(--data); border-color: var(--border-strong); }
.ctrl-btn.active { color: var(--data); border-color: var(--data); background: rgba(127,212,255,0.08); }

/* Legend */
.legend { position: absolute; bottom: 12px; left: 50%; transform: translateX(-50%); z-index: 10; background: rgba(5,7,12,0.75); backdrop-filter: blur(4px); border: 1px solid var(--border-subtle); padding: 5px 12px; font-family: var(--mono); font-size: 9px; color: var(--text-secondary); display: flex; gap: 12px; align-items: center; white-space: nowrap; }
.legend-row { display: flex; align-items: center; gap: 4px; }
.legend-glyph { width: 10px; text-align: center; }

/* RIGHT COL */
.right-col { display: flex; flex-direction: column; background: var(--bg-panel); border-left: 1px solid var(--border-subtle); backdrop-filter: blur(6px); overflow: hidden; }
.console { flex: 1; display: flex; flex-direction: column; min-height: 0; }
.console-head { padding: 8px 12px; font-family: var(--mono); font-size: 9px; letter-spacing: 0.25em; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center; background: rgba(5,7,12,0.5); flex-shrink: 0; }
.console-head .status { color: var(--success); display: flex; gap: 5px; align-items: center; }
.console-head .status::before { content: ''; width: 5px; height: 5px; background: var(--success); border-radius: 50%; animation: pulse 2s infinite; }
.console-head .clear-btn { font-family: var(--mono); font-size: 9px; color: var(--text-muted); background: none; border: none; cursor: pointer; letter-spacing: 0.1em; }
.console-head .clear-btn:hover { color: var(--text-primary); }
.console-out { flex: 1; padding: 10px 12px; overflow-y: auto; font-family: var(--mono); font-size: 11.5px; line-height: 1.55; min-height: 0; color: var(--text-secondary); }
.console-line { margin: 0; white-space: pre-wrap; word-break: break-word; }
.console-line.cmd { color: var(--accent); font-weight: 600; }
.console-line.sys { color: var(--text-muted); }
.console-line.ok { color: var(--success); }
.console-line.data { color: var(--data); }
.console-line.warn { color: var(--warning); }
.console-line.err { color: var(--danger); }
.console-line.dim { color: var(--text-muted); font-style: italic; font-size: 10.5px; }
.console-input { border-top: 1px solid var(--border-subtle); background: rgba(5,7,12,0.65); flex-shrink: 0; }
.console-prompt-label { padding: 5px 12px 3px; font-family: var(--mono); font-size: 10px; color: var(--accent); letter-spacing: 0.05em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.console-input input { display: block; width: 100%; box-sizing: border-box; background: transparent; border: none; border-top: 1px solid var(--border-subtle); outline: none; color: var(--text-primary); font-family: var(--mono); font-size: 12px; caret-color: var(--accent); padding: 6px 12px; transition: background 0.15s; }
.console-input input:focus { background: rgba(127,212,255,0.04); }
.shortcuts { padding: 6px 8px; border-top: 1px solid var(--border-subtle); display: grid; grid-template-columns: repeat(6, 1fr); gap: 3px; background: rgba(5,7,12,0.3); flex-shrink: 0; }
.sc { font-family: var(--mono); font-size: 9px; letter-spacing: 0.05em; padding: 4px 2px; text-transform: uppercase; cursor: pointer; background: transparent; color: var(--text-secondary); border: 1px solid var(--border-subtle); transition: all 0.12s; text-align: center; position: relative; }
.sc:hover { color: var(--data); border-color: var(--data); background: rgba(127,212,255,0.05); }
.sc[title]:hover::after { content: attr(title); position: absolute; bottom: calc(100% + 4px); left: 50%; transform: translateX(-50%); background: rgba(5,7,12,0.95); border: 1px solid var(--border-subtle); padding: 3px 6px; font-family: var(--mono); font-size: 9px; color: var(--text-primary); white-space: nowrap; z-index: 50; text-transform: none; pointer-events: none; }

/* RESIZE HANDLE */
.tim-resize-handle { background: var(--border-subtle); cursor: ew-resize; transition: background 0.15s; position: relative; }
.tim-resize-handle::after { content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 2px; height: 40px; background: var(--border-strong); border-radius: 1px; }
.tim-resize-handle:hover, .tim-resize-handle.active { background: rgba(127,212,255,0.12); }
.tim-resize-handle:hover::after, .tim-resize-handle.active::after { background: var(--data); }

/* Scrollbar */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border-subtle); }
::-webkit-scrollbar-thumb:hover { background: var(--border-strong); }
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

// Build galactic data for Three.js (current system + jump targets)
$galacticData = [];
$galacticData[] = [
    'name' => $systemeActuel->nom ?? 'Étoile',
    'x' => 0, 'y' => 0, 'z' => 0,
    'type' => 'star', 'colorHex' => '#ffe680', 'size' => 1.8, 'dist' => 0,
    'id' => 0,
];
foreach ($sautsDisponibles as $dest) {
    $dx = ($dest->secteur_x - $objetSpatial->secteur_x);
    $dy = ($dest->secteur_y - $objetSpatial->secteur_y);
    $dz = ($dest->secteur_z - $objetSpatial->secteur_z);
    $x3 = ($dx == 0 && $dy == 0) ? ($dest->position_x ?? 1) * 2 : $dx * 9;
    $z3 = ($dx == 0 && $dy == 0) ? ($dest->position_y ?? 1) * 2 : $dy * 9;
    $y3 = $dz * 1.8;
    // Couleur : accessible=jaune, visité=cyan, cartographié=gris-bleu, inaccessible=gris
    $visite = $dest->visite ?? false;
    $colorHex = $dest->accessible
        ? ($visite ? '#7fd4ff' : '#ffe680')
        : ($visite ? '#4a8fa8' : '#8b96a8');
    $galacticData[] = [
        'name' => $dest->nom,
        'x' => round($x3, 2), 'y' => round($y3, 2), 'z' => round($z3, 2),
        'sx' => $dest->secteur_x, 'sy' => $dest->secteur_y, 'sz' => $dest->secteur_z,
        'type' => 'star',
        'colorHex' => $colorHex,
        'size' => $visite ? 1.4 : 1.1,
        'dist' => round($dest->distance, 2),
        'id' => $dest->id,
        'accessible' => (bool)$dest->accessible,
        'visite' => (bool)$visite,
        'energieRequise' => $dest->energieRequise,
        'paRequis' => $dest->paRequis,
        'isJumpTarget' => true,
    ];
}

// Build system (local) data for Three.js
// Load planete models (distance from star, satellite links)
$planeteModels = $systemeActuel
    ? $systemeActuel->planetes()->get()->keyBy('id')
    : collect();

// Load station models → map station_id → planete_id (parent body)
$stationModels = $systemeActuel
    ? \App\Models\Station::where('systeme_stellaire_id', $systemeActuel->id)->get()->keyBy('id')
    : collect();

$localPlanets  = []; // corps orbitant directement l'étoile
$localSatellites = []; // lunes ET stations avec un parent

foreach ($poisSecteur as $poi) {
    if ($poi->type_poi === 'planete') {
        $m = $planeteModels->get($poi->id);
        $categorie    = $m ? ($m->categorie ?? 'planete') : 'planete';
        $parentId     = $m ? $m->planete_parente_id : null;
        $distEtoileUA = $m ? ($m->distance_etoile / 100.0) : $poi->distance;
        $displayR     = max(3.5, pow(max(0.5, $distEtoileUA), 0.35) * 7.0);

        $entry = [
            'id' => $poi->id, 'name' => $poi->nom,
            'type' => 'planete', 'poiType' => 'planete',
            'colorHex' => $categorie === 'lune' ? '#c8c8c8' : '#4ade80',
            'size' => $categorie === 'lune' ? 0.22 : 0.38,
            'dist' => round($poi->distance / 63241, 3),
            'distanceUA' => round($poi->distance, 1),
            'categorie' => $categorie,
            'planete_parente_id' => $parentId,
            'orbitRadius' => $displayR,
        ];

        if ($parentId) {
            $localSatellites[] = $entry;
        } else {
            $localPlanets[] = $entry;
        }

    } else { // station
        $sModel   = $stationModels->get($poi->id);
        $parentId = $sModel ? $sModel->planete_id : null;

        $entry = [
            'id' => $poi->id, 'name' => $poi->nom,
            'type' => 'station', 'poiType' => 'station',
            'colorHex' => '#7fd4ff', 'size' => 0.3,
            'dist' => round($poi->distance / 63241, 3),
            'distanceUA' => round($poi->distance, 1),
            'categorie' => 'station',
            'planete_parente_id' => $parentId,
            'orbitRadius' => 0.0, // calculé plus bas
        ];

        if ($parentId) {
            $localSatellites[] = $entry;
        } else {
            // Station sans planète parente : orbite directe autour de l'étoile
            $r = max(3.5, pow(max(0.5, $poi->distance), 0.35) * 7.0);
            $entry['orbitRadius'] = $r;
            $localPlanets[] = $entry;
        }
    }
}

// --- Étape 1 : positionner les corps autour de l'étoile ---
$nPlanets = count($localPlanets);
foreach ($localPlanets as $i => &$planet) {
    $angle = $i * (M_PI * 2 / max($nPlanets, 1));
    $planet['x'] = round(cos($angle) * $planet['orbitRadius'], 2);
    $planet['z'] = round(sin($angle) * $planet['orbitRadius'], 2);
    $planet['y'] = 0.0;
}
unset($planet);

// Map de tous les corps par id (planètes d'abord, puis on ajoutera les lunes au fur et à mesure)
$bodiesById = [];
foreach ($localPlanets as $p) $bodiesById[$p['id']] = $p;

// --- Étape 2 : positionner satellites (lunes + stations) en 2 passes ---
// Passe 1 : lunes (parent = planète, déjà dans bodiesById)
// Passe 2 : stations dont le parent est une lune (besoin de la position de la lune)
$pass1 = array_filter($localSatellites, fn($s) => isset($bodiesById[$s['planete_parente_id'] ?? -1]));
$pass2 = array_filter($localSatellites, fn($s) => !isset($bodiesById[$s['planete_parente_id'] ?? -1]));

$satAngleOffset = []; // angle courant par parent_id pour éviter superpositions

function positionSatellite(array &$sat, array $bodiesById, array &$satAngleOffset, \Illuminate\Support\Collection $planeteModels): void {
    $parentId = $sat['planete_parente_id'];
    $parent   = $bodiesById[$parentId] ?? null;

    // Angle décalé par parent pour espacer les satellites du même corps
    $satAngleOffset[$parentId] = ($satAngleOffset[$parentId] ?? 0.5) + 1.1;
    $angle = $satAngleOffset[$parentId];

    // Rayon orbital affiché
    if ($sat['categorie'] === 'lune') {
        $mModel = $planeteModels->get($sat['id']);
        $distPlaneteUA = $mModel ? max(0.0001, (float)($mModel->distance_planete ?? 0.001)) : 0.001;
        $orbitR = min(0.65, max(0.3, pow($distPlaneteUA * 1000, 0.28) * 0.45));
    } else {
        // Station : rayon fixe légèrement plus grand que la lune typique
        $orbitR = 0.5;
    }
    $sat['orbitRadius'] = $orbitR;

    if ($parent) {
        $sat['x'] = round($parent['x'] + cos($angle) * $orbitR, 2);
        $sat['z'] = round($parent['z'] + sin($angle) * $orbitR, 2);
        $sat['y'] = 0.0;
        $sat['parentX'] = $parent['x'];
        $sat['parentZ'] = $parent['z'];
    } else {
        $sat['x'] = round(cos($angle) * 4.0, 2);
        $sat['z'] = round(sin($angle) * 4.0, 2);
        $sat['y'] = 0.0;
        $sat['parentX'] = 0; $sat['parentZ'] = 0;
    }
    $sat['moonOrbitRadius'] = $orbitR;
}

$orderedSatellites = [];
foreach ($pass1 as &$sat) {
    positionSatellite($sat, $bodiesById, $satAngleOffset, $planeteModels);
    $bodiesById[$sat['id']] = $sat; // rend la lune disponible pour passe2
    $orderedSatellites[] = $sat;
}
unset($sat);
foreach ($pass2 as &$sat) {
    positionSatellite($sat, $bodiesById, $satAngleOffset, $planeteModels);
    $orderedSatellites[] = $sat;
}
unset($sat);

$localPOIs = array_merge($localPlanets, $orderedSatellites);

$calculSaut = session('dernier_calcul_saut');

// Position du vaisseau dans la scène système — même échelle non-linéaire que les planètes
// Formule planètes : orbitRadius = max(3.5, pow(distUA, 0.35) * 7.0)
$starOffX  = $systemeActuel ? ($systemeActuel->position_x ?? 0) : 0;
$starOffY  = $systemeActuel ? ($systemeActuel->position_y ?? 0) : 0;
$dxCua     = (float)$objetSpatial->position_x - (float)$starOffX;
$dyCua     = (float)$objetSpatial->position_y - (float)$starOffY;
$distCua   = sqrt($dxCua * $dxCua + $dyCua * $dyCua);
$distUA    = $distCua / 100.0;
$shipAngle = $distCua > 0.001 ? atan2($dyCua, $dxCua) : 0.0;
$shipSceneR = $distUA < 0.1
    ? 1.5  // très proche de l'étoile
    : max(3.5, pow(max(0.5, $distUA), 0.35) * 7.0);
$shipSceneX = round(cos($shipAngle) * $shipSceneR, 2);
$shipSceneZ = round(sin($shipAngle) * $shipSceneR, 2);

// Coordonnées locales pour mode système (UA depuis l'étoile)
$shipLocalXUA = round($dxCua / 100, 1);
$shipLocalYUA = round($dyCua / 100, 1);
$shipLocalDistUA = round($distUA, 1);
@endphp

@section('content')

<!-- HUD HEADER -->
<header class="hud-header">
  <div class="hud-brand">CONQUÊTE SPATIALE</div>
  <div class="hud-sep"></div>
  <div class="hud-block">
    <span class="hud-label">CMDR</span>
    <span class="hud-value">{{ $cmdName }}</span>
  </div>
  <div class="hud-block">
    <span class="hud-label">Position</span>
    <span class="hud-value data">{{ $sectorLabel }}</span>
  </div>
  <div class="hud-block">
    <span class="hud-label">Date galact.</span>
    <span class="hud-value" id="gclock">{{ $dateJeuActuelle ? $dateJeuActuelle->format('Y') . '.' . $dateJeuActuelle->dayOfYear : '—' }}</span>
  </div>
  <div class="hud-sep"></div>
  <div class="gauge-group">
    <div class="gauge" title="Points d'action : {{ $paVal }}/{{ $paMax }}">
      <span class="gauge-icon">PA</span>
      <div class="gauge-bar"><div class="gauge-fill {{ $paClass }}" style="width: {{ $paPct }}%"></div></div>
      <span class="gauge-val" id="hud-pa">{{ $paVal }}/{{ $paMax }}</span>
    </div>
    <div class="gauge" title="Énergie : {{ $enVal }}/{{ $enMax }}">
      <span class="gauge-icon">⚡</span>
      <div class="gauge-bar"><div class="gauge-fill {{ $enClass }}" style="width: {{ $enPct }}%"></div></div>
      <span class="gauge-val" id="hud-en">{{ $enPct }}%</span>
    </div>
    <div class="gauge" title="Coque : {{ $coqVal }}/{{ $coqMax }}">
      <span class="gauge-icon">◈</span>
      <div class="gauge-bar"><div class="gauge-fill {{ $coqClass }}" style="width: {{ $coqPct }}%"></div></div>
      <span class="gauge-val">{{ $coqPct }}%</span>
    </div>
    <div class="gauge" title="Bouclier : {{ $bouVal }}/100">
      <span class="gauge-icon">◎</span>
      @if($vaisseau->bouclier_id)
      <div class="gauge-bar"><div class="gauge-fill {{ $bouClass }}" style="width: {{ $bouVal }}%"></div></div>
      <span class="gauge-val">{{ $bouVal }}%</span>
      @else
      <div class="gauge-bar"><div class="gauge-fill low" style="width:0%"></div></div>
      <span class="gauge-val" style="color:var(--text-muted)">—</span>
      @endif
    </div>
    <div class="gauge" title="Cargaison : {{ $cargoVal }}/{{ $cargoMax }}t">
      <span class="gauge-icon">▣</span>
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

<!-- CALCUL EN COURS (banner) -->
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
    Jet {{ $calculSaut['jet_navigation'] }} · Erreur {{ $calculSaut['score_erreur'] }}
    @if($calculSaut['est_critique']) · <span style="color:var(--warning)">★ CRITIQUE</span> @endif
  </span>
  <div class="calc-actions">
    <button class="calc-btn cancel" onclick="annulerCalculSaut()">✕ Annuler</button>
    <button class="calc-btn improve" onclick="ameliorerCalculSaut()">↑ Améliorer (1 PA)</button>
    <button class="calc-btn jump" onclick="effectuerSaut({{ $calculSaut['destination_id'] }})">▶ Initier saut</button>
  </div>
</div>
@endif

<!-- MAIN STAGE -->
<div class="stage">

  <!-- LEFT NAV -->
  <nav class="nav">
    <div class="nav-scroll">

      {{-- Section contextuelle : Vaisseau ou Station --}}
      @if($personnage->dans_station_id)
      <div class="nav-section">
        <div class="nav-section-head">Station</div>
        <a href="{{ route('station.hall') }}" class="nav-item">Hall <span class="nav-cmd">→</span></a>
        <a href="{{ route('station.hangar') }}" class="nav-item">Hangar <span class="nav-cmd">→</span></a>
        <a href="{{ route('station.marche') }}" class="nav-item">Marché <span class="nav-cmd">→</span></a>
        <a href="{{ route('station.missions') }}" class="nav-item">Missions <span class="nav-cmd">→</span></a>
        <a href="{{ route('station.cantina') }}" class="nav-item">Cantina <span class="nav-cmd">→</span></a>
      </div>
      @else
      <div class="nav-section">
        <div class="nav-section-head">Vaisseau</div>
        <a href="{{ route('navire.timonerie') }}" class="nav-item active">Timonerie <span class="nav-cmd">→</span></a>
        <a href="{{ route('navire.ingenierie') }}" class="nav-item">Ingénierie <span class="nav-cmd">→</span></a>
        <a href="{{ route('navire.com') }}" class="nav-item">COM <span class="nav-cmd">→</span></a>
        <a href="{{ route('navire.soute') }}" class="nav-item">Soute <span class="nav-cmd">→</span></a>
        <a href="{{ route('navire.equipage') }}" class="nav-item">Équipage <span class="nav-cmd">→</span></a>
      </div>
      @endif

      <!-- MODULE INSTRUMENTS (timonerie-specific) -->
      <div class="nav-module">
        <div class="nav-module-head"><span>Instruments</span><span class="live">LIVE</span></div>
        <div class="nav-module-body">
          <div class="inst-grid">
            <div class="inst">
              <span class="inst-label" id="inst-pos-label">Secteur</span>
              <span class="inst-val" id="inst-pos-val">{{ $objetSpatial->secteur_x }},{{ $objetSpatial->secteur_y }},{{ $objetSpatial->secteur_z }}</span>
            </div>
            <div class="inst">
              <span class="inst-label" id="inst-ctx-label">Système</span>
              <span class="inst-val data" id="inst-ctx-val">{{ $systemeName }}</span>
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
      </div>

      <!-- MODULE SCANNER (timonerie-specific) -->
      <div class="nav-module">
        <div class="nav-module-head"><span>Scanner</span><span style="color:var(--data); font-family:var(--mono); font-size:9px;">{{ $scanCount }} obj</span></div>
        <div class="nav-module-body" style="padding: 5px 6px 7px;">
          <div class="scan-mode">
            <button class="active" id="scan-tab-local" onclick="switchScanTab('local')">Local</button>
            <button id="scan-tab-galact" onclick="switchScanTab('galact')">Galact.</button>
          </div>
          <div class="scan-list" id="scan-list-local">
            @if($systemeActuel)
            <div class="scan-item" onclick="selectScanItem(this, 'star', 0, '{{ e($systemeActuel->nom) }}', 0, false)">
              <span class="scan-glyph star">★</span>
              <span class="scan-name">{{ $systemeActuel->nom }}</span>
              <span class="scan-dist">étoile</span>
            </div>
            @endif
            @forelse($poisSecteur as $poi)
            @php
              $distUA = $poi->distance;
              $glyphClass = $poi->type_poi === 'station' ? 'station' : (($poi->categorie ?? '') === 'lune' ? 'moon' : 'planet');
              $glyphChar = $poi->type_poi === 'station' ? '⊙' : (($poi->categorie ?? '') === 'lune' ? '○' : '◇');
            @endphp
            <div class="scan-item" onclick="selectScanItem(this, '{{ $poi->type_poi }}', {{ $poi->id }}, '{{ e($poi->nom) }}', {{ $distUA }}, false)">
              <span class="scan-glyph {{ $glyphClass }}">{{ $glyphChar }}</span>
              <span class="scan-name">{{ $poi->nom }}</span>
              <span class="scan-dist">{{ number_format($distUA, 2) }} UA</span>
            </div>
            @empty
            <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);padding:6px;text-align:center;">Aucun POI</div>
            @endforelse
          </div>
          <div class="scan-list" id="scan-list-galact" style="display:none;">
            @forelse($sautsDisponibles as $dest)
            <div class="scan-item" onclick="selectScanItem(this, 'star', {{ $dest->id }}, '{{ e($dest->nom) }}', {{ round($dest->distance, 2) }}, true, {{ $dest->accessible ? 'true' : 'false' }}, {{ $dest->energieRequise ?? 0 }}, {{ $dest->paRequis ?? 0 }})">
              <span class="scan-glyph {{ $dest->visite ? 'station' : 'star' }}">{{ $dest->visite ? '◈' : '★' }}</span>
              <span class="scan-name">{{ $dest->nom }}</span>
              <span class="scan-dist">{{ number_format($dest->distance, 1) }} AL</span>
            </div>
            @empty
            <div style="font-family:var(--mono);font-size:10px;color:var(--text-muted);padding:6px;text-align:center;">Aucun saut</div>
            @endforelse
          </div>
        </div>
      </div>

      <div class="nav-section">
        <div class="nav-section-head">Personnage</div>
        <a href="{{ route('personnage.dossier') }}" class="nav-item">Dossier <span class="nav-cmd">→</span></a>
        <a href="{{ route('personnage.spatiocarte') }}" class="nav-item">Spatiocarte <span class="nav-cmd">→</span></a>
        <a href="{{ route('personnage.gestion') }}" class="nav-item">Gestion <span class="nav-cmd">→</span></a>
      </div>

      <div class="nav-section">
        <div class="nav-section-head">Jeu</div>
        <a href="{{ route('jeu.profil') }}" class="nav-item">Profil <span class="nav-cmd">→</span></a>
      </div>

      @if(auth()->user()->is_admin ?? false)
      <div class="nav-section">
        <div class="nav-section-head" style="color:var(--danger)">Administration</div>
        <a href="{{ route('admin.index') }}" class="nav-item" style="color:#ff6b6b;">Univers</a>
      </div>
      @endif
    </div>
  </nav>

  <!-- CENTER — STARMAP -->
  <div class="starmap-wrap">
    <canvas id="starmap"></canvas>

    <!-- Mode switcher -->
    <div class="view-modes">
      <button class="active" id="view-btn-galactic" onclick="setViewMode('galactic')">Galactique</button>
      <button id="view-btn-system" onclick="setViewMode('system')">Système</button>
    </div>

    <!-- Title label -->
    <div class="starmap-title">
      <span class="pulse"></span>
      <span id="view-label">Vue galactique — {{ count($sautsDisponibles) }} systèmes accessibles</span>
    </div>

    <!-- Destination overlay -->
    <div id="dest-overlay" class="dest-overlay" style="{{ $calculSaut ? '' : 'display:none;' }}">
        <div class="dest-overlay-head" id="dest-head">{{ $calculSaut ? 'Destination verrouillée' : 'Destination' }}</div>
        <div class="dest-name {{ !$calculSaut ? 'empty' : '' }}" id="dest-name">
          {{ $calculSaut ? $calculSaut['destination_nom'] : 'Aucune cible' }}
        </div>
        @php
          $coordsStyle = 'font-family:var(--mono);font-size:9px;color:var(--text-muted);margin-bottom:2px;';
          $coordsStyle .= $calculSaut ? '' : 'display:none;';
          $coordsTxt = $calculSaut
            ? 'x: '.($calculSaut['position_cible']['secteur_x'] ?? '?')
              .', y: '.($calculSaut['position_cible']['secteur_y'] ?? '?')
              .', z: '.($calculSaut['position_cible']['secteur_z'] ?? '?').' AL'
            : '';
        @endphp
        <div class="dest-coords" id="dest-coords" style="{{ $coordsStyle }}">{{ $coordsTxt }}</div>
        <div class="dest-meta" id="dest-meta">
          @if($calculSaut)
            {{ number_format($calculSaut['distance'], 2) }} AL · Jet {{ $calculSaut['jet_navigation'] }} · Erreur {{ $calculSaut['score_erreur'] }}
          @else
            Cliquez sur la carte ou le scanner
          @endif
        </div>
        <div class="dest-cost-row" id="dest-cost-row" style="{{ $calculSaut ? '' : 'display:none;' }}">
          <span>Coût du saut</span>
          <span class="val" id="dest-cost">{{ $calculSaut ? $calculSaut['energie_requise'].' E · '.$calculSaut['pa_requis'].' PA' : '—' }}</span>
        </div>
        <div class="dest-overlay-actions">
          <button class="btn danger" id="btn-annuler" onclick="{{ $calculSaut ? 'annulerCalculSaut()' : 'clearTarget()' }}" {{ !$calculSaut ? 'disabled' : '' }}>Annuler</button>
          <button class="btn primary" id="btn-action"
            @if($calculSaut)
              onclick="effectuerSaut({{ $calculSaut['destination_id'] }})"
            @else
              disabled
            @endif
          >
            {{ $calculSaut ? '▶ Initier saut' : 'Sélectionner cible' }}
          </button>
        </div>
    </div>

    <!-- Tooltip -->
    <div class="sys-tooltip" id="tooltip">
      <h4 id="tt-name">—</h4>
      <div class="row"><span>Distance</span><span id="tt-dist">—</span></div>
      <div class="row"><span>Coût énergie</span><span id="tt-cost" class="cost">—</span></div>
      <div class="row"><span>Type</span><span id="tt-type">—</span></div>
      <div class="row" style="margin-top:4px;font-size:9px;color:var(--text-muted);">Clic pour cibler</div>
    </div>

    <!-- Compass XYZ -->
    <div class="compass">
      <div class="compass-caption">Plan vertical</div>
      <div class="compass-axes">
        <button class="axis-btn" data-axis="X" onclick="setVerticalAxis('X')">X</button>
        <button class="axis-btn" data-axis="Y" onclick="setVerticalAxis('Y')">Y</button>
        <button class="axis-btn active" data-axis="Z" onclick="setVerticalAxis('Z')">Z</button>
      </div>
      <div class="compass-ring-wrap">
        <span class="compass-label n">N</span>
        <span class="compass-label s">S</span>
        <span class="compass-label e">E</span>
        <span class="compass-label w">W</span>
        <div class="compass-dot"></div>
      </div>
    </div>

    <!-- Zoom controls -->
    <div class="starmap-controls">
      <button class="ctrl-btn" id="zoom-in" title="Zoom +">+</button>
      <button class="ctrl-btn" id="zoom-out" title="Zoom −">−</button>
      <button class="ctrl-btn" id="reset-view" title="Vue par défaut">⌂</button>
      <button class="ctrl-btn active" id="toggle-grid" title="Grille">#</button>
      <button class="ctrl-btn active" id="toggle-orbits" title="Orbites">◯</button>
    </div>

    <!-- Legend -->
    <div class="legend">
      <div class="legend-row"><span class="legend-glyph" style="color:var(--accent)">▲</span>Vaisseau</div>
      <div class="legend-row"><span class="legend-glyph" style="color:var(--warning)">★</span>Étoile</div>
      <div class="legend-row"><span class="legend-glyph" style="color:var(--success)">◇</span>Planète</div>
      <div class="legend-row"><span class="legend-glyph" style="color:var(--text-secondary)">·</span>Lune</div>
      <div class="legend-row"><span class="legend-glyph" style="color:var(--data)">⊙</span>Station</div>
      <div class="legend-row" style="padding-left:8px;border-left:1px solid var(--border-subtle)"><span style="color:var(--success)">━</span>Accessible</div>
      <div class="legend-row"><span style="color:var(--warning)">━</span>Coûteux</div>
      <div class="legend-row"><span style="color:var(--danger)">━</span>Impossible</div>
    </div>
  </div>

  <!-- RESIZE HANDLE -->
  <div class="tim-resize-handle" id="tim-resize-handle"></div>

  <!-- RIGHT — CONSOLE -->
  <div class="right-col">
    <div class="console">
      <div class="console-head">
        <span>Console</span>
        <div style="display:flex;gap:10px;align-items:center;">
          <button class="clear-btn" onclick="document.getElementById('console-out').innerHTML=''">clear</button>
          <span class="status">Connecté</span>
        </div>
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
        <div class="console-prompt-label">{{ strtolower($cmdName) . '@' . strtolower($shipName) }}</div>
        <input type="text" id="cmd-input" placeholder="commande… (help)" autocomplete="off">
      </div>
      <div class="shortcuts">
        <button class="sc" onclick="handleShortcut('scan')" title="Scanner les objets proches">Scan</button>
        <button class="sc" onclick="handleShortcut('saut')" title="Initier le saut vers la destination">Saut</button>
        <button class="sc" onclick="handleShortcut('recharger')" title="Recharger l'énergie">Rech</button>
        <button class="sc" onclick="handleShortcut('position')" title="Afficher la position">Pos</button>
        <button class="sc" onclick="handleShortcut('inv')" title="Aller à la soute">Soute</button>
        <button class="sc" onclick="handleShortcut('help')" title="Liste des commandes">?</button>
      </div>
    </div>
  </div>

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

// ============================================================
// DATA (depuis Laravel)
// ============================================================
const galacticData  = @json($galacticData);
const localData     = @json($localPOIs);
const shipInitX     = {{ $shipSceneX }};
const shipInitZ     = {{ $shipSceneZ }};
// Exposer pour les scripts non-module
window._localData   = localData;
window._moveShipTo  = null; // sera défini après buildScene()

// ============================================================
// THREE.JS SETUP
// ============================================================
const canvas = document.getElementById('starmap');
const wrap = canvas.parentElement;

const scene = new THREE.Scene();
scene.fog = new THREE.FogExp2(0x05070c, 0.008);

const camera = new THREE.PerspectiveCamera(60, wrap.clientWidth / wrap.clientHeight, 0.1, 2000);

const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
renderer.setSize(wrap.clientWidth, wrap.clientHeight);
renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

// Axe vertical logique (Z par défaut)
let verticalAxis = 'Z';
function toSceneVec(x, y, z) {
  if (verticalAxis === 'Y') return new THREE.Vector3(x, y, z);
  if (verticalAxis === 'Z') return new THREE.Vector3(x, z, y);
  if (verticalAxis === 'X') return new THREE.Vector3(y, x, z);
  return new THREE.Vector3(x, y, z);
}

// ============================================================
// STARFIELD
// ============================================================
const sfGeo = new THREE.BufferGeometry();
const sfCount = 3000;
const sfPos = new Float32Array(sfCount * 3);
const sfCol = new Float32Array(sfCount * 3);
for (let i = 0; i < sfCount; i++) {
  const r = 400 + Math.random() * 600;
  const t2 = Math.random() * Math.PI * 2;
  const p2 = Math.acos(2 * Math.random() - 1);
  sfPos[i*3] = r * Math.sin(p2) * Math.cos(t2);
  sfPos[i*3+1] = r * Math.sin(p2) * Math.sin(t2);
  sfPos[i*3+2] = r * Math.cos(p2);
  const b = 0.3 + Math.random() * 0.7;
  sfCol[i*3] = b; sfCol[i*3+1] = b; sfCol[i*3+2] = b + Math.random() * 0.2;
}
sfGeo.setAttribute('position', new THREE.BufferAttribute(sfPos, 3));
sfGeo.setAttribute('color', new THREE.BufferAttribute(sfCol, 3));
scene.add(new THREE.Points(sfGeo, new THREE.PointsMaterial({ size: 0.8, vertexColors: true, transparent: true, opacity: 0.8, sizeAttenuation: false })));

// ============================================================
// GRID
// ============================================================
const gridGroup = new THREE.Group();
scene.add(gridGroup);
function buildGrid(size, div) {
  gridGroup.clear();
  const mat = new THREE.LineBasicMaterial({ color: 0x1f2937, transparent: true, opacity: 0.6 });
  const matM = new THREE.LineBasicMaterial({ color: 0x2d3b4f, transparent: true, opacity: 0.8 });
  for (let i = -div; i <= div; i++) {
    const x = (i / div) * size;
    const isMaj = i % 5 === 0;
    gridGroup.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints([new THREE.Vector3(x,0,-size), new THREE.Vector3(x,0,size)]), isMaj ? matM : mat));
    gridGroup.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints([new THREE.Vector3(-size,0,x), new THREE.Vector3(size,0,x)]), isMaj ? matM : mat));
  }
}

// ============================================================
// GLOW HELPER
// ============================================================
function makeGlowTexture(colorHex) {
  const size = 128;
  const c = document.createElement('canvas');
  c.width = c.height = size;
  const ctx = c.getContext('2d');
  const grad = ctx.createRadialGradient(size/2,size/2,0, size/2,size/2,size/2);
  const col = new THREE.Color(colorHex);
  const rgb = `${Math.floor(col.r*255)},${Math.floor(col.g*255)},${Math.floor(col.b*255)}`;
  grad.addColorStop(0, `rgba(${rgb},1)`);
  grad.addColorStop(0.2, `rgba(${rgb},0.5)`);
  grad.addColorStop(0.5, `rgba(${rgb},0.15)`);
  grad.addColorStop(1, `rgba(${rgb},0)`);
  ctx.fillStyle = grad; ctx.fillRect(0,0,size,size);
  return new THREE.CanvasTexture(c);
}

// ============================================================
// SCENE OBJECTS (rebuilt on mode switch)
// ============================================================
const objectsGroup = new THREE.Group();
const orbitsGroup  = new THREE.Group();
scene.add(objectsGroup);
scene.add(orbitsGroup);

let pickables = [];
let scanRing = null;
let orbitsVisible = true;
let viewMode = new URLSearchParams(location.search).get('mode') || 'galactic';
window.reloadWithMode = function reloadWithMode() {
  // Persist console lines and command history across page reload
  const out = document.getElementById('console-out');
  if (out) {
    const lines = Array.from(out.children).map(d => ({ t: d.textContent, c: d.className }));
    sessionStorage.setItem('timConsole', JSON.stringify(lines));
  }
  if (window._timCmdHistory) sessionStorage.setItem('timCmdHistory', JSON.stringify(window._timCmdHistory));
  const url = new URL(location.href);
  url.searchParams.set('mode', viewMode);
  location.href = url.toString();
}

function clearGroup(g) {
  while (g.children.length) {
    const c = g.children[0];
    g.remove(c);
    if (c.geometry) c.geometry.dispose();
    if (c.material) {
      if (Array.isArray(c.material)) c.material.forEach(m => m.dispose());
      else c.material.dispose();
    }
  }
}

// Vaisseau (persistant)
const shipGroup = new THREE.Group();
const shipMesh = new THREE.Mesh(new THREE.ConeGeometry(0.2,0.6,4), new THREE.MeshBasicMaterial({ color: 0xff8a3d }));
shipMesh.rotation.x = Math.PI/2; shipMesh.rotation.z = Math.PI/4;
shipGroup.add(shipMesh);
const halo = new THREE.Sprite(new THREE.SpriteMaterial({ map: makeGlowTexture('#ff8a3d'), blending: THREE.AdditiveBlending, transparent: true, opacity: 0.9 }));
halo.scale.set(2,2,1);
shipGroup.add(halo);
scene.add(shipGroup);

// Ligne destination — part toujours de la position du vaisseau
let destLine = null;
function setDestinationLine(pos) {
  if (destLine) { scene.remove(destLine); destLine = null; }
  if (!pos) return;
  const costRatio = (pos.dist || 0) / 25;
  const color = costRatio <= 0.3 ? 0x4ade80 : costRatio <= 0.7 ? 0xfbbf24 : 0xef4444;
  const sx = shipGroup.position.x;
  const sy = shipGroup.position.y;
  const sz = shipGroup.position.z;
  const stp = toSceneVec(pos.x || 0, pos.y || 0, pos.z || 0);
  const tx = stp.x, ty = stp.y, tz = stp.z;
  const midY = Math.max(sy, ty) + Math.max(2, Math.sqrt((tx-sx)**2+(tz-sz)**2)*0.15);
  const curve = new THREE.CatmullRomCurve3([
    new THREE.Vector3(sx, sy, sz),
    new THREE.Vector3((sx+tx)*0.5, midY, (sz+tz)*0.5),
    new THREE.Vector3(tx, ty, tz)
  ]);
  const geo = new THREE.BufferGeometry().setFromPoints(curve.getPoints(60));
  const mat = new THREE.LineDashedMaterial({ color, dashSize:0.4, gapSize:0.2, transparent:true, opacity:0.85 });
  destLine = new THREE.Line(geo, mat);
  destLine.computeLineDistances();
  scene.add(destLine);
}

function buildScene() {
  clearGroup(objectsGroup);
  clearGroup(orbitsGroup);
  pickables = [];
  if (scanRing) { scene.remove(scanRing); scanRing.geometry.dispose(); scanRing = null; }

  if (viewMode === 'galactic') {
    buildGrid(100, 20);
    buildGalactic();
  } else {
    buildGrid(40, 20);
    buildSystem();
  }
}

function buildGalactic() {
  const ring = new THREE.Mesh(new THREE.RingGeometry(14.9,15,128), new THREE.MeshBasicMaterial({ color: 0x7fd4ff, transparent: true, opacity: 0.35, side: THREE.DoubleSide }));
  ring.rotation.x = -Math.PI/2;
  scanRing = ring;
  scene.add(scanRing);

  galacticData.forEach(sys => {
    const g = new THREE.Group();
    const pos = toSceneVec(sys.x || 0, sys.y || 0, sys.z || 0);
    g.position.copy(pos);
    g.userData = sys;

    const core = new THREE.Mesh(new THREE.SphereGeometry(sys.size*0.5,16,16), new THREE.MeshBasicMaterial({ color: new THREE.Color(sys.colorHex) }));
    g.add(core);

    const glow = new THREE.Sprite(new THREE.SpriteMaterial({ map: makeGlowTexture(sys.colorHex), blending: THREE.AdditiveBlending, transparent: true, opacity: 0.8 }));
    glow.scale.set(sys.size*3, sys.size*3, 1);
    g.add(glow);

    if (pos.y !== 0) {
      const lm = new THREE.LineBasicMaterial({ color: new THREE.Color(sys.colorHex), transparent: true, opacity: 0.2 });
      g.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints([new THREE.Vector3(0,0,0), new THREE.Vector3(0,-pos.y,0)]), lm));
      const mk = new THREE.Mesh(new THREE.CircleGeometry(sys.size*0.3,16), new THREE.MeshBasicMaterial({ color: new THREE.Color(sys.colorHex), transparent: true, opacity: 0.4, side: THREE.DoubleSide }));
      mk.rotation.x = -Math.PI/2; mk.position.y = -pos.y;
      g.add(mk);
    }

    objectsGroup.add(g);
    pickables.push({ core, data: sys, group: g });
  });

  shipGroup.position.set(0,0.5,0);
  setCameraDefault(80);
  document.getElementById('view-label').textContent = 'Vue galactique — {{ count($sautsDisponibles) }} systèmes';
}

function buildSystem() {
  // Étoile centrale
  const starColor = '#ffe680';
  const sGroup = new THREE.Group();
  const starCore = new THREE.Mesh(new THREE.SphereGeometry(2.5,32,32), new THREE.MeshBasicMaterial({ color: new THREE.Color(starColor) }));
  sGroup.add(starCore);
  const starGlow = new THREE.Sprite(new THREE.SpriteMaterial({ map: makeGlowTexture(starColor), blending: THREE.AdditiveBlending, transparent: true, opacity: 0.9 }));
  starGlow.scale.set(10,10,1);
  sGroup.add(starGlow);
  sGroup.userData = { name: '{{ $systemeActuel->nom ?? "Étoile" }}', type: 'star', dist: 0, id: 0 };
  objectsGroup.add(sGroup);
  pickables.push({ core: starCore, data: sGroup.userData, group: sGroup });

  // Séparer : corps sans parent (orbitent l'étoile) vs satellites (lunes + stations avec parent)
  const primary    = localData.filter(p => !p.planete_parente_id);
  const satellites = localData.filter(p =>  p.planete_parente_id);

  // --- CORPS PRIMAIRES : orbite autour de l'étoile ---
  primary.forEach(poi => {
    const r = Math.sqrt(poi.x*poi.x + poi.z*poi.z);
    if (r > 0.1) {
      const pts = new THREE.EllipseCurve(0,0,r,r,0,Math.PI*2,false,0)
        .getPoints(128).map(pt => new THREE.Vector3(pt.x,0,pt.y));
      const col = poi.type === 'station' ? 0x7fd4ff : 0x4ade80;
      orbitsGroup.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints(pts),
        new THREE.LineBasicMaterial({ color: col, transparent: true, opacity: 0.15 })));
    }

    const pGroup = new THREE.Group();
    pGroup.position.set(poi.x, 0, poi.z);
    const sz = poi.size * 0.5;
    const pCore = poi.type === 'station'
      ? new THREE.Mesh(new THREE.BoxGeometry(sz, sz*0.4, sz), new THREE.MeshBasicMaterial({ color: new THREE.Color(poi.colorHex) }))
      : new THREE.Mesh(new THREE.SphereGeometry(sz, 24, 24), new THREE.MeshBasicMaterial({ color: new THREE.Color(poi.colorHex) }));
    pGroup.add(pCore);
    const pGlow = new THREE.Sprite(new THREE.SpriteMaterial({ map: makeGlowTexture(poi.colorHex), blending: THREE.AdditiveBlending, transparent: true, opacity: 0.55 }));
    pGlow.scale.set(poi.size*3, poi.size*3, 1);
    pGroup.add(pGlow);
    pGroup.userData = poi;
    objectsGroup.add(pGroup);
    pickables.push({ core: pCore, data: poi, group: pGroup });
  });

  // --- SATELLITES (lunes + stations) : orbite autour de leur parent ---
  satellites.forEach(sat => {
    const px = sat.parentX || 0;
    const pz = sat.parentZ || 0;
    const mr = sat.moonOrbitRadius || sat.orbitRadius || 0.5;

    // Orbite centrée sur le parent
    const oPts = new THREE.EllipseCurve(0,0,mr,mr,0,Math.PI*2,false,0)
      .getPoints(64).map(pt => new THREE.Vector3(pt.x + px, 0, pt.y + pz));
    const oCol = sat.type === 'station' ? 0x7fd4ff : 0xa0a0b0;
    orbitsGroup.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints(oPts),
      new THREE.LineBasicMaterial({ color: oCol, transparent: true, opacity: 0.2 })));

    const sGroup = new THREE.Group();
    sGroup.position.set(sat.x, 0, sat.z);
    const sz = sat.size * 0.5;
    const sCore = sat.type === 'station'
      ? new THREE.Mesh(new THREE.BoxGeometry(sz, sz*0.4, sz), new THREE.MeshBasicMaterial({ color: new THREE.Color(sat.colorHex) }))
      : new THREE.Mesh(new THREE.SphereGeometry(sz, 16, 16), new THREE.MeshBasicMaterial({ color: new THREE.Color(sat.colorHex) }));
    sGroup.add(sCore);
    const sGlow = new THREE.Sprite(new THREE.SpriteMaterial({ map: makeGlowTexture(sat.colorHex), blending: THREE.AdditiveBlending, transparent: true, opacity: 0.45 }));
    sGlow.scale.set(sat.size*2.5, sat.size*2.5, 1);
    sGroup.add(sGlow);
    sGroup.userData = sat;
    objectsGroup.add(sGroup);
    pickables.push({ core: sCore, data: sat, group: sGroup });
  });

  shipGroup.position.set(shipInitX || 3, 1, shipInitZ || 2);
  orbitsGroup.visible = orbitsVisible;
  setCameraDefault(40);
  document.getElementById('view-label').textContent = 'Vue système — {{ $systemeActuel->nom ?? "Local" }} · {{ count($poisSecteur) }} corps';
}

// ============================================================
// CAMERA (orbital + pan)
// ============================================================
let theta = 0, phi = 1, radius = 80;
const cameraTarget = new THREE.Vector3(0, 0, 0);
let cameraMoveTarget = null;   // smooth pan destination
let cameraRadiusTarget = null; // smooth zoom destination

function setCameraDefault(r) {
  theta = 0; phi = 1; radius = r;
  cameraTarget.set(0, 0, 0);
  cameraMoveTarget = null; cameraRadiusTarget = null;
  updateCamera();
}
function updateCamera() {
  camera.position.x = cameraTarget.x + radius * Math.sin(phi) * Math.sin(theta);
  camera.position.y = cameraTarget.y + radius * Math.cos(phi);
  camera.position.z = cameraTarget.z + radius * Math.sin(phi) * Math.cos(theta);
  camera.lookAt(cameraTarget);
  // Keep ship at constant screen size regardless of zoom level
  shipGroup.scale.setScalar(radius * 0.025);
}

// Animate camera to a new target position + optional new radius
function animateCameraTo(pos, newRadius) {
  cameraMoveTarget = pos.clone();
  if (newRadius !== null) cameraRadiusTarget = newRadius;
}

let isDragging = false, isPanning = false, prevMouse = { x:0, y:0 };

// Suppress context menu on right-click
canvas.addEventListener('contextmenu', e => e.preventDefault());

canvas.addEventListener('mousedown', e => {
  isDragging = true;
  isPanning = (e.button === 2);
  prevMouse = { x:e.clientX, y:e.clientY };
  canvas.style.cursor = isPanning ? 'move' : 'grabbing';
});
window.addEventListener('mouseup', () => { isDragging = false; isPanning = false; canvas.style.cursor = 'grab'; });
window.addEventListener('mousemove', e => {
  if (!isDragging) return;
  const dx = e.clientX - prevMouse.x;
  const dy = e.clientY - prevMouse.y;
  if (isPanning) {
    // Right-drag: pan horizontal (left/right + forward/backward in XZ plane, Y unchanged)
    const panSpeed = radius * 0.002;
    const rightX = Math.cos(theta);
    const rightZ = -Math.sin(theta);
    const fwdX = -Math.sin(theta);
    const fwdZ = -Math.cos(theta);
    cameraTarget.x -= rightX * dx * panSpeed;
    cameraTarget.z -= rightZ * dx * panSpeed;
    cameraTarget.x += fwdX * dy * panSpeed;
    cameraTarget.z += fwdZ * dy * panSpeed;
    cameraMoveTarget = null; // cancel any pending animation
  } else {
    // Left-drag: orbit
    theta -= dx * 0.005;
    phi = Math.max(0.2, Math.min(Math.PI - 0.2, phi + dy * 0.005));
  }
  prevMouse = { x:e.clientX, y:e.clientY };
  updateCamera();
});
canvas.addEventListener('wheel', e => {
  e.preventDefault();
  const maxR = viewMode==='galactic'?200:120, minR = viewMode==='galactic'?20:8;
  radius = Math.max(minR, Math.min(maxR, radius + e.deltaY * 0.08));
  cameraRadiusTarget = null; // cancel smooth zoom
  updateCamera();
}, { passive: false });

document.getElementById('zoom-in').addEventListener('click', () => { radius = Math.max(8, radius-8); updateCamera(); });
document.getElementById('zoom-out').addEventListener('click', () => { radius = Math.min(200, radius+8); updateCamera(); });
document.getElementById('reset-view').addEventListener('click', () => setCameraDefault(viewMode==='galactic'?80:45));

let gridVisible = true;
const toggleGridBtn = document.getElementById('toggle-grid');
toggleGridBtn.addEventListener('click', () => { gridVisible = !gridVisible; gridGroup.visible = gridVisible; toggleGridBtn.classList.toggle('active', gridVisible); });

const toggleOrbitsBtn = document.getElementById('toggle-orbits');
toggleOrbitsBtn.addEventListener('click', () => { orbitsVisible = !orbitsVisible; orbitsGroup.visible = orbitsVisible; toggleOrbitsBtn.classList.toggle('active', orbitsVisible); });

// ============================================================
// HOVER & CLICK
// ============================================================
const raycaster = new THREE.Raycaster();
const mouse = new THREE.Vector2();
const tooltip = document.getElementById('tooltip');

canvas.addEventListener('mousemove', e => {
  if (isDragging) { tooltip.classList.remove('show'); return; }
  const rect = canvas.getBoundingClientRect();
  mouse.x = ((e.clientX-rect.left)/rect.width)*2-1;
  mouse.y = -((e.clientY-rect.top)/rect.height)*2+1;
  raycaster.setFromCamera(mouse, camera);
  const hits = raycaster.intersectObjects(pickables.map(p => p.core));
  if (hits.length > 0) {
    const hit = pickables.find(p => p.core === hits[0].object);
    if (hit) {
      const sys = hit.data;
      tooltip.classList.add('show');
      tooltip.style.left = Math.min(e.clientX-rect.left+14, rect.width-200)+'px';
      tooltip.style.top  = Math.min(e.clientY-rect.top+14,  rect.height-120)+'px';
      document.getElementById('tt-name').textContent = sys.name;
      document.getElementById('tt-dist').textContent = sys.dist > 0 ? sys.dist.toFixed(2)+(sys.isJumpTarget?' AL':' AU') : 'Système actuel';
      const el = document.getElementById('tt-cost');
      if (sys.isJumpTarget) {
        el.textContent = (sys.energieRequise||'?') + ' E · ' + (sys.paRequis||'?') + ' PA';
        el.className = 'cost ' + (sys.accessible ? 'ok' : 'ko');
      } else if (sys.distanceUA) {
        el.textContent = sys.distanceUA + ' UA'; el.className = 'cost ok';
      } else {
        el.textContent = '—'; el.className = 'cost';
      }
      document.getElementById('tt-type').textContent = sys.isJumpTarget ? 'Saut hyperespace' : (sys.poiType || sys.type || '—');
      canvas.style.cursor = 'pointer';
    }
  } else {
    tooltip.classList.remove('show');
    canvas.style.cursor = isDragging ? 'grabbing' : 'grab';
  }
});

// Shared raycast helper
function raycastHit(e) {
  const rect = canvas.getBoundingClientRect();
  mouse.x = ((e.clientX-rect.left)/rect.width)*2-1;
  mouse.y = -((e.clientY-rect.top)/rect.height)*2+1;
  raycaster.setFromCamera(mouse, camera);
  const hits = raycaster.intersectObjects(pickables.map(p => p.core));
  if (!hits.length) return null;
  return pickables.find(p => p.core === hits[0].object) || null;
}

function selectPoI(hit) {
  if (!hit || hit.data.id === 0) return;
  if (hit.data.isJumpTarget) window.lockJumpTarget(hit.data);
  else window.lockLocalTarget(hit.data);
  setDestinationLine(hit.data);
  consoleLog('> cible ' + hit.data.name, 'cmd');
  consoleLog('  Cible verrouillée : ' + hit.data.name, 'data');
}

// Single click — delayed to let dblclick cancel it
let _clickTimer = null;
canvas.addEventListener('click', e => {
  clearTimeout(_clickTimer);
  if (isPanning) return; // ignore click ending a right-drag
  _clickTimer = setTimeout(() => { selectPoI(raycastHit(e)); }, 220);
});

// Double click
canvas.addEventListener('dblclick', e => {
  clearTimeout(_clickTimer);
  const hit = raycastHit(e);
  if (!hit) return;
  const data = hit.data;
  if (data.id === 0) {
    // Central star / current system
    if (viewMode === 'galactic') {
      window.setViewMode('system'); // switch to system view
    } else {
      // In system view: re-centre if panned away, else go back to galactic
      if (cameraTarget.length() > 2.0) {
        animateCameraTo(new THREE.Vector3(0, 0, 0), null);
      } else {
        window.setViewMode('galactic');
      }
    }
  } else {
    // Regular PoI: select + zoom + centre
    selectPoI(hit);
    const zoomTarget = viewMode === 'galactic' ? 14 : 7;
    animateCameraTo(hit.group.position, Math.min(radius, zoomTarget));
  }
});

// ============================================================
// RESIZE
// ============================================================
let resizeRaf;
function onResize() {
  cancelAnimationFrame(resizeRaf);
  resizeRaf = requestAnimationFrame(() => {
    camera.aspect = wrap.clientWidth / wrap.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(wrap.clientWidth, wrap.clientHeight);
  });
}
window.addEventListener('resize', onResize);
new ResizeObserver(onResize).observe(wrap);

// ============================================================
// ANIMATE
// ============================================================
let shipMoveTo = null;
let shipMoveOnDone = null;
let shipTargetYaw = 0;

function moveShipTo(x, y, z, onDone) {
  shipMoveTo = new THREE.Vector3(x, y, z);
  shipMoveOnDone = onDone || null;
  // Point ship toward destination immediately
  const dx = x - shipGroup.position.x;
  const dz = z - shipGroup.position.z;
  if (Math.abs(dx) > 0.01 || Math.abs(dz) > 0.01) {
    shipTargetYaw = Math.atan2(dx, dz);
  }
}
window._moveShipTo  = moveShipTo;
window._shipGroup   = shipGroup;
window._setShipFaceTarget = function(tx, tz) {
  const dx = tx - shipGroup.position.x;
  const dz = tz - shipGroup.position.z;
  if (Math.abs(dx) > 0.01 || Math.abs(dz) > 0.01) {
    shipTargetYaw = Math.atan2(dx, dz);
  }
};

function animate(t) {
  requestAnimationFrame(animate);
  let needCameraUpdate = false;

  // Smooth camera target pan (double-click centering)
  if (cameraMoveTarget) {
    cameraTarget.lerp(cameraMoveTarget, 0.1);
    if (cameraTarget.distanceTo(cameraMoveTarget) < 0.05) {
      cameraTarget.copy(cameraMoveTarget);
      cameraMoveTarget = null;
    }
    needCameraUpdate = true;
  }
  // Smooth zoom (double-click zoom-in)
  if (cameraRadiusTarget !== null) {
    radius += (cameraRadiusTarget - radius) * 0.1;
    if (Math.abs(radius - cameraRadiusTarget) < 0.05) {
      radius = cameraRadiusTarget;
      cameraRadiusTarget = null;
    }
    needCameraUpdate = true;
  }
  if (needCameraUpdate) updateCamera();

  // Smooth ship rotation toward target
  let dyaw = ((shipTargetYaw - shipGroup.rotation.y) % (Math.PI * 2));
  if (dyaw > Math.PI) dyaw -= Math.PI * 2;
  if (dyaw < -Math.PI) dyaw += Math.PI * 2;
  shipGroup.rotation.y += dyaw * 0.06;

  if (scanRing) scanRing.rotation.z = t * 0.0002;
  if (shipMoveTo) {
    shipGroup.position.lerp(shipMoveTo, 0.05);
    if (shipGroup.position.distanceTo(shipMoveTo) < 0.12) {
      shipGroup.position.copy(shipMoveTo);
      const cb = shipMoveOnDone;
      shipMoveTo = null; shipMoveOnDone = null;
      if (cb) cb();
    }
  }
  renderer.render(scene, camera);
}

// ============================================================
// VIEW MODE + AXIS (exposed globally)
// ============================================================
// Données PHP pour les instruments dynamiques
const _instGalPos   = '{{ $objetSpatial->secteur_x }},{{ $objetSpatial->secteur_y }},{{ $objetSpatial->secteur_z }}';
const _instGalCtxLbl = 'Système';
const _instGalCtxVal = @json($systemeName);
const _instSysPos   = '{{ $shipLocalXUA }}, {{ $shipLocalYUA }} UA';
const _instSysCtxLbl = '∆ étoile';
const _instSysCtxVal = '{{ $shipLocalDistUA }} UA';

function updateInstruments(mode) {
  if (mode === 'galactic') {
    document.getElementById('inst-pos-label').textContent = 'Secteur';
    document.getElementById('inst-pos-val').textContent   = _instGalPos;
    document.getElementById('inst-ctx-label').textContent = _instGalCtxLbl;
    document.getElementById('inst-ctx-val').textContent   = _instGalCtxVal;
  } else {
    document.getElementById('inst-pos-label').textContent = 'Pos. locale';
    document.getElementById('inst-pos-val').textContent   = _instSysPos;
    document.getElementById('inst-ctx-label').textContent = _instSysCtxLbl;
    document.getElementById('inst-ctx-val').textContent   = _instSysCtxVal;
  }
}

window.setViewMode = function(mode) {
  if (mode === viewMode) return;
  viewMode = mode;
  document.getElementById('view-btn-galactic').classList.toggle('active', mode==='galactic');
  document.getElementById('view-btn-system').classList.toggle('active', mode==='system');
  updateInstruments(mode);
  // Sync scanner tabs
  if (mode === 'galactic') window.switchScanTab('galact');
  else window.switchScanTab('local');
  buildScene();
  setDestinationLine(null);
};

// Initialiser les instruments selon le mode URL au chargement
updateInstruments(viewMode);

window.setVerticalAxis = function(axis) {
  verticalAxis = axis;
  document.querySelectorAll('.axis-btn').forEach(b => b.classList.toggle('active', b.dataset.axis === axis));
  buildScene();
};

// START
buildScene();
animate(0);
</script>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
const consoleOut = document.getElementById('console-out');
const cmdInput = document.getElementById('cmd-input');
let lockedTargetId = null;
let lockedTargetType = null; // 'jump' | 'local'

// ============ CONSOLE ============
const CONSOLE_MAX = 50;
function consoleTrim() {
  while (consoleOut.children.length > CONSOLE_MAX) consoleOut.removeChild(consoleOut.firstChild);
}
function consoleLog(text, type = 'sys') {
  if (!consoleOut) return;
  const div = document.createElement('div');
  div.className = 'console-line ' + type;
  div.textContent = text;
  consoleOut.appendChild(div);
  consoleTrim();
  consoleOut.scrollTop = consoleOut.scrollHeight;
}

// Parsing des marqueurs [W]/[T]/[C]/[E] identiques à console-hud.blade.php
function consoleLogWithMarkers(text, defaultType = 'sys') {
  if (!consoleOut) return;
  const div = document.createElement('div');
  div.className = 'console-line ' + defaultType;
  const rx = /\[W\](.*?)\[\/W\]|\[T\](.*?)\[\/T\]|\[C\](.*?)\[\/C\]|\[E\](.*?)\[\/E\]/g;
  let last = 0, m;
  while ((m = rx.exec(text)) !== null) {
    if (m.index > last) div.appendChild(document.createTextNode(text.substring(last, m.index)));
    const s = document.createElement('span');
    if      (m[1] !== undefined) { s.style.color = 'var(--text-primary)'; s.style.fontWeight = '700'; s.textContent = m[1]; }
    else if (m[2] !== undefined) { s.style.color = 'var(--data)';         s.style.fontWeight = '600'; s.textContent = m[2]; }
    else if (m[3] !== undefined) { s.style.color = 'var(--warning)';                                  s.textContent = m[3]; }
    else                         { s.style.color = 'var(--danger)';                                   s.textContent = m[4]; }
    div.appendChild(s);
    last = rx.lastIndex;
  }
  if (last < text.length) div.appendChild(document.createTextNode(text.substring(last)));
  consoleOut.appendChild(div);
  consoleTrim();
  consoleOut.scrollTop = consoleOut.scrollHeight;
}

function appendToConsole(text, cssClass) {
  const map = { 'text-green-400':'ok','text-cyan-400':'data','text-red-400':'err','text-yellow-400':'warn','text-gray-300':'sys','text-gray-500':'dim' };
  consoleLog(text, map[cssClass] || 'sys');
}

const CMD_HISTORY_MAX = 20;
window._timCmdHistory = [];
const cmdHistory = window._timCmdHistory;
let cmdHistoryPos = -1;
let cmdHistoryDraft = '';

// Restore console history and command history after a page reload (e.g. after jump)
(function() {
  const saved = sessionStorage.getItem('timConsole');
  if (saved && consoleOut) {
    try {
      const lines = JSON.parse(saved);
      const frag = document.createDocumentFragment();
      lines.forEach(({ t, c }) => {
        const d = document.createElement('div');
        d.className = c; d.textContent = t;
        frag.appendChild(d);
      });
      consoleOut.insertBefore(frag, consoleOut.firstChild);
      while (consoleOut.children.length > CONSOLE_MAX) consoleOut.removeChild(consoleOut.firstChild);
      consoleOut.scrollTop = consoleOut.scrollHeight;
    } catch(e) {}
    sessionStorage.removeItem('timConsole');
  }
  const savedH = sessionStorage.getItem('timCmdHistory');
  if (savedH) {
    try {
      const h = JSON.parse(savedH);
      h.forEach(c => cmdHistory.push(c));
      if (cmdHistory.length > CMD_HISTORY_MAX) cmdHistory.splice(CMD_HISTORY_MAX);
    } catch(e) {}
    sessionStorage.removeItem('timCmdHistory');
  }
})();

cmdInput?.addEventListener('keydown', e => {
  if (e.key === 'Enter') {
    const cmd = cmdInput.value.trim();
    if (!cmd) return;
    cmdInput.value = '';
    if (cmdHistory[0] !== cmd) {
      cmdHistory.unshift(cmd);
      if (cmdHistory.length > CMD_HISTORY_MAX) cmdHistory.pop();
    }
    cmdHistoryPos = -1;
    cmdHistoryDraft = '';
    sendCommand(cmd);
  } else if (e.key === 'ArrowUp') {
    e.preventDefault();
    if (cmdHistory.length === 0) return;
    if (cmdHistoryPos === -1) cmdHistoryDraft = cmdInput.value;
    cmdHistoryPos = Math.min(cmdHistoryPos + 1, cmdHistory.length - 1);
    cmdInput.value = cmdHistory[cmdHistoryPos];
    cmdInput.setSelectionRange(cmdInput.value.length, cmdInput.value.length);
  } else if (e.key === 'ArrowDown') {
    e.preventDefault();
    if (cmdHistoryPos <= 0) { cmdHistoryPos = -1; cmdInput.value = cmdHistoryDraft; return; }
    cmdHistoryPos--;
    cmdInput.value = cmdHistory[cmdHistoryPos];
    cmdInput.setSelectionRange(cmdInput.value.length, cmdInput.value.length);
  }
});

function sendCommand(cmd) {
  consoleLog('> ' + cmd, 'cmd');
  fetch('{{ route("command") }}', {
    method: 'POST',
    headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json' },
    body: JSON.stringify({ command: cmd })
  })
  .then(r => {
    if (r.status === 419) { consoleLog('[SESSION EXPIRÉE] Rafraîchissez la page (F5)', 'warn'); return null; }
    return r.json();
  })
  .then(data => {
    if (!data) return;
    if (data.message) {
      data.message.split('\n').forEach(line => { if (line.trim()) consoleLogWithMarkers(line, data.success ? '' : 'err'); });
    }
    if (data.energie_actuelle !== undefined) updateGaugeEnergy(data.energie_actuelle);
    if (data.pa_restants !== undefined) updateGaugePA(data.pa_restants);
  })
  .catch(err => consoleLog('[ERREUR] ' + err.message, 'err'));
}

function updateGaugeEnergy(val) {
  document.getElementById('inst-energie').textContent = val + '/{{ $enMax }}';
}
function updateGaugePA(val) {
  document.getElementById('inst-pa').textContent = val + '/{{ $paMax }}';
  document.getElementById('hud-pa').textContent = val + '/{{ $paMax }}';
}

// ============ SCANNER TAB ============
window.switchScanTab = function(tab) {
  const isLocal = tab === 'local';
  document.getElementById('scan-tab-local').classList.toggle('active', isLocal);
  document.getElementById('scan-tab-galact').classList.toggle('active', !isLocal);
  document.getElementById('scan-list-local').style.display = isLocal ? '' : 'none';
  document.getElementById('scan-list-galact').style.display = isLocal ? 'none' : '';
};

// ============ DESTINATION OVERLAY ============
function showDestOverlay(show) {
  document.getElementById('dest-overlay').style.display = show ? '' : 'none';
}

window.lockJumpTarget = function(sys) {
  lockedTargetId = sys.id;
  lockedTargetType = 'jump';
  document.getElementById('dest-head').textContent = 'Destination verrouillée';
  document.getElementById('dest-name').textContent = sys.name;
  document.getElementById('dest-name').className = 'dest-name';
  const coordsEl = document.getElementById('dest-coords');
  if (sys.sx !== undefined) {
    coordsEl.textContent = 'x: ' + sys.sx + ', y: ' + sys.sy + ', z: ' + sys.sz + ' AL';
    coordsEl.style.display = '';
  } else {
    coordsEl.style.display = 'none';
  }
  document.getElementById('dest-meta').textContent = (sys.dist||0).toFixed(2) + ' AL · ' + (sys.energieRequise||'?') + ' E · ' + (sys.paRequis||'?') + ' PA';
  document.getElementById('dest-cost-row').style.display = 'flex';
  document.getElementById('dest-cost').textContent = (sys.energieRequise||'?') + ' E · ' + (sys.paRequis||'?') + ' PA';

  const btnA = document.getElementById('btn-annuler');
  btnA.disabled = false; btnA.className = 'btn'; btnA.textContent = 'Annuler';
  btnA.onclick = clearTarget;

  const btnAct = document.getElementById('btn-action');
  btnAct.disabled = !sys.accessible;
  btnAct.className = 'btn primary';
  btnAct.textContent = 'Calculer saut';
  btnAct.onclick = () => calculerSaut(sys.id);

  showDestOverlay(true);
};

window.lockLocalTarget = function(sys) {
  lockedTargetId = sys.id;
  lockedTargetType = 'local';
  document.getElementById('dest-head').textContent = 'Cible locale';
  document.getElementById('dest-name').textContent = sys.name;
  document.getElementById('dest-name').className = 'dest-name';
  document.getElementById('dest-meta').textContent = sys.distanceUA ? sys.distanceUA + ' UA' : 'Position locale';
  document.getElementById('dest-cost-row').style.display = 'none';

  const btnA = document.getElementById('btn-annuler');
  btnA.disabled = false; btnA.className = 'btn'; btnA.textContent = 'Annuler';
  btnA.onclick = clearTarget;

  const btnAct = document.getElementById('btn-action');
  btnAct.disabled = false;
  btnAct.className = 'btn primary';
  btnAct.textContent = 'S\'approcher';
  btnAct.onclick = () => sApprocher(sys.id, sys.poiType);

  // Orient ship toward target
  if (window._setShipFaceTarget) window._setShipFaceTarget(sys.x || 0, sys.z || 0);

  showDestOverlay(true);
};

function clearTarget() {
  lockedTargetId = null; lockedTargetType = null;
  document.getElementById('dest-head').textContent = 'Destination';
  document.getElementById('dest-name').textContent = 'Aucune cible';
  document.getElementById('dest-name').className = 'dest-name empty';
  document.getElementById('dest-coords').style.display = 'none';
  document.getElementById('dest-meta').textContent = 'Cliquez sur la carte ou le scanner';
  document.getElementById('dest-cost-row').style.display = 'none';
  document.getElementById('btn-annuler').disabled = true;
  document.getElementById('btn-action').disabled = true;
  document.getElementById('btn-action').textContent = 'Sélectionner cible';
  document.querySelectorAll('.scan-item.active').forEach(i => i.classList.remove('active'));
  showDestOverlay(false);
}

function selectScanItem(el, type, id, name, dist, isJump, accessible = true, energieRequise = 0, paRequis = 0) {
  document.querySelectorAll('.scan-item.active').forEach(i => i.classList.remove('active'));
  el.classList.add('active');
  const sysData = { id, name, type, distanceUA: dist, poiType: type, isJumpTarget: isJump, dist: isJump ? dist : dist/63241, accessible, energieRequise, paRequis };
  if (isJump) {
    window.lockJumpTarget(sysData);
  } else if (id > 0) {
    window.lockLocalTarget(sysData);
  }
  consoleLog('> cible ' + name, 'cmd');
  consoleLog('  Cible : ' + name, 'data');
}

// ============ NAVIGATION ============
async function calculerSaut(destinationId, poiId = 'systeme') {
  consoleLog('> calculer-saut ' + destinationId, 'cmd');
  try {
    const r = await fetch('{{ route("navire.timonerie.calculer-saut") }}', {
      method: 'POST',
      headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken },
      body: JSON.stringify({ destination_id: destinationId, poi_id: poiId })
    });
    const data = await r.json();
    if (data.error) { consoleLog('[ERREUR] ' + data.error, 'err'); return; }
    consoleLog('  Calcul vers ' + data.destination, 'data');
    consoleLog('  Distance : ' + data.distance + ' AL · Énergie : ' + data.energieRequise + ' · PA : ' + data.paRequis, 'sys');
    if (!data.accessible) {
      consoleLog('  ⚠ Ressources insuffisantes', 'warn');
    } else {
      consoleLog('  ✓ Saut calculé — prêt à initier', 'ok');
      const btnAct = document.getElementById('btn-action');
      btnAct.textContent = '▶ Initier saut';
      btnAct.onclick = () => effectuerSaut(destinationId, poiId);
      btnAct.disabled = false;
    }
    reloadWithMode();
  } catch (e) {
    consoleLog('[ERREUR] ' + e.message, 'err');
  }
}

async function effectuerSaut(destinationId, poiId = 'systeme') {
  consoleLog('> saut-effectuer ' + destinationId, 'cmd');
  try {
    const r = await fetch('{{ route("navire.timonerie.effectuer-saut") }}', {
      method: 'POST',
      headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken },
      body: JSON.stringify({ destination_id: destinationId, poi_id: poiId })
    });
    const data = await r.json();
    if (data.error) { consoleLog('[ERREUR] ' + data.error, 'err'); return; }
    consoleLog('  ✓ ' + data.message, 'ok');
    consoleLog('  Énergie restante : ' + data.energieRestante + ' · PA : ' + data.paRestants, 'sys');
    updateGaugeEnergy(data.energieRestante);
    updateGaugePA(data.paRestants);
    // Animer : vaisseau s'éloigne rapidement (simulation saut hyperspacial)
    const mst = window._moveShipTo;
    if (mst && window._shipGroup) {
      const sx = window._shipGroup.position.x, sz = window._shipGroup.position.z;
      const angle = Math.atan2(sz, sx) + Math.PI;
      mst(sx + Math.cos(angle)*40, 1, sz + Math.sin(angle)*40, reloadWithMode);
    } else {
      setTimeout(reloadWithMode, 1200);
    }
  } catch (e) {
    consoleLog('[ERREUR] ' + e.message, 'err');
  }
}

async function sApprocher(poiId, poiType) {
  consoleLog('> approcher ' + poiId, 'cmd');
  try {
    const r = await fetch('{{ route("navire.timonerie.s-approcher") }}', {
      method: 'POST',
      headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken },
      body: JSON.stringify({ poi_id: poiId, poi_type: poiType })
    });
    if (r.status === 419) { consoleLog('[SESSION EXPIRÉE] Rafraîchissez la page (F5)', 'warn'); return; }
    const data = await r.json();
    if (data.error) { consoleLog('[ERREUR] ' + data.error, 'err'); return; }
    consoleLog('  ✓ ' + data.message, 'ok');
    updateGaugeEnergy(data.energieRestante);
    updateGaugePA(data.paRestants);

    // Vrai si le vaisseau est maintenant à portée d'action
    const arrivedNear = data.peutAmarrer === true || (typeof data.distanceRestante === 'number' && data.distanceRestante < 0.01);

    const ld = window._localData || [];
    const target = ld.find(p => p.id == poiId);
    const mst = window._moveShipTo;
    if (target && mst) {
      // Stop near the object, not on top: offset along the outward radial direction
      const angle = Math.atan2(target.z || 0, target.x || 0);
      const margin = (target.size || 2) * 0.5 + 1.0;
      const destX = (target.x || 0) + Math.cos(angle) * margin;
      const destZ = (target.z || 0) + Math.sin(angle) * margin;
      const onDone = arrivedNear
        ? () => {
            const btnAct = document.getElementById('btn-action');
            if (poiType === 'station') {
              btnAct.textContent = 'S\'amarrer';
              btnAct.onclick = () => sAmarrer(poiId);
            } else {
              btnAct.textContent = 'Se satelliser';
              btnAct.onclick = () => sOrbiter(poiId);
            }
          }
        : () => setTimeout(reloadWithMode, 400);
      mst(destX, 1, destZ, onDone);
    } else {
      setTimeout(reloadWithMode, 1500);
    }
  } catch (e) {
    consoleLog('[ERREUR] ' + e.message, 'err');
  }
}

async function sOrbiter(planeteId) {
  consoleLog('> se-satelliser ' + planeteId, 'cmd');
  try {
    const r = await fetch('{{ route("navire.timonerie.s-orbiter") }}', {
      method: 'POST',
      headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken },
      body: JSON.stringify({ planete_id: planeteId })
    });
    if (r.status === 419) { consoleLog('[SESSION EXPIRÉE] Rafraîchissez la page (F5)', 'warn'); return; }
    const data = await r.json();
    if (data.error) { consoleLog('[ERREUR] ' + data.error, 'err'); return; }
    consoleLog('  ✓ ' + data.message, 'ok');
    updateGaugeEnergy(data.energieRestante);
    updateGaugePA(data.paRestants);
    // En orbite : proposer Atterrir ou Quitter orbite
    const btnAct = document.getElementById('btn-action');
    btnAct.textContent = 'Atterrir';
    btnAct.onclick = () => atterrir(planeteId);
    consoleLog('  ▸ Orbite stable — Atterrir disponible', 'data');
  } catch (e) {
    consoleLog('[ERREUR] ' + e.message, 'err');
  }
}

async function atterrir(planeteId) {
  consoleLog('> atterrir ' + planeteId, 'cmd');
  try {
    const r = await fetch('{{ route("navire.timonerie.atterrir") }}', {
      method: 'POST',
      headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken },
      body: JSON.stringify({ planete_id: planeteId })
    });
    if (r.status === 419) { consoleLog('[SESSION EXPIRÉE] Rafraîchissez la page (F5)', 'warn'); return; }
    const data = await r.json();
    if (data.error) { consoleLog('[ERREUR] ' + data.error, 'err'); return; }
    consoleLog('  ✓ ' + data.message, 'ok');
    updateGaugeEnergy(data.energieRestante);
    updateGaugePA(data.paRestants);
    setTimeout(reloadWithMode, 2000);
  } catch (e) {
    consoleLog('[ERREUR] ' + e.message, 'err');
  }
}

async function sAmarrer(stationId) {
  consoleLog('> s-amarrer ' + stationId, 'cmd');
  try {
    const r = await fetch('{{ route("navire.timonerie.s-amarrer") }}', {
      method: 'POST',
      headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken },
      body: JSON.stringify({ station_id: stationId })
    });
    if (r.status === 419) { consoleLog('[SESSION EXPIRÉE] Rafraîchissez la page (F5)', 'warn'); return; }
    const data = await r.json();
    if (data.error) { consoleLog('[ERREUR] ' + data.error, 'err'); return; }
    consoleLog('  ✓ ' + data.message, 'ok');
    consoleLog('  Accès station activé — redirection...', 'data');
    updateGaugeEnergy(data.energieRestante);
    updateGaugePA(data.paRestants);
    setTimeout(() => window.location.href = '{{ route("station.hall") }}', 2000);
  } catch (e) {
    consoleLog('[ERREUR] ' + e.message, 'err');
  }
}

// ============ CALCUL SAUT ============
async function annulerCalculSaut() {
  if (!confirm('Annuler ce calcul de saut ?')) return;
  const r = await fetch('{{ route("navire.timonerie.annuler-calcul") }}', {
    method: 'POST',
    headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken }
  });
  const data = await r.json();
  consoleLog(data.error ? '[ERREUR] ' + data.error : '  ✓ Calcul annulé', data.error ? 'err' : 'ok');
  if (!data.error) reloadWithMode();
}

async function ameliorerCalculSaut() {
  if (!confirm('Améliorer ce calcul coûte 1 PA. Continuer ?')) return;
  const r = await fetch('{{ route("navire.timonerie.ameliorer-calcul") }}', {
    method: 'POST',
    headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken }
  });
  const data = await r.json();
  if (data.error) { consoleLog('[ERREUR] ' + data.error, 'err'); return; }
  consoleLog('  ✓ Calcul amélioré · Score : ' + data.nouveau_score + ' · Précision : ' + data.precision + '%', 'ok');
  reloadWithMode();
}

// ============ RACCOURCIS ============
function handleShortcut(cmd) {
  switch (cmd) {
    case 'scan':
      consoleLog('> scan', 'cmd');
      consoleLog('  Scanner actif · {{ $scanCount }} objets détectés', 'data');
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

// ─── CONSOLE RESIZE ──────────────────────────────────────────────────
(function() {
  const stage  = document.querySelector('.stage');
  const handle = document.getElementById('tim-resize-handle');
  const savedW = parseInt(localStorage.getItem('timConsolW') || '320', 10);
  if (stage) stage.style.setProperty('--console-w', savedW + 'px');

  let resizing = false, rx0 = 0, rw0 = 0;
  if (handle && stage) {
    handle.addEventListener('mousedown', e => {
      const rc = document.querySelector('.right-col');
      resizing = true; rx0 = e.clientX; rw0 = rc ? rc.offsetWidth : 320;
      handle.classList.add('active');
      e.preventDefault();
      document.body.style.userSelect = 'none';
      document.body.style.cursor = 'ew-resize';
    });
  }
  document.addEventListener('mousemove', e => {
    if (!resizing || !stage) return;
    const nw = Math.max(220, Math.min(600, rw0 + (rx0 - e.clientX)));
    stage.style.setProperty('--console-w', nw + 'px');
  });
  document.addEventListener('mouseup', () => {
    if (!resizing) return;
    resizing = false;
    if (handle) handle.classList.remove('active');
    document.body.style.userSelect = '';
    document.body.style.cursor = '';
    if (stage) {
      const nw = parseInt(stage.style.getPropertyValue('--console-w'), 10);
      if (!isNaN(nw)) localStorage.setItem('timConsolW', nw);
    }
  });
})();
</script>

<script src="{{ asset('js/orbital-calculator.js') }}"></script>
@endsection
