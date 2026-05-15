@extends('layouts.game-hud')

@section('title', 'Carte 3D')

@push('hud-styles')
<style>
.hud-main { padding: 0 !important; overflow: hidden !important; position: relative; }

.c3d-wrap {
  position: absolute; top: 0; left: 0; right: 0; bottom: 0;
  background: radial-gradient(ellipse at center, #0a0f1a 0%, #050709 70%);
}
#c3d-canvas { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: block; cursor: grab; }

/* ── ONGLETS ── */
.c3d-tabs {
  position: absolute; top: 12px; left: 14px; z-index: 20;
  display: flex; background: rgba(5,7,12,0.88);
  border: 1px solid var(--border-subtle); backdrop-filter: blur(4px);
}
.c3d-tab {
  padding: 6px 18px; font-family: var(--mono); font-size: 10px;
  letter-spacing: 0.12em; text-transform: uppercase; text-decoration: none;
  color: var(--text-muted); transition: color 0.12s;
}
.c3d-tab + .c3d-tab { border-left: 1px solid var(--border-subtle); }
.c3d-tab.active { color: var(--data); background: rgba(127,212,255,0.05); }
.c3d-tab:hover:not(.active) { color: var(--text-secondary); }

/* ── BARRE MODE + AXE ── */
.c3d-modebar {
  position: absolute; top: 12px; left: 50%; transform: translateX(-50%); z-index: 20;
  display: flex; gap: 6px; align-items: center;
}
.c3d-modebtn {
  padding: 5px 14px; font-family: var(--mono); font-size: 10px;
  letter-spacing: 0.1em; text-transform: uppercase; cursor: pointer;
  background: rgba(5,7,12,0.85); border: 1px solid var(--border-subtle);
  color: var(--text-muted); transition: all 0.15s; backdrop-filter: blur(4px);
}
.c3d-modebtn:hover { color: var(--text-secondary); border-color: var(--border-strong); }
.c3d-modebtn.active { color: var(--data); border-color: rgba(127,212,255,0.5); background: rgba(127,212,255,0.06); }
.c3d-modesep { width: 1px; height: 20px; background: var(--border-subtle); }

/* ── COMPTEUR / LABEL ── */
.c3d-info {
  position: absolute; top: 12px; right: 14px; z-index: 20;
  background: rgba(5,7,12,0.85); border: 1px solid var(--border-subtle);
  padding: 6px 12px; font-family: var(--mono); font-size: 10px;
  color: var(--text-muted); backdrop-filter: blur(4px);
}
.c3d-info .sys-count { color: var(--data); font-weight: 600; }

/* ── PANNEAU SÉLECTION ── */
.c3d-panel {
  position: absolute; top: 58px; right: 14px; z-index: 25;
  background: rgba(5,7,12,0.94); border: 1px solid var(--data);
  padding: 14px 16px; width: 240px;
  font-family: var(--mono); font-size: 10px;
  backdrop-filter: blur(6px);
  box-shadow: 0 0 20px rgba(127,212,255,0.12);
  display: none; pointer-events: auto;
}
.c3d-panel.show { display: block; }
.c3d-panel-close {
  position: absolute; top: 8px; right: 10px; background: none; border: none;
  color: var(--text-muted); font-family: var(--mono); font-size: 12px;
  cursor: pointer; padding: 2px 5px; line-height: 1;
}
.c3d-panel-close:hover { color: var(--text-primary); }
.c3d-panel-title {
  font-size: 13px; color: var(--data); font-weight: 600;
  margin-bottom: 3px; letter-spacing: 0.04em; padding-right: 18px;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.c3d-panel-badge {
  display: inline-block; font-size: 9px; letter-spacing: 0.12em;
  text-transform: uppercase; color: var(--accent); font-weight: 700;
  margin-bottom: 6px;
}
.c3d-panel-sub {
  font-size: 9px; color: var(--text-muted); letter-spacing: 0.08em;
  text-transform: uppercase; margin-bottom: 6px;
}
.c3d-panel-sep { border: none; border-top: 1px solid var(--border-subtle); margin: 8px 0; }
.c3d-panel-row {
  display: flex; justify-content: space-between; gap: 8px;
  margin: 3px 0; color: var(--text-muted);
}
.c3d-panel-row span { color: var(--text-primary); text-align: right; }
.c3d-saut-box {
  margin-top: 8px; padding: 7px 8px;
  border-left: 2px solid var(--warning);
  background: rgba(251,191,36,0.04);
}
.c3d-saut-box.impossible {
  border-left-color: var(--danger); background: rgba(239,68,68,0.05);
}
.c3d-saut-label { font-size: 9px; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 5px; }
.c3d-saut-label.ok   { color: var(--success); }
.c3d-saut-label.ko   { color: var(--danger); }
.c3d-panel-actions { display: flex; gap: 6px; margin-top: 10px; }
.c3d-panel-btn {
  flex: 1; font-family: var(--mono); font-size: 10px; letter-spacing: 0.1em;
  text-transform: uppercase; padding: 6px 4px; cursor: pointer;
  background: transparent; border: 1px solid var(--border-subtle);
  color: var(--text-secondary); transition: all 0.15s; text-align: center;
}
.c3d-panel-btn:hover { color: var(--text-primary); border-color: var(--border-strong); }
.c3d-panel-btn.primary { color: var(--data); border-color: rgba(127,212,255,0.4); }
.c3d-panel-btn.primary:hover { background: rgba(127,212,255,0.08); }

/* ── TOOLTIP ── */
.c3d-tooltip {
  position: absolute; pointer-events: none; z-index: 30;
  background: rgba(5,7,12,0.9); border: 1px solid var(--border-strong);
  padding: 6px 10px; font-family: var(--mono); font-size: 10px;
  color: var(--text-primary); opacity: 0; transition: opacity 0.1s; white-space: nowrap;
}
.c3d-tooltip.show { opacity: 1; }
.c3d-tooltip .tt-dist { color: var(--data); margin-left: 8px; }

/* ── CONTRÔLES ZOOM ── */
.c3d-controls {
  position: absolute; bottom: 14px; right: 14px; z-index: 20;
  display: flex; flex-direction: column; gap: 3px;
}
.c3d-ctrl {
  width: 28px; height: 28px; background: rgba(5,7,12,0.82);
  border: 1px solid var(--border-subtle); color: var(--text-secondary);
  font-family: var(--mono); font-size: 14px; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  backdrop-filter: blur(4px); transition: all 0.15s;
}
.c3d-ctrl:hover { color: var(--data); border-color: var(--border-strong); }

/* ── LÉGENDE ── */
.c3d-legend {
  position: absolute; bottom: 14px; left: 50%; transform: translateX(-50%); z-index: 20;
  background: rgba(5,7,12,0.75); backdrop-filter: blur(4px);
  border: 1px solid var(--border-subtle); padding: 5px 16px;
  font-family: var(--mono); font-size: 9px; color: var(--text-secondary);
  display: flex; gap: 16px; align-items: center; white-space: nowrap;
}
.c3d-lgd-row { display: flex; align-items: center; gap: 5px; }
.c3d-lgd-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
</style>
@endpush

@section('hud-content')
<div class="c3d-wrap">

  {{-- Onglets --}}
  <div class="c3d-tabs">
    <span class="c3d-tab active">3D</span>
    <a href="{{ route('carte') }}" class="c3d-tab">Carte</a>
    <a href="{{ route('personnage.spatiocarte', ['onglet' => 'atlas']) }}" class="c3d-tab">Atlas</a>
  </div>

  {{-- Barre mode + axe vertical --}}
  <div class="c3d-modebar">
    <button class="c3d-modebtn active" id="btn-galactic">Galactique</button>
    <button class="c3d-modebtn"        id="btn-systeme">Système</button>
    <div class="c3d-modesep"></div>
    <button class="c3d-modebtn active" id="btn-axis-Z" title="Axe vertical = Z (altitude)">Axe Z</button>
    <button class="c3d-modebtn"        id="btn-axis-Y" title="Axe vertical = Y">Axe Y</button>
    <button class="c3d-modebtn"        id="btn-axis-X" title="Axe vertical = X">Axe X</button>
  </div>

  {{-- Compteur --}}
  <div class="c3d-info" id="c3d-info">
    <span class="sys-count" id="c3d-count">{{ $nSystems }}</span> systèmes connus
  </div>

  {{-- Canvas --}}
  <canvas id="c3d-canvas"></canvas>

  {{-- Panneau sélection --}}
  <div class="c3d-panel" id="c3d-panel">
    <button class="c3d-panel-close" id="cp-close">✕</button>
    <div class="c3d-panel-title" id="cp-name">—</div>
    <div class="c3d-panel-badge" id="cp-badge" style="display:none">Position actuelle</div>
    <div class="c3d-panel-sub"   id="cp-sub"  style="display:none"></div>
    <hr class="c3d-panel-sep">
    <div class="c3d-panel-row" id="cp-row-coords">Coordonnées  <span id="cp-coords">—</span></div>
    <div class="c3d-panel-row" id="cp-row-dist">Distance      <span id="cp-dist">—</span></div>
    <div class="c3d-panel-row" id="cp-row-type">Type étoile   <span id="cp-type">—</span></div>
    <hr class="c3d-panel-sep" id="cp-sep2">
    <div class="c3d-panel-row" id="cp-row-planetes">Planètes      <span id="cp-planetes">—</span></div>
    <div class="c3d-panel-row" id="cp-row-mines">Gisements     <span id="cp-mines">—</span></div>
    <div class="c3d-panel-row" id="cp-row-stations">Stations      <span id="cp-stations">—</span></div>
    <div class="c3d-saut-box"  id="cp-saut-box">
      <div class="c3d-saut-label" id="cp-saut-label">—</div>
      <div class="c3d-panel-row">Coût PA      <span id="cp-pa">—</span></div>
      <div class="c3d-panel-row">Énergie req. <span id="cp-energie">—</span></div>
    </div>
    <div class="c3d-panel-actions">
      <button class="c3d-panel-btn primary" id="cp-centrer">Centrer</button>
      <button class="c3d-panel-btn"         id="cp-zoomer">Voir système</button>
    </div>
  </div>

  {{-- Tooltip --}}
  <div class="c3d-tooltip" id="c3d-tooltip"></div>

  {{-- Contrôles zoom --}}
  <div class="c3d-controls">
    <button class="c3d-ctrl" id="ctrl-plus"  title="Zoom +">+</button>
    <button class="c3d-ctrl" id="ctrl-minus" title="Zoom −">−</button>
    <button class="c3d-ctrl" id="ctrl-home"  title="Vue par défaut">⌂</button>
  </div>

  {{-- Légende --}}
  <div class="c3d-legend" id="c3d-legend">
    <div class="c3d-lgd-row">
      <div class="c3d-lgd-dot" style="background:#ff8a3d;box-shadow:0 0 5px #ff8a3d;"></div>
      Position actuelle
    </div>
    <div class="c3d-lgd-row">
      <div class="c3d-lgd-dot" style="background:#7fd4ff;box-shadow:0 0 4px #7fd4ff;"></div>
      Systèmes découverts
    </div>
    <div class="c3d-lgd-row" style="color:var(--text-muted);">
      Tab : étoile suiv. · Flèches/Clic-droit : déplacer · Clic : sélectionner · Dbl-clic : centrer+zoomer
    </div>
  </div>

</div>
@endsection

@push('hud-scripts')
<script type="importmap">
{
  "imports": {
    "three": "https://unpkg.com/three@0.160.0/build/three.module.js"
  }
}
</script>

<script type="module">
import * as THREE from 'three';

// ── DONNÉES PHP ───────────────────────────────────────────────────────────────
const galacticData  = @json($galacticData);
const systemeAjaxUrl = '{{ route("api.carte.3d.systeme", ["id" => "__ID__"]) }}';
const currentSysId   = {{ $galacticData[0]['id'] ?? 0 }};

// ── SCÈNE / RENDERER ─────────────────────────────────────────────────────────
const canvas   = document.getElementById('c3d-canvas');
const wrap     = canvas.parentElement;

function wrapW() { return wrap.clientWidth  || (window.innerWidth  - 200); }
function wrapH() { return wrap.clientHeight || (window.innerHeight - 52);  }

const scene    = new THREE.Scene();
scene.fog      = new THREE.FogExp2(0x05070c, 0.003);
const camera   = new THREE.PerspectiveCamera(60, wrapW() / wrapH(), 0.1, 2000);
const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
renderer.setSize(wrapW(), wrapH());
renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

// ── ÉTAT ─────────────────────────────────────────────────────────────────────
let mode           = 'galactic'; // 'galactic' | 'system'
let verticalAxis   = 'Z';        // 'X' | 'Y' | 'Z'
let currentGroup   = null;       // groupe Three.js sélectionné
let pickables      = [];         // { core, data, group }
let navIdx         = -1;         // index Tab navigation
let lastSystemId   = currentSysId; // dernier système vu en mode système
let galacticRadius = 80;           // rayon auto-fit calculé à chaque buildGalactic

// Caméra
let radius       = 80;
let azimuth      = Math.PI / 6;
let polar        = Math.PI / 4;
const camTarget  = new THREE.Vector3();
let targetAnim   = null;
let radiusTarget = null;

// ── COORD MAP ────────────────────────────────────────────────────────────────
// game data: x=EW*9, y=altitude*1.8, z=NS*9
// toSceneVec: Three.js Vector3 whose Y = altitude component, chosen by verticalAxis
function toSceneVec(x, y, z) {
  // galacticData: x=EW*9, y=alt*1.8, z=NS*9
  // verticalAxis Z: game.z (NS) → Three.Y  → Vector3(x, z, y)
  // verticalAxis Y: game.y (alt) → Three.Y → Vector3(x, y, z)
  // verticalAxis X: game.x (EW) → Three.Y  → Vector3(z, x, y)
  if (verticalAxis === 'Z') return new THREE.Vector3(x, z, y);
  if (verticalAxis === 'Y') return new THREE.Vector3(x, y, z);
  return new THREE.Vector3(z, x, y); // X
}

// ── FOND ÉTOILÉ ──────────────────────────────────────────────────────────────
const sfCount = 3000;
const sfPos = new Float32Array(sfCount * 3);
const sfCol = new Float32Array(sfCount * 3);
for (let i = 0; i < sfCount; i++) {
  const r  = 400 + Math.random() * 600;
  const t  = Math.random() * Math.PI * 2;
  const p  = Math.acos(2 * Math.random() - 1);
  sfPos[i*3]   = r * Math.sin(p) * Math.cos(t);
  sfPos[i*3+1] = r * Math.sin(p) * Math.sin(t);
  sfPos[i*3+2] = r * Math.cos(p);
  const b = 0.3 + Math.random() * 0.7;
  sfCol[i*3] = b; sfCol[i*3+1] = b; sfCol[i*3+2] = b + Math.random() * 0.2;
}
const sfGeo = new THREE.BufferGeometry();
sfGeo.setAttribute('position', new THREE.BufferAttribute(sfPos, 3));
sfGeo.setAttribute('color',    new THREE.BufferAttribute(sfCol, 3));
const starfield = new THREE.Points(sfGeo, new THREE.PointsMaterial({
  size: 0.8, vertexColors: true, transparent: true, opacity: 0.8, sizeAttenuation: false
}));
scene.add(starfield);

// ── GRILLE ────────────────────────────────────────────────────────────────────
const gridGroup = new THREE.Group();
for (let i = -20; i <= 20; i++) {
  const x   = i * 5;
  const maj = i % 5 === 0;
  const mat = new THREE.LineBasicMaterial({ color: maj ? 0x2d3b4f : 0x1f2937, transparent: true, opacity: maj ? 0.8 : 0.5 });
  gridGroup.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints([new THREE.Vector3(x,0,-100), new THREE.Vector3(x,0,100)]), mat));
  gridGroup.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints([new THREE.Vector3(-100,0,x), new THREE.Vector3(100,0,x)]), mat));
}
scene.add(gridGroup);

