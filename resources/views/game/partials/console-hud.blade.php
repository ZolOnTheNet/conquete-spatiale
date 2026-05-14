{{-- Console HUD — colonne droite du layout game-hud --}}
@php
    $_pNom    = isset($personnage) ? strtolower(trim(($personnage->prenom ?? '') . ' ' . ($personnage->nom ?? 'cmdr'))) : 'cmdr';
    $_vNom    = isset($vaisseau)   ? strtolower($vaisseau->objetSpatial->nom ?? $vaisseau->nom ?? 'vaisseau') : 'vaisseau';
    $_pLabel  = $_pNom . '@' . $_vNom;
@endphp
<div class="console">
  <div class="console-head">
    <span class="con-status">CONSOLE</span>
    <button class="clear-btn" onclick="hudConsoleClear()">CLR</button>
  </div>

  <div class="console-out" id="console-output">
    <p class="console-line sys">Système initialisé</p>
    <p class="console-line sys">Connexion établie</p>
    <p class="console-line dim">Tapez 'help' pour l'aide</p>
  </div>

  <div class="console-input">
    <div class="console-prompt-label">{{ $_pLabel }}</div>
    <input type="text" id="command-input"
           placeholder="commande..."
           autocomplete="off" spellcheck="false"
           onkeydown="hudConsoleKeydown(event)">
  </div>

  <div class="shortcuts">
    <button class="sc" onclick="effectuerScan('simple')"  title="Scan simple">📡 Scan</button>
    <button class="sc" onclick="effectuerScan('reglage')" title="Scan + Réglage">🔧 Régl.</button>
    <button class="sc" onclick="effectuerScan('astro')"   title="Scan + Astro">🌟 Astro</button>
    <button class="sc" onclick="sendQuickCommand('help')">Aide</button>
    <button class="sc" onclick="sendQuickCommand('history')">Hist.</button>
    <button class="sc" onclick="hudConsoleClear()">Clr</button>
  </div>
</div>

<script>
(function() {
// ── CONSOLE HUD ────────────────────────────────────────────────────────────────
const _input  = document.getElementById('command-input');
const _output = document.getElementById('console-output');
const _enMax  = {{ $enMax ?? ($_enMax ?? 100) }};
const _paMax  = {{ $paMax ?? ($_paMax ?? 50) }};

// Restaurer le contenu sauvegardé
(function restoreConsole() {
    const saved = localStorage.getItem('consoleContent');
    if (saved && _output) {
        _output.innerHTML = saved;
        setTimeout(() => _output.scrollTop = _output.scrollHeight, 100);
    }
})();

let _history = JSON.parse(localStorage.getItem('commandHistory') || '[]');
let _histIdx = -1;
let _tempCmd = '';

const _cmds = [
    'help','aide','status','statut','position','pos','vaisseau','ship',
    'deplacer','move','bouger','saut','jump','scan','scanner',
    'carte','map','inventaire','inv','marche','market',
    'acheter','buy','vendre','sell','prix','prices',
    'armes','weapons','boucliers','shields','equiper','equip',
    'arrimer','dock','desarrimer','undock','clear'
];

if (_input) {
    _input.focus();
    if (_output) _output.addEventListener('click', () => _input.focus());
}

function hudConsoleKeydown(e) {
    if (e.key === 'Enter') { sendCommand(e); return; }

    if (e.ctrlKey && e.key === 'l') {
        e.preventDefault(); hudConsoleClear(); return;
    }
    if (e.ctrlKey && e.key === 'c') {
        e.preventDefault();
        _input.value = ''; _histIdx = -1; _tempCmd = '';
        appendToConsole('^C', 'sys'); return;
    }
    if (e.key === 'Tab') {
        e.preventDefault();
        const cur = _input.value.toLowerCase();
        if (cur) {
            const m = _cmds.filter(c => c.startsWith(cur));
            if (m.length === 1) _input.value = m[0] + ' ';
            else if (m.length > 1) appendToConsole('Suggestions: ' + m.join(', '), 'data');
        }
        return;
    }
    if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (_histIdx === -1) _tempCmd = _input.value;
        if (_histIdx < _history.length - 1) _input.value = _history[++_histIdx];
        return;
    }
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (_histIdx > 0) _input.value = _history[--_histIdx];
        else if (_histIdx === 0) { _histIdx = -1; _input.value = _tempCmd; }
        return;
    }
}
window.hudConsoleKeydown = hudConsoleKeydown;

function addToHistory(cmd) {
    if (!_history.length || _history[0] !== cmd) {
        _history.unshift(cmd);
        if (_history.length > 100) _history = _history.slice(0, 100);
        localStorage.setItem('commandHistory', JSON.stringify(_history));
    }
}

