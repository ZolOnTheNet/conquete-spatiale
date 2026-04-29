{{-- Console Droite --}}
<aside id="game-console" class="w-96 bg-gray-900/90 border-l border-cyan-500/30 flex flex-col relative" style="min-width: 20rem; max-width: 50rem;">
    <!-- Poignée de redimensionnement -->
    <div id="console-resize-handle"
         class="absolute left-0 top-0 bottom-0 w-1 bg-cyan-500/20 hover:bg-cyan-400 cursor-ew-resize transition-colors z-10"
         title="Glisser pour redimensionner la console">
    </div>

    <div class="bg-gray-800/50 border-b border-cyan-500/30 px-4 py-3">
        <h2 class="text-sm font-bold text-cyan-400">CONSOLE</h2>
        <div class="flex items-center gap-1">
            <button onclick="adjustFontSize(-1)" class="text-xs px-2 py-1 bg-gray-700 hover:bg-gray-600 rounded transition" title="Diminuer la taille de police">-</button>
            <button onclick="adjustFontSize(1)" class="text-xs px-2 py-1 bg-gray-700 hover:bg-gray-600 rounded transition" title="Augmenter la taille de police">+</button>
        </div>
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
    <div class="p-4 border-t border-cyan-500/20 bg-gray-900 space-y-2">
        <div class="grid grid-cols-3 gap-2">
            <button onclick="effectuerScan('simple')"
                    class="bg-purple-600/80 hover:bg-purple-600 border border-purple-500/50 px-2 py-1.5 rounded text-xs text-white transition"
                    title="Scan simple: uniquement les dés du scanner">
                📡 Scan
            </button>
            <button onclick="effectuerScan('reglage')"
                    class="bg-cyan-600/80 hover:bg-cyan-600 border border-cyan-500/50 px-2 py-1.5 rounded text-xs text-white transition"
                    title="Scan + jet de Finesse pour bonus">
                🔧 Scan & Réglage
            </button>
            <button onclick="effectuerScan('astro')"
                    class="bg-blue-600/80 hover:bg-blue-600 border border-blue-500/50 px-2 py-1.5 rounded text-xs text-white transition"
                    title="Scan + jet de Savoir pour bonus">
                🌟 Scan & Astro
            </button>
        </div>
        <div class="grid grid-cols-3 gap-2">
            <button onclick="sendQuickCommand('help')" class="bg-cyan-900/30 hover:bg-cyan-900/50 border border-cyan-500/30 px-2 py-1 rounded text-xs text-cyan-300 transition" title="Afficher l'aide">
                Aide
            </button>
            <button onclick="sendQuickCommand('history')" class="bg-purple-900/30 hover:bg-purple-900/50 border border-purple-500/30 px-2 py-1 rounded text-xs text-purple-300 transition" title="Afficher l'historique des commandes">
                History
            </button>
            <button onclick="sendQuickCommand('clear')" class="bg-gray-800/50 hover:bg-gray-700 border border-gray-600 px-2 py-1 rounded text-xs text-gray-400 transition" title="Effacer la console (Ctrl+L)">
                Clear
            </button>
        </div>
    </div>
</aside>

@push('scripts')
<script>
const commandInput = document.getElementById('command-input');
const commandForm = document.getElementById('command-form');
const consoleOutput = document.getElementById('console-output');

// Historique des commandes (sauvegardé dans localStorage)
let commandHistory = JSON.parse(localStorage.getItem('commandHistory') || '[]');
let historyIndex = -1;
let tempCommand = ''; // Commande en cours d'édition

// Restaurer le contenu de la console
function restoreConsoleContent() {
    const savedContent = localStorage.getItem('consoleContent');
    if (savedContent && consoleOutput) {
        consoleOutput.innerHTML = savedContent;
        // Forcer le scroll en bas après un court délai pour s'assurer que le DOM est rendu
        setTimeout(() => {
            consoleOutput.scrollTop = consoleOutput.scrollHeight;
        }, 100);
    }
}