// ── HELPERS ──────────────────────────────────────────────────────────────────
function makeGlow(hex) {
  const c = document.createElement('canvas'); c.width = c.height = 128;
  const ctx = c.getContext('2d');
  const g   = ctx.createRadialGradient(64,64,0, 64,64,64);
  const col = new THREE.Color(hex);
  const rgb = `${Math.floor(col.r*255)},${Math.floor(col.g*255)},${Math.floor(col.b*255)}`;
  g.addColorStop(0,   `rgba(${rgb},1)`);
  g.addColorStop(0.2, `rgba(${rgb},0.5)`);
  g.addColorStop(0.5, `rgba(${rgb},0.15)`);
  g.addColorStop(1,   `rgba(${rgb},0)`);
  ctx.fillStyle = g; ctx.fillRect(0,0,128,128);
  return new THREE.CanvasTexture(c);
}

function makeOrbitRing(radius, color) {
  const pts = [];
  for (let i = 0; i <= 64; i++) {
    const a = (i / 64) * Math.PI * 2;
    pts.push(new THREE.Vector3(Math.cos(a) * radius, 0, Math.sin(a) * radius));
  }
  return new THREE.Line(
    new THREE.BufferGeometry().setFromPoints(pts),
    new THREE.LineBasicMaterial({ color, transparent: true, opacity: 0.25 })
  );
}