function sendCommand(cmdOrEvent) {
    let cmd;
    const isEvent = (typeof cmdOrEvent !== 'string');
    if (isEvent) {
        if (cmdOrEvent) cmdOrEvent.preventDefault();
        cmd = _input ? _input.value.trim() : '';
        if (!cmd) return;
        addToHistory(cmd);
        _histIdx = -1;
    } else {
        cmd = cmdOrEvent.trim();
    }

    appendToConsole('> ' + cmd, 'cmd');

    fetch('{{ route("command") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ command: cmd })
    })
    .then(r => {
        if (r.status === 419) { appendToConsole('[SESSION EXPIRÉE] Rafraîchissez (F5)', 'warn'); return null; }
        return r.json();
    })
    .then(data => {
        if (!data) return;
        if (data.message) {
            data.message.split('\n').forEach(line => {
                if (line.trim()) appendToConsoleWithColors(line, data.success ? '' : 'err');
            });
        }
        updateGameInfo(data);
        if (data.refresh_ui) setTimeout(() => { saveConsoleContent(); location.reload(); }, 1000);
    })
    .catch(err => appendToConsole('[ERREUR] ' + err.message, 'err'));

    if (isEvent && _input) _input.value = '';
}
window.sendCommand = sendCommand;

function sendQuickCommand(cmd) {
    if (cmd === 'clear') { hudConsoleClear(); return; }
    if (cmd === 'history') {
        appendToConsole('=== HISTORIQUE ===', 'data');
        if (!_history.length) { appendToConsole('Aucune commande', 'dim'); return; }
        _history.slice().reverse().forEach((c, i) => appendToConsole((i+1) + '. ' + c));
        return;
    }
    addToHistory(cmd);
    if (_input) { _input.value = cmd; sendCommand(); }
}
window.sendQuickCommand = sendQuickCommand;

function hudConsoleClear() {
    if (!_output) return;
    _output.innerHTML = '';
    appendToConsole('Console effacée', 'dim');
    saveConsoleContent();
}
window.hudConsoleClear = hudConsoleClear;

function appendToConsole(text, cls) {
    if (!_output) return;
    const p = document.createElement('p');
    p.className = 'console-line' + (cls ? ' ' + cls : '');
    p.textContent = text;
    _output.appendChild(p);
    _output.scrollTop = _output.scrollHeight;
    saveConsoleContent();
}
window.appendToConsole = appendToConsole;

function appendToConsoleWithColors(text, defaultCls) {
    if (!_output) return;
    const p = document.createElement('p');
    p.className = 'console-line' + (defaultCls ? ' ' + defaultCls : '');
    // Marqueurs : [W]=blanc  [T]=cyan  [C]=orange  [E]=rouge
    const rx = /\[W\](.*?)\[\/W\]|\[T\](.*?)\[\/T\]|\[C\](.*?)\[\/C\]|\[E\](.*?)\[\/E\]/g;
    let last = 0, m;
    while ((m = rx.exec(text)) !== null) {
        if (m.index > last) p.appendChild(document.createTextNode(text.substring(last, m.index)));
        const s = document.createElement('span');
        if      (m[1] !== undefined) { s.style.color = 'var(--text-primary)'; s.style.fontWeight = '700'; s.textContent = m[1]; }
        else if (m[2] !== undefined) { s.style.color = 'var(--data)';         s.style.fontWeight = '600'; s.textContent = m[2]; }
        else if (m[3] !== undefined) { s.style.color = 'var(--warning)';                                  s.textContent = m[3]; }
        else                         { s.style.color = 'var(--danger)';                                   s.textContent = m[4]; }
        p.appendChild(s);
        last = rx.lastIndex;
    }
    if (last < text.length) p.appendChild(document.createTextNode(text.substring(last)));
    _output.appendChild(p);
    _output.scrollTop = _output.scrollHeight;
    saveConsoleContent();
}
window.appendToConsoleWithColors = appendToConsoleWithColors;

function saveConsoleContent() {
    if (_output) localStorage.setItem('consoleContent', _output.innerHTML);
}