// Fonction pour scroller automatiquement en bas
function scrollConsoleToBottom() {
    if (consoleOutput) {
        consoleOutput.scrollTop = consoleOutput.scrollHeight;
    }
}

// Sauvegarder le contenu de la console
function saveConsoleContent() {
    if (consoleOutput) {
        localStorage.setItem('consoleContent', consoleOutput.innerHTML);
    }
}

// Restaurer le contenu au chargement
restoreConsoleContent();

// Liste de commandes pour autocomplétion
const availableCommands = [
    'help', 'aide', 'status', 'statut', 'position', 'pos', 'vaisseau', 'ship',
    'deplacer', 'move', 'bouger', 'saut', 'jump', 'scan', 'scanner',
    'carte', 'map', 'inventaire', 'inv', 'marche', 'market',
    'acheter', 'buy', 'vendre', 'sell', 'prix', 'prices',
    'armes', 'weapons', 'boucliers', 'shields', 'equiper', 'equip',
    'arrimer', 'dock', 'desarrimer', 'undock', 'clear'
];

// Focus sur l'input au chargement
if (commandInput) {
    commandInput.focus();

    // Refocus automatique si on clique dans la console
    if (consoleOutput) {
        consoleOutput.addEventListener('click', () => commandInput.focus());
    }

    // Gestion des raccourcis clavier
    commandInput.addEventListener('keydown', function(e) {
        // Ctrl+L : Clear console
        if (e.ctrlKey && e.key === 'l') {
            e.preventDefault();
            consoleOutput.innerHTML = '<div class="text-gray-500">Console effacée (Ctrl+L)</div>';
            saveConsoleContent(); // Sauvegarder après effacement
            return;
        }

        // Ctrl+C : Vider la ligne en cours
        if (e.ctrlKey && e.key === 'c') {
            e.preventDefault();
            commandInput.value = '';
            historyIndex = -1;
            tempCommand = '';
            appendToConsole('> ^C', 'text-gray-500');
            return;
        }

        // Tab : Autocomplétion
        if (e.key === 'Tab') {
            e.preventDefault();
            const currentValue = commandInput.value.toLowerCase();
            if (currentValue) {
                const matches = availableCommands.filter(cmd => cmd.startsWith(currentValue));
                if (matches.length === 1) {
                    commandInput.value = matches[0] + ' ';
                } else if (matches.length > 1) {
                    appendToConsole('Suggestions: ' + matches.join(', '), 'text-yellow-400');
                }
            }
            return;
        }

        // Flèche haut : Commande précédente
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (historyIndex === -1) {
                tempCommand = commandInput.value; // Sauvegarder la commande en cours
            }
            if (historyIndex < commandHistory.length - 1) {
                historyIndex++;
                commandInput.value = commandHistory[historyIndex];
            }
            return;
        }

        // Flèche bas : Commande suivante
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (historyIndex > 0) {
                historyIndex--;
                commandInput.value = commandHistory[historyIndex];
            } else if (historyIndex === 0) {
                historyIndex = -1;
                commandInput.value = tempCommand; // Restaurer la commande en cours
            }
            return;
        }
    });
}

// Ajouter une commande à l'historique
function addToHistory(command) {
    // Ajouter à l'historique (éviter les doublons consécutifs)
    if (commandHistory.length === 0 || commandHistory[0] !== command) {
        commandHistory.unshift(command);
        // Limiter l'historique à 100 commandes
        if (commandHistory.length > 100) {
            commandHistory = commandHistory.slice(0, 100);
        }
        // Sauvegarder dans localStorage
        localStorage.setItem('commandHistory', JSON.stringify(commandHistory));
    }
}