// ── CLEARSCENE ────────────────────────────────────────────────────────────────
function clearScene() {
  // Remove everything except starfield + grid
  const toRemove = [];
  scene.children.forEach(c => { if (c !== starfield && c !== gridGroup) toRemove.push(c); });
  toRemove.forEach(c => scene.remove(c));
  pickables = [];
  navIdx    = -1;
  clearSelection();
}

// ── BUILD GALACTIC ────────────────────────────────────────────────────────────
function buildGalactic() {
  clearScene();
  gridGroup.visible = true;

  let maxExtent = 1;

  galacticData.forEach(sys => {
    const pos = toSceneVec(sys.x || 0, sys.y || 0, sys.z || 0);
    maxExtent = Math.max(maxExtent, pos.length());

    const g   = new THREE.Group();
    g.position.copy(pos);
    g.userData = { ...sys, _mode: 'galactic' };

    const core = new THREE.Mesh(
      new THREE.SphereGeometry(sys.size * 0.5, 16, 16),
      new THREE.MeshBasicMaterial({ color: new THREE.Color(sys.colorHex) })
    );
    g.add(core);

    const glow = new THREE.Sprite(new THREE.SpriteMaterial({
      map: makeGlow(sys.colorHex), blending: THREE.AdditiveBlending,
      transparent: true, opacity: 0.8
    }));
    glow.scale.set(sys.size * 3, sys.size * 3, 1);
    g.add(glow);

    // Drop line vers plan Y=0
    const offY = pos.y;
    if (Math.abs(offY) > 0.5) {
      const lm = new THREE.LineBasicMaterial({ color: new THREE.Color(sys.colorHex), transparent: true, opacity: 0.18 });
      g.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints([
        new THREE.Vector3(0,0,0), new THREE.Vector3(0,-offY,0)
      ]), lm));
      const mk = new THREE.Mesh(
        new THREE.CircleGeometry(sys.size * 0.28, 12),
        new THREE.MeshBasicMaterial({ color: new THREE.Color(sys.colorHex), transparent: true, opacity: 0.28, side: THREE.DoubleSide })
      );
      mk.rotation.x = -Math.PI / 2; mk.position.y = -offY;
      g.add(mk);
    }

    scene.add(g);
    pickables.push({ core, data: g.userData, group: g });
  });

  // Auto-fit camera : rayon = étendue max / tan(demi-FOV 30°) × marge 1.25
  // Garantit que toutes les étoiles rentrent dans le frustum depuis l'origine
  galacticRadius = Math.max(80, maxExtent / Math.tan(Math.PI / 6) * 1.25);
  radius = galacticRadius;
  updateCamera();

  // Anneau de référence (rayon = 1 AL en unités Three.js = 9)
  scene.add(makeOrbitRing(9, 0x2d4f6f));

  updateInfo();
}

