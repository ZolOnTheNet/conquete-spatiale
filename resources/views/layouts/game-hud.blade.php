@extends('layouts.app')

@section('title', $pageTitle ?? 'Conquête Spatiale')

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
  min-width: 1100px;
}

/* ── HUD HEADER ─────────────────────────────────────────────── */
.hud-header {
  position: fixed; top: 0; left: 0; right: 0; height: 52px; min-width: 1100px;
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
.gauge-bar { width: 52px; height: 5px; background: rgba(125,165,200,0.1); border: 1px solid var(--border-subtle); position: relative; }
.gauge-fill { height: 100%; transition: width 0.3s; }
.gauge-fill.ok  { background: linear-gradient(90deg, var(--success), #86efac); box-shadow: 0 0 6px rgba(74,222,128,0.4); }
.gauge-fill.mid { background: linear-gradient(90deg, var(--warning), #fcd34d); box-shadow: 0 0 6px rgba(251,191,36,0.4); }
.gauge-fill.low { background: linear-gradient(90deg, var(--danger), #fca5a5); box-shadow: 0 0 6px rgba(239,68,68,0.5); }
.gauge-val { font-family: var(--mono); font-size: 10px; color: var(--text-secondary); min-width: 30px; }
.hud-spacer { flex: 1; }
.hud-btn {
  font-family: var(--mono); font-size: 10px; letter-spacing: 0.15em;
  color: var(--text-muted); text-transform: uppercase; background: none;
  border: 1px solid var(--border-subtle); padding: 4px 9px; cursor: pointer;
  transition: all 0.15s; text-decoration: none; display: inline-flex; align-items: center;
}
.hud-btn:hover { color: var(--text-primary); border-color: var(--border-strong); }

/* ── STAGE ───────────────────────────────────────────────────── */
.stage {
  position: fixed; top: 52px; left: 0; right: 0; bottom: 0;
  display: flex; min-width: 1100px;
}

/* ── LEFT NAV ────────────────────────────────────────────────── */
.nav {
  width: 200px; flex-shrink: 0;
  background: var(--bg-panel); border-right: 1px solid var(--border-subtle);
  display: flex; flex-direction: column; overflow: hidden; backdrop-filter: blur(6px);
}
.nav-scroll { flex: 1; overflow-y: auto; padding: 10px 0; }
.nav-section { margin-bottom: 12px; }
.nav-section-head {
  padding: 4px 14px; font-family: var(--mono); font-size: 9px; letter-spacing: 0.2em;
  color: var(--text-muted); text-transform: uppercase; display: flex; align-items: center; gap: 6px;
}
.nav-section-head::before { content: ''; width: 4px; height: 4px; background: var(--text-muted); border-radius: 50%; flex-shrink: 0; }
.nav-item {
  display: flex; justify-content: space-between; align-items: center;
  padding: 5px 14px; color: var(--text-secondary); font-size: 12px; cursor: pointer;
  transition: all 0.12s; border-left: 2px solid transparent; font-weight: 500;
  text-decoration: none;
}
.nav-item:hover { color: var(--text-primary); background: rgba(127,212,255,0.04); }
.nav-item.active { color: var(--accent); background: rgba(255,138,61,0.06); border-left-color: var(--accent); }
.nav-cmd { font-family: var(--mono); font-size: 9px; color: var(--text-muted); opacity: 0; transition: opacity 0.15s; }
.nav-item:hover .nav-cmd { opacity: 1; }
.nav-footer { border-top: 1px solid var(--border-subtle); padding: 10px 14px; flex-shrink: 0; }
.nav-footer-name { font-size: 12px; color: var(--text-secondary); font-weight: 600; margin-bottom: 2px; }
.nav-footer-meta { font-family: var(--mono); font-size: 9px; color: var(--text-muted); letter-spacing: 0.1em; margin-bottom: 6px; }
.nav-footer-link { font-family: var(--mono); font-size: 9px; color: var(--data); letter-spacing: 0.1em; text-decoration: none; }
.nav-footer-link:hover { color: var(--text-primary); }

/* ── CENTER ──────────────────────────────────────────────────── */
.hud-main { flex: 1; overflow-y: auto; background: var(--bg-void); min-width: 0; padding: 24px 28px; }

/* ── RESIZE HANDLE ───────────────────────────────────────────── */
.hud-resize-handle {
  width: 6px; flex-shrink: 0; cursor: ew-resize;
  background: var(--border-subtle);
  transition: background 0.15s;
  position: relative;
}
.hud-resize-handle::after {
  content: '';
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  width: 2px; height: 40px;
  background: var(--border-strong);
  border-radius: 1px;
}
.hud-resize-handle:hover, .hud-resize-handle.active {
  background: rgba(127,212,255,0.12);
}
.hud-resize-handle:hover::after, .hud-resize-handle.active::after {
  background: var(--data);
}

/* ── RIGHT COL (console) ─────────────────────────────────────── */
.right-col {
  width: 320px; flex-shrink: 0;
  min-width: 220px; max-width: 600px;
  display: flex; flex-direction: column;
  background: var(--bg-panel); border-left: 1px solid var(--border-subtle);
  backdrop-filter: blur(6px); overflow: hidden;
}
.console { flex: 1; display: flex; flex-direction: column; min-height: 0; }
.console-head {
  padding: 8px 12px; font-family: var(--mono); font-size: 9px; letter-spacing: 0.25em;
  color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border-subtle);
  display: flex; justify-content: space-between; align-items: center;
  background: rgba(5,7,12,0.5); flex-shrink: 0;
}
.console-head .con-status { color: var(--success); display: flex; gap: 5px; align-items: center; }
.console-head .con-status::before { content: ''; width: 5px; height: 5px; background: var(--success); border-radius: 50%; animation: hudpulse 2s infinite; }
.console-head .clear-btn { font-family: var(--mono); font-size: 9px; color: var(--text-muted); background: none; border: none; cursor: pointer; letter-spacing: 0.1em; }
.console-head .clear-btn:hover { color: var(--text-primary); }
.console-out {
  flex: 1; padding: 10px 12px; overflow-y: auto; font-family: var(--mono);
  font-size: 11.5px; line-height: 1.55; min-height: 0; color: var(--text-secondary);
}
.console-line { margin: 0; white-space: pre-wrap; word-break: break-word; }
.console-line.cmd  { color: var(--accent); font-weight: 600; }
.console-line.sys  { color: var(--text-muted); }
.console-line.ok   { color: var(--success); }
.console-line.data { color: var(--data); }
.console-line.warn { color: var(--warning); }
.console-line.err  { color: var(--danger); }
.console-line.dim  { color: var(--text-muted); font-style: italic; font-size: 10.5px; }
.console-input {
  border-top: 1px solid var(--border-subtle);
  background: rgba(5,7,12,0.65); flex-shrink: 0;
}
.console-prompt-label {
  padding: 5px 12px 3px;
  font-family: var(--mono); font-size: 10px; color: var(--accent);
  letter-spacing: 0.05em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.console-input input {
  display: block; width: 100%; box-sizing: border-box;
  background: transparent; border: none; border-top: 1px solid var(--border-subtle);
  outline: none; color: var(--text-primary); font-family: var(--mono); font-size: 12px;
  caret-color: var(--accent); padding: 6px 12px; transition: background 0.15s;
}
.console-input input:focus { background: rgba(127,212,255,0.04); }
.shortcuts {
  padding: 6px 8px; border-top: 1px solid var(--border-subtle);
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 3px;
  background: rgba(5,7,12,0.3); flex-shrink: 0;
}
.sc {
  font-family: var(--mono); font-size: 9px; letter-spacing: 0.05em; padding: 5px 2px;
  text-transform: uppercase; cursor: pointer; background: transparent;
  color: var(--text-secondary); border: 1px solid var(--border-subtle);
  transition: all 0.12s; text-align: center;
}
.sc:hover { color: var(--data); border-color: var(--data); background: rgba(127,212,255,0.05); }

/* ── Scrollbar ───────────────────────────────────────────────── */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border-subtle); }
::-webkit-scrollbar-thumb:hover { background: var(--border-strong); }

/* ── Animation ───────────────────────────────────────────────── */
@keyframes hudpulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

/* ── Helpers pour le contenu des pages ───────────────────────── */
.hud-page-title {
  font-family: var(--sans); font-size: 20px; font-weight: 700;
  color: var(--data); letter-spacing: 0.1em; text-transform: uppercase;
  margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
}
.hud-panel {
  background: var(--bg-panel); border: 1px solid var(--border-subtle);
  padding: 20px; margin-bottom: 16px;
}
.hud-panel-title {
  font-family: var(--mono); font-size: 9px; letter-spacing: 0.2em;
  color: var(--text-muted); text-transform: uppercase; margin-bottom: 12px;
  padding-bottom: 8px; border-bottom: 1px solid var(--border-subtle);
}
</style>
@stack('hud-styles')
@endpush

@php
use App\Helpers\GameTimeHelper;
$_p   = $personnage  ?? null;
$_v   = $vaisseau    ?? null;
$_os  = $_v ? $_v->objetSpatial : null;
$_sys = $systemeActuel ?? null;

$_paMax   = $_p ? ($_p->pa_max ?? 50) : 50;
$_paVal   = $_p ? ($_p->points_action ?? 0) : 0;
$_paPct   = $_paMax > 0 ? round($_paVal / $_paMax * 100) : 0;
$_paCls   = $_paPct > 60 ? 'ok' : ($_paPct > 30 ? 'mid' : 'low');

$_enVal   = $_v ? ($_v->energie_actuelle ?? 0) : 0;
$_enMax   = $_v ? ($_v->reserve ?? 100) : 100;
$_enPct   = $_enMax > 0 ? round($_enVal / $_enMax * 100) : 0;
$_enCls   = $_enPct > 60 ? 'ok' : ($_enPct > 30 ? 'mid' : 'low');

$_coqVal  = $_v ? ($_v->coque_actuelle ?? 100) : 100;
$_coqMax  = $_v ? ($_v->coque_max ?? 100) : 100;
$_coqPct  = $_coqMax > 0 ? round($_coqVal / $_coqMax * 100) : 100;
$_coqCls  = $_coqPct > 60 ? 'ok' : ($_coqPct > 30 ? 'mid' : 'low');

$_bouVal  = $_v ? (int)($_v->bouclier_actuel ?? 0) : 0;
$_bouCls  = $_bouVal > 60 ? 'ok' : ($_bouVal > 30 ? 'mid' : 'low');
$_hasBou  = $_v && $_v->bouclier_id;

$_cgVal   = $_v ? ($_v->cargaison_actuelle ?? $_v->cargo_actuel ?? 0) : 0;
$_cgMax   = $_v ? ($_v->cargaison_max ?? $_v->cargo_max ?? 20) : 20;
$_cgPct   = $_cgMax > 0 ? round($_cgVal / $_cgMax * 100) : 0;
$_cgCls   = $_cgPct < 80 ? 'ok' : ($_cgPct < 95 ? 'mid' : 'low');

$_credits = $_p ? ($_p->credits ?? 0) : 0;
$_cmd     = $_p ? strtoupper(trim(($_p->prenom ?? '') . ' ' . ($_p->nom ?? 'CMDR'))) : 'CMDR';

$_sysNom  = $_sys ? strtoupper($_sys->nom) : 'ESPACE PROFOND';
$_pos     = $_os ? "({$_os->secteur_x}, {$_os->secteur_y}, {$_os->secteur_z}) · {$_sysNom}" : $_sysNom;

$_date    = $_p ? GameTimeHelper::getDateActuelleJeu($_p) : null;
$_dateStr = $_date ? ($_date->format('Y') . '.' . str_pad($_date->dayOfYear, 3, '0', STR_PAD_LEFT)) : '—';

$_compte  = auth()->user();
@endphp

@section('content')

{{-- ── HUD HEADER ─────────────────────────────────────────────── --}}
<header class="hud-header">
  <div class="hud-brand">CONQUÊTE SPATIALE</div>
  <div class="hud-sep"></div>
  <div class="hud-block">
    <span class="hud-label">CMDR</span>
    <span class="hud-value">{{ $_cmd }}</span>
  </div>
  <div class="hud-block">
    <span class="hud-label">Position</span>
    <span class="hud-value data">{{ $_pos }}</span>
  </div>
  <div class="hud-block">
    <span class="hud-label">Date galact.</span>
    <span class="hud-value">{{ $_dateStr }}</span>
  </div>
  <div class="hud-sep"></div>
  <div class="gauge-group">
    <div class="gauge" title="Points d'action : {{ $_paVal }}/{{ $_paMax }}">
      <span class="gauge-icon">PA</span>
      <div class="gauge-bar"><div class="gauge-fill {{ $_paCls }}" style="width:{{ $_paPct }}%" id="hud-pa-bar"></div></div>
      <span class="gauge-val" id="hud-pa">{{ $_paVal }}/{{ $_paMax }}</span>
    </div>
    <div class="gauge" title="Énergie : {{ $_enVal }}/{{ $_enMax }}">
      <span class="gauge-icon">⚡</span>
      <div class="gauge-bar"><div class="gauge-fill {{ $_enCls }}" style="width:{{ $_enPct }}%" id="hud-en-bar"></div></div>
      <span class="gauge-val" id="hud-en">{{ $_enPct }}%</span>
    </div>
    <div class="gauge" title="Coque : {{ $_coqVal }}/{{ $_coqMax }}">
      <span class="gauge-icon">◈</span>
      <div class="gauge-bar"><div class="gauge-fill {{ $_coqCls }}" style="width:{{ $_coqPct }}%" id="hud-coq-bar"></div></div>
      <span class="gauge-val" id="hud-coq">{{ $_coqPct }}%</span>
    </div>
    @if($_hasBou)
    <div class="gauge" title="Bouclier : {{ $_bouVal }}/100">
      <span class="gauge-icon">◎</span>
      <div class="gauge-bar"><div class="gauge-fill {{ $_bouCls }}" style="width:{{ $_bouVal }}%" id="hud-bou-bar"></div></div>
      <span class="gauge-val" id="hud-bou">{{ $_bouVal }}%</span>
    </div>
    @endif
    <div class="gauge" title="Cargaison : {{ $_cgVal }}/{{ $_cgMax }}t">
      <span class="gauge-icon">▣</span>
      <div class="gauge-bar"><div class="gauge-fill {{ $_cgCls }}" style="width:{{ $_cgPct }}%" id="hud-cargo-bar"></div></div>
      <span class="gauge-val" id="hud-cargo">{{ $_cgVal }}/{{ $_cgMax }}t</span>
    </div>
  </div>
  <div class="hud-sep"></div>
  <div class="hud-block">
    <span class="hud-label">Crédits</span>
    <span class="hud-value accent" id="hud-credits">{{ number_format($_credits, 0, ',', ' ') }} CR</span>
  </div>
  <div class="hud-spacer"></div>
  <form method="POST" action="{{ route('logout') }}" style="display:inline">
    @csrf
    <button type="submit" class="hud-btn">Déco</button>
  </form>
</header>

{{-- ── STAGE ─────────────────────────────────────────────────── --}}
<div class="stage">

  {{-- Left nav --}}
  <nav class="nav">
    @include('game.partials.nav-hud', ['_compte' => $_compte])
  </nav>

  {{-- Center content --}}
  <main class="hud-main">
    @yield('hud-content')
  </main>

  {{-- Resize handle --}}
  <div id="hud-resize-handle" class="hud-resize-handle"></div>

  {{-- Right console --}}
  <aside id="hud-console-panel" class="right-col">
    @include('game.partials.console-hud', ['_enMax' => $_enMax, '_paMax' => $_paMax])
  </aside>

</div>

@stack('hud-scripts')
@endsection