// ── SCAN ───────────────────────────────────────────────────────────────────────
async function effectuerScan(type) {
    const routes = {
        simple:  '{{ route("navire.scan.simple") }}',
        reglage: '{{ route("navire.scan.reglage") }}',
        astro:   '{{ route("navire.scan.astro") }}'
    };
    const labels = { simple: 'scan', reglage: 'scan reglage', astro: 'scan astro' };
    addToHistory(labels[type]);
    appendToConsole('> ' + labels[type], 'cmd');

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    try {
        const r = await fetch(routes[type], {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        });
        if (r.status === 419) { appendToConsole('[SESSION EXPIRÉE] Rafraîchissez (F5)', 'warn'); return; }
        const data = await r.json();
        if (!data.success) { appendToConsole('[ERREUR] ' + (data.message || 'Erreur'), 'err'); return; }

        const titres = { simple: '── SCAN SIMPLE ──', reglage: '── SCAN + RÉGLAGE ──', astro: '── SCAN + ASTRO ──' };
        appendToConsole(titres[type], 'data');

        if (data.jet_competence) {
            const j = data.jet_competence;
            appendToConsole('JET: ' + (j.succes ? '✓ Réussite' : '✗ Échec') + '  ' + j.total + ' vs ' + j.difficulte, j.succes ? 'ok' : 'err');
            if (j.complication) appendToConsole('⚠ Complication', 'warn');
            appendToConsole('Bonus dé: ' + j.dice_label, 'data');
        }

        appendToConsole('Formule: ' + data.scan.formula + ' = ' + data.scan.details, 'data');
        if (data.bonus) {
            const b = typeof data.bonus === 'object' ? data.bonus.formule + ' = ' + data.bonus.resultat : '+' + data.bonus;
            appendToConsole('Bonus: ' + b, 'data');
        }

        if (data.progres?.length) {
            const maxCumul = Math.max(...data.progres.map(p => p.cumul_apres));
            appendToConsole('Cumul: ' + maxCumul.toFixed(1), 'data');
            data.progres.forEach(p => {
                const cls = p.detecte ? 'ok' : (p.pourcentage > 75 ? 'warn' : (p.pourcentage > 25 ? '' : 'dim'));
                const det = p.nouveau_detecte ? ' 🎉' : (p.detecte ? ' ✓' : '');
                appendToConsole((p.detecte ? '  ✓ ' : '    ') + p.type + ': ' + p.nom + ' (' + p.pourcentage + '%)' + det, cls);
            });
        }

        if (data.objets_detectes?.length) {
            appendToConsole('🎉 ' + data.objets_detectes.length + ' nouvel(s) objet(s) !', 'ok');
            setTimeout(() => { saveConsoleContent(); location.reload(); }, 2000);
        }

        appendToConsole(String(data.progres ? data.progres.length : 0) + ' objet(s) scanné(s)', 'dim');
    } catch (err) { appendToConsole('[ERREUR] ' + err.message, 'err'); }
}
window.effectuerScan = effectuerScan;

// ── RESIZE ─────────────────────────────────────────────────────────────────────
// Le handle (#hud-resize-handle) est un flex-sibling du panel dans game-hud.blade.php
const _panel  = document.getElementById('hud-console-panel');
const _handle = document.getElementById('hud-resize-handle');
const _savedW = localStorage.getItem('hudConsoleWidth');
if (_savedW && _panel) _panel.style.width = _savedW + 'px';

let _resizing = false, _rx0 = 0, _rw0 = 0;
if (_handle && _panel) {
    _handle.addEventListener('mousedown', e => {
        _resizing = true; _rx0 = e.clientX; _rw0 = _panel.offsetWidth;
        _handle.classList.add('active');
        e.preventDefault();
        document.body.style.userSelect = 'none';
        document.body.style.cursor = 'ew-resize';
    });
}
document.addEventListener('mousemove', e => {
    if (!_resizing) return;
    const nw = Math.max(220, Math.min(600, _rw0 + (_rx0 - e.clientX)));
    if (_panel) _panel.style.width = nw + 'px';
});
document.addEventListener('mouseup', () => {
    if (!_resizing) return;
    _resizing = false;
    if (_handle) _handle.classList.remove('active');
    document.body.style.userSelect = '';
    document.body.style.cursor = '';
    if (_panel) localStorage.setItem('hudConsoleWidth', _panel.offsetWidth);
});

// ── updateGameInfo ─────────────────────────────────────────────────────────────
function updateGameInfo(data) {
    if (data.energie_actuelle !== undefined) {
        const pct = _enMax > 0 ? Math.round(data.energie_actuelle / _enMax * 100) : 0;
        const el = document.getElementById('hud-en');
        const bar = document.getElementById('hud-en-bar');
        if (el) el.textContent = pct + '%';
        if (bar) bar.style.width = pct + '%';
    }
    if (data.pa_restants !== undefined) {
        const el = document.getElementById('hud-pa');
        if (el) el.textContent = data.pa_restants + '/' + _paMax;
    }
    if (data.credits !== undefined) {
        const el = document.getElementById('hud-credits');
        if (el) el.textContent = data.credits.toLocaleString('fr-FR') + ' CR';
    }
}
window.updateGameInfo = updateGameInfo;

})();
</script>