// ── BUILD SYSTEM ──────────────────────────────────────────────────────────────
async function buildSystem(sysId) {
  lastSystemId = sysId;
  clearScene();
  gridGroup.visible = true;

  const url = systemeAjaxUrl.replace('__ID__', sysId);
  let data;
  try {
    const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    data = await resp.json();
  } catch(e) {
    console.error('carte3d system load error', e);
    return;
  }

  // Étoile centrale
  const starGlow = new THREE.Sprite(new THREE.SpriteMaterial({
    map: makeGlow('#ffcc66'), blending: THREE.AdditiveBlending, transparent: true, opacity: 0.9
  }));
  starGlow.scale.set(8, 8, 1);
  scene.add(starGlow);

  const starCore = new THREE.Mesh(
    new THREE.SphereGeometry(1.5, 24, 24),
    new THREE.MeshBasicMaterial({ color: 0xffcc66 })
  );
  scene.add(starCore);

  // POIs
  data.pois.forEach(poi => {
    const g   = new THREE.Group();
    g.position.set(poi.x, poi.y, poi.z);
    g.userData = { ...poi, _mode: 'system', _sysName: data.systeme.nom };

    const color = new THREE.Color(poi.colorHex);

    // Anneau orbital autour du parent ou de l'étoile
    if (poi.orbitRadius > 0 && poi.type !== 'satellite') {
      scene.add(makeOrbitRing(poi.orbitRadius, color.getHex()));
    }

    const core = new THREE.Mesh(
      new THREE.SphereGeometry(poi.size * 0.5, 14, 14),
      new THREE.MeshBasicMaterial({ color })
    );
    g.add(core);

    if (poi.type === 'station') {
      // Station: petite forme octaédrique
      const oct = new THREE.Mesh(
        new THREE.OctahedronGeometry(poi.size * 0.4),
        new THREE.MeshBasicMaterial({ color, wireframe: true })
      );
      g.add(oct);
    }

    const glow = new THREE.Sprite(new THREE.SpriteMaterial({
      map: makeGlow(poi.colorHex), blending: THREE.AdditiveBlending, transparent: true, opacity: 0.6
    }));
    glow.scale.set(poi.size * 2.5, poi.size * 2.5, 1);
    g.add(glow);

    scene.add(g);
    pickables.push({ core, data: g.userData, group: g });
  });

  // Zoom adapté au système
  radiusTarget = 60;
  targetAnim   = new THREE.Vector3(0, 0, 0);

  updateInfo(data.systeme.nom, data.pois.length);
}