// Envoyer une commande
function sendCommand(commandOrEvent) {
    // Gérer les deux formats: commande en string ou événement
    let command;
    if (typeof commandOrEvent === 'string') {
        command = commandOrEvent.trim();
    } else {
        // C'est un événement
        const event = commandOrEvent;
        if (event) event.preventDefault();
        command = commandInput ? commandInput.value.trim() : '';
        if (!command) return;
    }

    // Ajouter à l'historique seulement pour les commandes depuis l'input
    if (typeof commandOrEvent !== 'string') {
        commandHistory.unshift(command);
        historyIndex = -1;
    }

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
                    // Utiliser appendToConsoleWithColors pour parser les marqueurs [T] et [C]
                    appendToConsoleWithColors(line, colorClass);
                }
            });
        }

        // Mettre à jour les informations si disponibles
        updateGameInfo(data);

        // Rafraîchir l'interface si nécessaire
        if (data.refresh_ui) {
            setTimeout(() => {
                saveConsoleContent();
                window.location.reload();
            }, 1000);
        }
    })
    .catch(error => {
        appendToConsole('[ERREUR] ' + error.message, 'text-red-400');
    });

    // Vider l'input seulement pour les commandes depuis l'input
    if (typeof commandOrEvent !== 'string' && commandInput) {
        commandInput.value = '';
    }
}

// Commande rapide (boutons)
function sendQuickCommand(cmd) {
    if (cmd === 'clear') {
        consoleOutput.innerHTML = '<div class="text-gray-500">Console effacée</div>';
        saveConsoleContent(); // Sauvegarder après effacement
        return;
    }

    if (cmd === 'history') {
        addToHistory('history'); // Ajouter à l'historique
        appendToConsole('> history', 'text-cyan-400');
        appendToConsole('=== HISTORIQUE DES COMMANDES ===', 'text-cyan-400');
        if (commandHistory.length === 0) {
            appendToConsole('Aucune commande dans l\'historique', 'text-gray-500');
        } else {
            commandHistory.slice().reverse().forEach((c, i) => {
                appendToConsole(`${i + 1}. ${c}`, 'text-gray-300');
            });
        }
        return;
    }

    if (cmd === 'clear-history') {
        commandHistory = [];
        localStorage.removeItem('commandHistory');
        historyIndex = -1;
        tempCommand = '';
        appendToConsole('Historique effacé', 'text-yellow-400');
        return;
    }

    // Ajouter la commande à l'historique avant de l'envoyer
    addToHistory(cmd);

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

    // Sauvegarder l'historique
    if (typeof saveConsoleHistory === 'function') {
        saveConsoleHistory();
    }

    scrollConsoleToBottom();
    saveConsoleContent();
}

// Ajouter du texte avec parsing des couleurs
function appendToConsoleWithColors(text, defaultColorClass = 'text-gray-300') {
    if (!consoleOutput) return;

    const div = document.createElement('div');
    div.className = 'leading-relaxed';

    // Parser les marqueurs [T]...[/T] et [C]...[/C]
    const regex = /\[T\](.*?)\[\/T\]|\[C\](.*?)\[\/C\]/g;
    let lastIndex = 0;
    let match;

    while ((match = regex.exec(text)) !== null) {
        // Ajouter le texte avant le marqueur
        if (match.index > lastIndex) {
            const textNode = document.createElement('span');
            textNode.className = defaultColorClass;
            textNode.textContent = text.substring(lastIndex, match.index);
            div.appendChild(textNode);
        }

        // Ajouter le texte coloré
        const coloredNode = document.createElement('span');
        if (match[1] !== undefined) {
            // [T] = Titre (bleu-vert / cyan)
            coloredNode.className = 'text-cyan-400 font-semibold';
            coloredNode.textContent = match[1];
        } else if (match[2] !== undefined) {
            // [C] = Commande (jaune)
            coloredNode.className = 'text-yellow-400';
            coloredNode.textContent = match[2];
        }
        div.appendChild(coloredNode);

        lastIndex = regex.lastIndex;
    }

    // Ajouter le texte restant
    if (lastIndex < text.length) {
        const textNode = document.createElement('span');
        textNode.className = defaultColorClass;
        textNode.textContent = text.substring(lastIndex);
        div.appendChild(textNode);
    }

    consoleOutput.appendChild(div);
    scrollConsoleToBottom(); // Scroll automatique en bas
    saveConsoleContent(); // Sauvegarder après chaque ajout
}

