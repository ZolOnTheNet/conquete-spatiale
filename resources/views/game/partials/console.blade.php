{{-- Console Droite --}}
<aside class="w-96 bg-gray-900/90 border-l border-cyan-500/30 flex flex-col">
    <div class="bg-gray-800/50 border-b border-cyan-500/30 px-4 py-3">
        <h2 class="text-sm font-bold text-cyan-400">CONSOLE</h2>
    </div>
    <style>
        .command-text {
            color: #FF8C00;
        }
    </style>

    <!-- Console Output -->
    <div class="flex-1 p-4 font-mono text-sm overflow-y-auto bg-black" id="console-output">
        <div class="text-cyan-400">> Systeme initialise</div>
        <div class="text-gray-300">> Connexion etablie</div>
        <div class="text-gray-300">> Tapez 'help' pour l'aide</div>
        <div class="text-gray-500">---</div>
    </div>

    <!-- Console Input -->
    <div class="p-4 border-t border-cyan-500/20 bg-gray-900">
        <form id="command-form" class="flex gap-2" onsubmit="sendCommand(event)">
            <span class="text-cyan-400">></span>
            <input type="text" id="command-input"
                   class="flex-1 bg-transparent border-none outline-none text-white font-mono text-sm"
                   placeholder="Entrez une commande..."
                   autocomplete="off">
        </form>
    </div>

    <!-- Shortcut Buttons -->
    <div class="p-4 border-t border-cyan-500/20 bg-gray-900 grid grid-cols-3 gap-2">
        <button onclick="sendQuickCommand('scan')" class="bg-cyan-900/30 hover:bg-cyan-900/50 border border-cyan-500/30 px-2 py-1 rounded text-xs text-cyan-300 transition">
            Scan
        </button>
        <button onclick="sendQuickCommand('help')" class="bg-cyan-900/30 hover:bg-cyan-900/50 border border-cyan-500/30 px-2 py-1 rounded text-xs text-cyan-300 transition">
            Aide
        </button>
        <button onclick="sendQuickCommand('clear')" class="bg-gray-800/50 hover:bg-gray-700 border border-gray-600 px-2 py-1 rounded text-xs text-gray-400 transition">
            Clear
        </button>
    </div>
</aside>

@push('scripts')
<script>
const commandInput = document.getElementById('command-input');
const commandForm = document.getElementById('command-form');
const consoleOutput = document.getElementById('console-output');

// Historique des commandes
let commandHistory = [];
let historyIndex = -1;

// Focus sur l'input au chargement
if (commandInput) {
    commandInput.focus();

    // Navigation dans l'historique avec flèches
    commandInput.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (historyIndex < commandHistory.length - 1) {
                historyIndex++;
                commandInput.value = commandHistory[historyIndex];
            }
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (historyIndex > 0) {
                historyIndex--;
                commandInput.value = commandHistory[historyIndex];
            } else {
                historyIndex = -1;
                commandInput.value = '';
            }
        }
    });
}

// Envoyer une commande
function sendCommand(event) {
    if (event) event.preventDefault();

    const command = commandInput ? commandInput.value.trim() : '';
    if (!command) return;

    // Ajouter à l'historique
    commandHistory.unshift(command);
    historyIndex = -1;

    // Afficher la commande
    appendToConsole('> ' + command, 'text-cyan-400');

    // Envoyer au serveur
    fetch('{{ route("command") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ command: command })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            const lines = data.message.split('\n');
            lines.forEach(line => {
                if (line.trim()) {
                    const colorClass = data.success ? 'text-gray-300' : 'text-red-400';
                    appendToConsole(line, colorClass);
                }
            });
        }
        
        // Mettre à jour les informations si disponibles
        updateGameInfo(data);
    })
    .catch(error => {
        appendToConsole('[ERREUR] ' + error.message, 'text-red-400');
    });

    // Vider l'input
    if (commandInput) commandInput.value = '';
}

// Commande rapide (boutons)
function sendQuickCommand(cmd) {
    if (cmd === 'clear') {
        consoleOutput.innerHTML = '<div class="text-gray-500">Console effacée</div>';
        return;
    }

    if (commandInput) {
        commandInput.value = cmd;
        sendCommand();
    }
}

// Ajouter du texte à la console
function appendToConsole(text, colorClass = 'text-gray-300') {
    if (!consoleOutput) return;

    const div = document.createElement('div');
    div.className = colorClass;
    
    // Vérifier si le texte contient des balises HTML
    if (text.includes('<span') && text.includes('style=')) {
        div.innerHTML = text;
    } else {
        div.textContent = text;
    }
    
    consoleOutput.appendChild(div);
    consoleOutput.scrollTop = consoleOutput.scrollHeight;
}

// Mettre à jour les informations du vaisseau et du personnage
function updateGameInfo(data) {
    // Mettre à jour l'énergie du vaisseau
    if (data.energie_actuelle !== undefined) {
        const energieElement = document.querySelector('.ship-stats-compact .stat-item:nth-child(1)');
        if (energieElement) {
            const energieMax = parseFloat(energieElement.getAttribute('data-max')) || 1000;
            energieElement.textContent = '⚡ ' + Math.round(data.energie_actuelle / energieMax * 100) + '%';
        }
    }
    
    // Mettre à jour les PA du personnage
    if (data.pa_restants !== undefined) {
        const paElement = document.querySelector('.player-actions');
        if (paElement) {
            paElement.textContent = '⚡ PA: ' + data.pa_restants;
        }
    }
    
    // Mettre à jour les crédits du personnage
    if (data.credits !== undefined) {
        const creditsElement = document.querySelector('.player-credits');
        if (creditsElement) {
            creditsElement.textContent = '💰 ' + data.credits + ' CR';
        }
    }
    
    // Mettre à jour la position du vaisseau
    if (data.position) {
        const positionElement = document.querySelector('.system-coords');
        if (positionElement) {
            positionElement.innerHTML = data.position;
        }
    }
}
</script>
@endpush