// ── MISE À JOUR AFFICHAGE ─────────────────────────────────────────────────────
function updateInfo(label, count) {
  const countEl = document.getElementById('c3d-count');
  const infoEl  = document.getElementById('c3d-info');
  if (mode === 'system') {
    infoEl.innerHTML = `<span class="sys-count">${label ?? '—'}</span> — ${count ?? 0} POIs`;
  } else {
    countEl.textContent = galacticData.length;
    infoEl.innerHTML = `<span class="sys-count">${galacticData.length}</span> systèmes connus`;
  }
}

function updateModeButtons() {
  document.getElementById('btn-galactic').classList.toggle('active', mode === 'galactic');
  document.getElementById('btn-systeme').classList.toggle('active',  mode === 'system');
}

function updateAxisButtons() {
  ['X','Y','Z'].forEach(a => document.getElementById('btn-axis-'+a).classList.toggle('active', verticalAxis === a));
}

// ── CAMÉRA ────────────────────────────────────────────────────────────────────
function updateCamera() {
  camera.position.set(
    camTarget.x + radius * Math.sin(polar) * Math.cos(azimuth),
    camTarget.y + radius * Math.cos(polar),
    camTarget.z + radius * Math.sin(polar) * Math.sin(azimuth)
  );
  camera.lookAt(camTarget);
}
updateCamera();