// ============================================================================
// SYSTÈME DE SCAN
// ============================================================================

async function effectuerScan(type) {
    const routes = {
        'simple': '{{ route("navire.scan.simple") }}',
        'reglage': '{{ route("navire.scan.reglage") }}',
        'astro': '{{ route("navire.scan.astro") }}'
    };

    const route = routes[type];
    if (!route) {
        appendToConsole('[ERREUR] Type de scan invalide', 'text-red-400');
        return;
    }

    // Ajouter la commande de scan à l'historique
    const scanCommands = {
        'simple': 'scan',
        'reglage': 'scan reglage',
        'astro': 'scan astro'
    };
    addToHistory(scanCommands[type]);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    try {
        appendToConsole('> ' + scanCommands[type], 'text-cyan-400');

        const response = await fetch(route, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (!data.success) {
            appendToConsole('[ERREUR] ' + (data.message || 'Erreur lors du scan'), 'text-red-400');
            return;
        }

        // Titre selon le type
        const titres = {
            'simple': '📡 SCAN SIMPLE',
            'reglage': '🔧 SCAN + RÉGLAGE',
            'astro': '🌟 SCAN + ASTRO'
        };
        appendToConsole('═══════════════════════════════', 'text-purple-400');
        appendToConsole(titres[type], 'text-purple-400');
        appendToConsole('═══════════════════════════════', 'text-purple-400');

        // Jet de compétence (si applicable)
        if (data.jet_competence) {
            const jet = data.jet_competence;
            appendToConsole('', 'text-gray-500');
            appendToConsole('JET DE COMPÉTENCE:', 'text-cyan-300');
            const resultat = jet.succes ? '✓ Réussite' : '✗ Échec';
            const couleur = jet.succes ? 'text-green-400' : 'text-red-400';
            appendToConsole(resultat, couleur);
            appendToConsole(`Jet: ${jet.espoir} (Espoir) + ${jet.peur} (Peur) + ${jet.modificateur} = ${jet.total} vs ${jet.difficulte}`, 'text-gray-300');
            appendToConsole(`Marge: ${jet.marge >= 0 ? '+' : ''}${jet.marge}`, 'text-gray-300');
            appendToConsole(`Dé bonus: ${jet.dice_label}`, 'text-cyan-400');
            if (jet.complication) {
                appendToConsole('⚠️ Complication (Peur > Espoir)', 'text-yellow-400');
            }
        }

        // Résultat du scan
        appendToConsole('', 'text-gray-500');
        appendToConsole('SCAN:', 'text-cyan-300');
        appendToConsole(`Formule: ${data.scan.formula}`, 'text-purple-400');
        appendToConsole(`Détails: ${data.scan.details}`, 'text-gray-300');

        // Afficher le bonus de compétence si présent
        if (data.bonus && typeof data.bonus === 'object') {
            appendToConsole(`Bonus: ${data.bonus.formule} = ${data.bonus.resultat}`, 'text-cyan-400');
        } else if (data.bonus !== null && data.bonus !== undefined && data.bonus !== 0) {
            appendToConsole(`Bonus: ${data.bonus > 0 ? '+' : ''}${data.bonus}`, 'text-gray-300');
        }

        const totalJet = data.total || data.scan.total;
        appendToConsole(`Total du jet : ${totalJet}`, 'text-purple-300');

        // Afficher le CUMUL (maximum cumul_apres)
        if (data.progres && data.progres.length > 0) {
            const maxCumul = Math.max(...data.progres.map(p => p.cumul_apres));
            appendToConsole(`CUMUL: ${maxCumul.toFixed(1)}`, 'text-cyan-300');
        }

        // Objets détectés
        if (data.objets_detectes && data.objets_detectes.length > 0) {
            appendToConsole('', 'text-gray-500');
            appendToConsole('🎉 NOUVEAUX OBJETS DÉTECTÉS !', 'text-green-400');
            data.objets_detectes.forEach(obj => {
                appendToConsole(`• ${obj.type}: ${obj.nom}`, 'text-green-300');
            });
        }

        // Afficher TOUS les objets scannés avec leurs scores
        if (data.progres && data.progres.length > 0) {
            appendToConsole('', 'text-gray-500');
            appendToConsole('OBJETS SCANNÉS:', 'text-cyan-300');

            data.progres.forEach(p => {
                const couleur = p.detecte ? 'text-green-400' :
                              p.pourcentage > 75 ? 'text-yellow-400' :
                              p.pourcentage > 25 ? 'text-orange-400' : 'text-gray-400';
                const detecte = p.nouveau_detecte ? ' 🎉 DÉTECTÉ!' : '';
                const etat = p.detecte ? ' ✓' : '';

                // Format: Type: Nom score_requis (pourcentage%)
                appendToConsole(
                    `${p.type}: ${p.nom} ${p.score_requis.toFixed(1)} (${p.pourcentage}%)${etat}${detecte}`,
                    couleur
                );
            });
        }

        appendToConsole('', 'text-gray-500');
        appendToConsole(`${data.progres ? data.progres.length : 0} objet(s) scanné(s)`, 'text-gray-500');
        appendToConsole('═══════════════════════════════', 'text-purple-400');

        // Rafraîchir l'interface si de nouveaux objets sont détectés
        if (data.objets_detectes && data.objets_detectes.length > 0) {
            appendToConsole('', 'text-gray-500');
            appendToConsole('Rafraîchissement de l\'interface...', 'text-cyan-400');
            setTimeout(() => {
                saveConsoleContent(); // Sauvegarder avant le refresh
                window.location.reload();
            }, 2000); // Délai pour voir les résultats
        }

    } catch (error) {
        appendToConsole('[ERREUR] ' + error.message, 'text-red-400');
    }
}

// ============================================================================
// SYSTÈME DE REDIMENSIONNEMENT DE LA CONSOLE
// ============================================================================

const gameConsole = document.getElementById('game-console');
const resizeHandle = document.getElementById('console-resize-handle');

// Charger la largeur sauvegardée
const savedWidth = localStorage.getItem('consoleWidth');
if (savedWidth && gameConsole) {
    gameConsole.style.width = savedWidth + 'px';
}

// Variables pour le drag
let isResizing = false;
let startX = 0;
let startWidth = 0;

if (resizeHandle && gameConsole) {
    resizeHandle.addEventListener('mousedown', function(e) {
        isResizing = true;
        startX = e.clientX;
        startWidth = gameConsole.offsetWidth;

        // Empêcher la sélection de texte pendant le drag
        e.preventDefault();
        document.body.style.userSelect = 'none';
        document.body.style.cursor = 'ew-resize';
    });

    document.addEventListener('mousemove', function(e) {
        if (!isResizing) return;

        // Calculer la nouvelle largeur (on drag vers la gauche pour agrandir)
        const deltaX = startX - e.clientX;
        const newWidth = startWidth + deltaX;

        // Appliquer les contraintes min/max (20rem = 320px, 50rem = 800px)
        const minWidth = 320;
        const maxWidth = 800;

        if (newWidth >= minWidth && newWidth <= maxWidth) {
            gameConsole.style.width = newWidth + 'px';
        }
    });

    document.addEventListener('mouseup', function() {
        if (isResizing) {
            isResizing = false;
            document.body.style.userSelect = '';
            document.body.style.cursor = '';

            // Sauvegarder la nouvelle largeur
            localStorage.setItem('consoleWidth', gameConsole.offsetWidth);
        }
    });
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