function centerOn(g) { targetAnim = g.position.clone(); }
function zoomOn(g)   { targetAnim = g.position.clone(); radiusTarget = mode === 'system' ? 10 : 20; }

// ── ORBITE / DRAG ─────────────────────────────────────────────────────────────
let isDragging = false;
let isPanning  = false;
let prevMouse  = { x: 0, y: 0 };

canvas.addEventListener('contextmenu', e => e.preventDefault());

canvas.addEventListener('mousedown', e => {
  isDragging = true;
  isPanning  = (e.button === 2);
  prevMouse  = { x: e.clientX, y: e.clientY };
  canvas.style.cursor = isPanning ? 'move' : 'grabbing';
});
window.addEventListener('mouseup', () => { isDragging = false; isPanning = false; canvas.style.cursor = 'grab'; });
window.addEventListener('mousemove', e => {
  if (!isDragging) { doHover(e); return; }
  const dx = e.clientX - prevMouse.x;
  const dy = e.clientY - prevMouse.y;
  if (isPanning) {
    // Clic droit : pan en espace écran (vecteurs right/up calculés depuis azimuth/polar)
    const panSpeed = radius * 0.002;
    const rightX =  Math.cos(azimuth);
    const rightZ = -Math.sin(azimuth);
    const upX = -Math.cos(polar) * Math.sin(azimuth);
    const upY =  Math.sin(polar);
    const upZ = -Math.cos(polar) * Math.cos(azimuth);
    camTarget.x -= rightX * dx * panSpeed;
    camTarget.z -= rightZ * dx * panSpeed;
    camTarget.x += upX * dy * panSpeed;
    camTarget.y += upY * dy * panSpeed;
    camTarget.z += upZ * dy * panSpeed;
    targetAnim = null;
  } else {
    azimuth -= dx * 0.008;
    polar    = Math.max(0.05, Math.min(Math.PI - 0.05, polar + dy * 0.008));
  }
  prevMouse = { x: e.clientX, y: e.clientY };
  updateCamera();
});
canvas.addEventListener('wheel', e => {
  e.preventDefault();
  radius = Math.max(4, Math.min(300, radius * (1 + e.deltaY * 0.001)));
  updateCamera();
}, { passive: false });

// ── TOUCHES ───────────────────────────────────────────────────────────────────
window.addEventListener('keydown', e => {
  if (document.activeElement !== document.body && document.activeElement !== canvas) return;
  if (e.key === 'Tab') {
    e.preventDefault();
    navStar(e.shiftKey ? -1 : 1);
    return;
  }
  const panStep = radius * 0.06;
  const dir = new THREE.Vector3();
  camera.getWorldDirection(dir);
  dir.y = 0; dir.normalize();
  const right = new THREE.Vector3().crossVectors(dir, new THREE.Vector3(0,1,0)).normalize();

  if (e.key === 'ArrowLeft')  { camTarget.addScaledVector(right, -panStep); targetAnim = null; updateCamera(); }
  if (e.key === 'ArrowRight') { camTarget.addScaledVector(right,  panStep); targetAnim = null; updateCamera(); }
  if (e.key === 'ArrowUp')    { camTarget.addScaledVector(dir,    panStep); targetAnim = null; updateCamera(); }
  if (e.key === 'ArrowDown')  { camTarget.addScaledVector(dir,   -panStep); targetAnim = null; updateCamera(); }
  if (e.key === 'PageUp')     { radius = Math.max(4, radius * 0.85); updateCamera(); }
  if (e.key === 'PageDown')   { radius = Math.min(300, radius * 1.18); updateCamera(); }
});

function navStar(dir) {
  if (!pickables.length) return;
  navIdx = (navIdx + dir + pickables.length) % pickables.length;
  const p = pickables[navIdx];
  selectItem(p);
  centerOn(p.group);
}

// ── RAYCASTING ────────────────────────────────────────────────────────────────
const raycaster = new THREE.Raycaster();
const mouse     = new THREE.Vector2();

function raycast(e) {
  const rect = canvas.getBoundingClientRect();
  mouse.x =  ((e.clientX - rect.left) / rect.width)  * 2 - 1;
  mouse.y = -((e.clientY - rect.top)  / rect.height) * 2 + 1;
  raycaster.setFromCamera(mouse, camera);
  const hits = raycaster.intersectObjects(pickables.map(p => p.core), false);
  return hits.length ? (pickables.find(p => p.core === hits[0].object) || null) : null;
}

// ── TOOLTIP ───────────────────────────────────────────────────────────────────
const tooltip = document.getElementById('c3d-tooltip');
function doHover(e) {
  const hit = raycast(e);
  if (hit) {
    const d = hit.data;
    const distStr = (d.distance != null && !d.isCurrent) ? ` <span class="tt-dist">${d.distance} AL</span>` : '';
    tooltip.innerHTML = d.name + distStr;
    const rect = canvas.getBoundingClientRect();
    tooltip.style.left = (e.clientX - rect.left + 14) + 'px';
    tooltip.style.top  = (e.clientY - rect.top  - 26) + 'px';
    tooltip.classList.add('show');
    canvas.style.cursor = 'pointer';
  } else {
    tooltip.classList.remove('show');
    if (!isDragging) canvas.style.cursor = 'grab';
  }
}

// ── PANNEAU SÉLECTION ─────────────────────────────────────────────────────────
const panel = document.getElementById('c3d-panel');

function selectItem(hit) {
  currentGroup = hit.group;
  const d = hit.data;

  document.getElementById('cp-name').textContent    = d.name;
  document.getElementById('cp-badge').style.display = (d.isCurrent && mode === 'galactic') ? '' : 'none';

  if (mode === 'galactic') {
    document.getElementById('cp-sub').style.display = 'none';
    document.getElementById('cp-row-coords').style.display = '';
    document.getElementById('cp-row-dist').style.display   = '';
    document.getElementById('cp-row-type').style.display   = '';
    document.getElementById('cp-sep2').style.display       = '';
    document.getElementById('cp-row-planetes').style.display = '';
    document.getElementById('cp-row-mines').style.display    = '';
    document.getElementById('cp-row-stations').style.display = '';

    document.getElementById('cp-coords').textContent   = d.coords || '—';
    document.getElementById('cp-dist').textContent     = d.isCurrent ? '—' : (d.distance + ' AL');
    document.getElementById('cp-type').textContent     = d.type_etoile || '—';
    document.getElementById('cp-planetes').textContent = d.nb_planetes ?? '—';
    document.getElementById('cp-mines').textContent    = d.nb_mines ?? '—';
    document.getElementById('cp-stations').textContent = d.nb_stations ?? '—';

    const sautBox = document.getElementById('cp-saut-box');
    if (d.isCurrent) {
      sautBox.style.display = 'none';
    } else {
      sautBox.style.display = '';
      document.getElementById('cp-pa').textContent      = d.pa_requis + ' PA';
      document.getElementById('cp-energie').textContent = d.energie_requise + ' E';
      const lbl = document.getElementById('cp-saut-label');
      if (d.saut_possible) {
        lbl.textContent = 'Saut possible'; lbl.className = 'c3d-saut-label ok';
        sautBox.classList.remove('impossible');
      } else {
        lbl.textContent = 'Saut impossible'; lbl.className = 'c3d-saut-label ko';
        sautBox.classList.add('impossible');
      }
    }
  } else {
    // Mode système: POI info
    document.getElementById('cp-sub').style.display   = '';
    document.getElementById('cp-sub').textContent     = d.categorie || d.type || '—';
    document.getElementById('cp-row-coords').style.display  = 'none';
    document.getElementById('cp-row-dist').style.display    = 'none';
    document.getElementById('cp-row-type').style.display    = 'none';
    document.getElementById('cp-sep2').style.display        = '';
    document.getElementById('cp-row-planetes').style.display = 'none';
    document.getElementById('cp-row-mines').style.display    = '';
    document.getElementById('cp-row-stations').style.display = 'none';
    document.getElementById('cp-mines').textContent = d.nb_gisements ?? '—';
    const minesRow = document.getElementById('cp-row-mines');
    minesRow.childNodes[0].textContent = 'Gisements ';
    document.getElementById('cp-saut-box').style.display = 'none';
  }

  document.getElementById('cp-zoomer').textContent = (mode === 'galactic') ? 'Voir système' : 'Zoomer';

  panel.classList.add('show');
}

function clearSelection() { currentGroup = null; panel.classList.remove('show'); }

document.getElementById('cp-close').addEventListener('click', clearSelection);
document.getElementById('cp-centrer').addEventListener('click', () => { if (currentGroup) centerOn(currentGroup); });
document.getElementById('cp-zoomer').addEventListener('click', () => {
  if (!currentGroup) return;
  if (mode === 'galactic') switchToSystem(currentGroup.userData.id);
  else                     zoomOn(currentGroup);
});

// ── HELPERS MODE ─────────────────────────────────────────────────────────────
function switchToSystem(sysId) {
  if (!sysId) return;
  mode = 'system'; updateModeButtons();
  buildSystem(sysId);
}

function switchToGalactic() {
  const prevId = lastSystemId;
  mode = 'galactic'; updateModeButtons();
  azimuth = Math.PI/6; polar = Math.PI/4;
  camTarget.set(0,0,0);
  buildGalactic(); // recalcule radius via auto-fit
  if (prevId) {
    const found = pickables.find(p => p.data.id === prevId);
    if (found) { selectItem(found); centerOn(found.group); }
  }
}

// ── CLICK / DBL-CLICK ────────────────────────────────────────────────────────
let _clickTimer = null;
canvas.addEventListener('click', e => {
  if (isPanning) return;
  const hit = raycast(e);
  clearTimeout(_clickTimer);
  _clickTimer = setTimeout(() => { _clickTimer = null; if (hit) selectItem(hit); else clearSelection(); }, 220);
});
canvas.addEventListener('dblclick', e => {
  clearTimeout(_clickTimer); _clickTimer = null;
  const hit = raycast(e);
  if (!hit) return;
  if (currentGroup === hit.group) {
    // Déjà centré sur cet objet → basculer de mode
    if (mode === 'galactic') switchToSystem(hit.data.id);
    else                      switchToGalactic();
  } else {
    // Pas encore centré → centrer + sélectionner
    selectItem(hit);
    centerOn(hit.group);
  }
});

// ── BOUTONS MODE / AXE ────────────────────────────────────────────────────────
document.getElementById('btn-galactic').addEventListener('click', () => {
  if (mode === 'galactic') return;
  switchToGalactic();
});
document.getElementById('btn-systeme').addEventListener('click', () => {
  if (mode === 'system') return;
  switchToSystem(currentGroup?.userData?.id ?? currentSysId);
});

['X','Y','Z'].forEach(a => {
  document.getElementById('btn-axis-'+a).addEventListener('click', () => {
    if (verticalAxis === a) return;
    verticalAxis = a; updateAxisButtons();
    if (mode === 'galactic') buildGalactic();
  });
});

// ── BOUTONS ZOOM ─────────────────────────────────────────────────────────────
document.getElementById('ctrl-plus').addEventListener('click',  () => { radiusTarget = Math.max(4,   radius * 0.65); });
document.getElementById('ctrl-minus').addEventListener('click', () => { radiusTarget = Math.min(300, radius * 1.5); });
document.getElementById('ctrl-home').addEventListener('click',  () => {
  targetAnim   = new THREE.Vector3(0,0,0);
  radiusTarget = (mode === 'galactic') ? galacticRadius : 60;
  azimuth = Math.PI/6; polar = Math.PI/4;
});

// ── ANIMATION ────────────────────────────────────────────────────────────────
function animate() {
  requestAnimationFrame(animate);
  let dirty = false;
  if (targetAnim !== null) {
    camTarget.lerp(targetAnim, 0.1);
    if (camTarget.distanceTo(targetAnim) < 0.05) { camTarget.copy(targetAnim); targetAnim = null; }
    dirty = true;
  }
  if (radiusTarget !== null) {
    radius += (radiusTarget - radius) * 0.1;
    if (Math.abs(radius - radiusTarget) < 0.05) { radius = radiusTarget; radiusTarget = null; }
    dirty = true;
  }
  if (dirty) updateCamera();
  renderer.render(scene, camera);
}
animate();

// ── RESIZE ────────────────────────────────────────────────────────────────────
let resizeRaf;
function onResize() {
  cancelAnimationFrame(resizeRaf);
  resizeRaf = requestAnimationFrame(() => {
    camera.aspect = wrapW() / wrapH();
    camera.updateProjectionMatrix();
    renderer.setSize(wrapW(), wrapH());
    updateCamera();
  });
}
window.addEventListener('resize', onResize);
new ResizeObserver(onResize).observe(wrap);

// ── INIT ─────────────────────────────────────────────────────────────────────
buildGalactic();
</script>
@endpush
