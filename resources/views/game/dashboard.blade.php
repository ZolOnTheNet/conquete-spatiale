@extends('layouts.app')

@section('title', 'Conquete Spatiale - Interface')

@push('styles')
<style>
    .panel {
        background: rgba(15, 23, 42, 0.9);
        border: 1px solid rgba(74, 158, 255, 0.3);
    }

    .panel-header {
        background: rgba(74, 158, 255, 0.1);
        border-bottom: 1px solid rgba(74, 158, 255, 0.3);
    }

    .menu-item {
        transition: all 0.2s;
    }

    .menu-item:hover {
        background: rgba(74, 158, 255, 0.2);
    }

    .menu-item.active {
        background: rgba(74, 158, 255, 0.3);
        border-left: 3px solid #4a9eff;
    }

    .console-output {
        height: calc(100vh - 350px);
        overflow-y: auto;
    }

    .console-input {
        background: rgba(0, 0, 0, 0.5);
    }

    .shortcut-btn {
        background: rgba(74, 158, 255, 0.2);
        border: 1px solid rgba(74, 158, 255, 0.4);
        transition: all 0.2s;
    }

    .shortcut-btn:hover {
        background: rgba(74, 158, 255, 0.4);
        transform: translateY(-1px);
    }
</style>
@endpush

@section('content')
<div class="h-screen flex flex-col">
    <!-- Top Bar -->
    <header class="bg-gray-900/90 border-b border-cyan-500/30 px-4 py-2 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <h1 class="text-xl font-orbitron text-cyan-400">CONQUETE SPATIALE</h1>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-gray-400">
                {{ $personnage->prenom ?? '' }} {{ $personnage->nom }}
            </span>
            <span class="text-cyan-400 font-bold" id="pa-display">
                PA: {{ $personnage->points_action }}/{{ $personnage->max_points_action }}
            </span>
            <span class="text-yellow-400" id="credits-display">
                {{ number_format($personnage->credits) }} cr
            </span>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-gray-500 hover:text-red-400 text-sm">
                    Deconnexion
                </button>
            </form>
        </div>
    </header>

    <!-- Main 3-Column Layout -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Left Panel - Navigation -->
        <aside class="w-64 panel border-r flex flex-col">
            <div class="panel-header px-4 py-3">
                <h2 class="text-sm font-bold text-cyan-400">NAVIGATION</h2>
            </div>

            <nav class="flex-1 overflow-y-auto p-2">
                <!-- Lieu -->
                <div class="mb-4">
                    <h3 class="text-xs text-gray-500 uppercase px-2 mb-1">Lieu</h3>
                    <button class="menu-item w-full text-left px-3 py-2 rounded text-sm text-gray-300" onclick="sendCommand('position')">
                        Position
                    </button>
                    <button class="menu-item w-full text-left px-3 py-2 rounded text-sm text-gray-300" onclick="sendCommand('carte')">
                        Carte
                    </button>
                    <button class="menu-item w-full text-left px-3 py-2 rounded text-sm text-gray-300" onclick="sendCommand('scan')">
                        Scanner
                    </button>
                </div>

                <!-- Vaisseau -->
                <div class="mb-4">
                    <h3 class="text-xs text-gray-500 uppercase px-2 mb-1">Vaisseau</h3>
                    <button class="menu-item w-full text-left px-3 py-2 rounded text-sm text-gray-300" onclick="sendCommand('vaisseau')">
                        Pont
                    </button>
                    <button class="menu-item w-full text-left px-3 py-2 rounded text-sm text-gray-300" onclick="sendCommand('inventaire')">
                        Soute
                    </button>
                    <button class="menu-item w-full text-left px-3 py-2 rounded text-sm text-gray-300" onclick="sendCommand('etat-combat')">
                        Armement
                    </button>
                </div>

                <!-- Economie -->
                <div class="mb-4">
                    <h3 class="text-xs text-gray-500 uppercase px-2 mb-1">Economie</h3>
                    <button class="menu-item w-full text-left px-3 py-2 rounded text-sm text-gray-300" onclick="sendCommand('marche')">
                        Marche
                    </button>
                    <button class="menu-item w-full text-left px-3 py-2 rounded text-sm text-gray-300" onclick="sendCommand('recettes')">
                        Recettes
                    </button>
                </div>

                <!-- Combat -->
                <div class="mb-4">
                    <h3 class="text-xs text-gray-500 uppercase px-2 mb-1">Combat</h3>
                    <button class="menu-item w-full text-left px-3 py-2 rounded text-sm text-gray-300" onclick="sendCommand('armes')">
                        Armes
                    </button>
                    <button class="menu-item w-full text-left px-3 py-2 rounded text-sm text-gray-300" onclick="sendCommand('boucliers')">
                        Boucliers
                    </button>
                    <button class="menu-item w-full text-left px-3 py-2 rounded text-sm text-gray-300" onclick="sendCommand('ennemis')">
                        Encyclopedie
                    </button>
                </div>

                <!-- Admin (si admin) -->
                @if($compte->is_admin)
                <div class="mb-4 border-t border-cyan-500/20 pt-4">
                    <h3 class="text-xs text-red-400 uppercase px-2 mb-1">Administration</h3>
                    <a href="{{ route('admin.index') }}" class="menu-item block px-3 py-2 rounded text-sm text-red-300 hover:text-red-200">
                        Dashboard Admin
                    </a>
                    <a href="{{ route('admin.comptes') }}" class="menu-item block px-3 py-2 rounded text-sm text-red-300 hover:text-red-200">
                        Comptes
                    </a>
                    <a href="{{ route('admin.univers') }}" class="menu-item block px-3 py-2 rounded text-sm text-red-300 hover:text-red-200">
                        Univers
                    </a>
                    <a href="{{ route('admin.backup') }}" class="menu-item block px-3 py-2 rounded text-sm text-red-300 hover:text-red-200">
                        Backup
                    </a>
                </div>
                @endif
            </nav>

            <!-- Personnage Info -->
            <div class="p-4 border-t border-cyan-500/20">
                <div class="text-xs text-gray-500">Niveau {{ $personnage->niveau }}</div>
                <div class="text-sm text-gray-300">{{ $personnage->prenom ?? '' }} {{ $personnage->nom }}</div>
                <div class="text-xs text-gray-500 mt-1">XP: {{ $personnage->experience }}</div>
            </div>
        </aside>

        <!-- Center Panel - Main Display -->
        <main class="flex-1 panel flex flex-col">
            <div class="panel-header px-4 py-3 flex items-center justify-between">
                <h2 class="text-sm font-bold text-cyan-400">AFFICHAGE PRINCIPAL</h2>
                <span class="text-xs text-gray-500" id="current-view">Vue: Statut</span>
            </div>

            <div class="flex-1 p-4 overflow-y-auto" id="main-display">
                <!-- Contenu principal dynamique -->
                <div class="text-center text-gray-500 py-8">
                    <p class="mb-4">Bienvenue, {{ $personnage->prenom ?? '' }} {{ $personnage->nom }}</p>
                    <p class="text-sm">Utilisez les menus ou la console pour naviguer</p>
                    <p class="text-sm">Tapez <code class="text-cyan-400">help</code> pour voir les commandes</p>
                </div>

                @if($vaisseau)
                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div class="bg-gray-800/50 p-4 rounded">
                        <h3 class="text-sm text-cyan-400 mb-2">Vaisseau</h3>
                        <p class="text-white">{{ $vaisseau->nom }}</p>
                        <p class="text-xs text-gray-400">{{ $vaisseau->modele ?? 'Scout' }}</p>
                    </div>
                    <div class="bg-gray-800/50 p-4 rounded">
                        <h3 class="text-sm text-cyan-400 mb-2">Position</h3>
                        <p class="text-white">{{ $vaisseau->coord_x }}, {{ $vaisseau->coord_y }}, {{ $vaisseau->coord_z }}</p>
                        <p class="text-xs text-gray-400">Secteur 0,0,0</p>
                    </div>
                </div>
                @endif
            </div>
        </main>

        <!-- Right Panel - Console -->
        <aside class="w-96 panel border-l flex flex-col">
            <div class="panel-header px-4 py-3">
                <h2 class="text-sm font-bold text-cyan-400">CONSOLE</h2>
            </div>

            <!-- Console Output -->
            <div class="console-output flex-1 p-4 font-mono text-sm" id="console-output">
                <div class="text-cyan-400">> Systeme initialise</div>
                <div class="text-gray-300">> Connexion etablie</div>
                <div class="text-gray-300">> Tapez 'help' pour l'aide</div>
                <div class="text-gray-500">---</div>
            </div>

            <!-- Console Input -->
            <div class="p-4 border-t border-cyan-500/20">
                <form id="command-form" class="flex gap-2">
                    <span class="text-cyan-400">></span>
                    <input type="text" id="command-input"
                           class="flex-1 bg-transparent border-none outline-none text-white font-mono"
                           placeholder="Entrez une commande..."
                           autocomplete="off">
                </form>
            </div>

            <!-- Shortcut Buttons -->
            <div class="p-4 border-t border-cyan-500/20 grid grid-cols-3 gap-2">
                <button onclick="sendCommand('scan')" class="shortcut-btn px-2 py-1 rounded text-xs text-cyan-300">
                    Scan
                </button>
                <button onclick="sendCommand('saut')" class="shortcut-btn px-2 py-1 rounded text-xs text-cyan-300">
                    Saut
                </button>
                <button onclick="sendCommand('scanner-ennemis')" class="shortcut-btn px-2 py-1 rounded text-xs text-cyan-300">
                    Combat
                </button>
                <button onclick="sendCommand('marche')" class="shortcut-btn px-2 py-1 rounded text-xs text-cyan-300">
                    Marche
                </button>
                <button onclick="sendCommand('inventaire')" class="shortcut-btn px-2 py-1 rounded text-xs text-cyan-300">
                    Inv
                </button>
                <button onclick="sendCommand('help')" class="shortcut-btn px-2 py-1 rounded text-xs text-cyan-300">
                    Aide
                </button>
            </div>
        </aside>
    </div>
</div>

@push('scripts')
<script>
    const commandInput = document.getElementById('command-input');
    const commandForm = document.getElementById('command-form');
    const consoleOutput = document.getElementById('console-output');
    const mainDisplay = document.getElementById('main-display');

    // Historique des commandes
    let commandHistory = [];
    let historyIndex = -1;

    // Focus sur l'input au chargement
    commandInput.focus();

    // Gestion du formulaire
    commandForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const command = commandInput.value.trim();
        if (command) {
            sendCommand(command);
            commandHistory.unshift(command);
            historyIndex = -1;
            commandInput.value = '';
        }
    });

    // Navigation dans l'historique
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

    // Envoyer une commande
    function sendCommand(command) {
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
                // Formater et afficher la reponse
                const lines = data.message.split('\n');
                lines.forEach(line => {
                    if (line.trim()) {
                        const colorClass = data.success ? 'text-gray-300' : 'text-red-400';
                        appendToConsole(line, colorClass);
                    }
                });
            }

            // Mettre a jour les stats si disponibles
            updateStats();
        })
        .catch(error => {
            appendToConsole('[ERREUR] ' + error.message, 'text-red-400');
        });
    }

    // Ajouter du texte a la console
    function appendToConsole(text, colorClass = 'text-gray-300') {
        const div = document.createElement('div');
        div.className = colorClass;
        div.textContent = text;
        consoleOutput.appendChild(div);
        consoleOutput.scrollTop = consoleOutput.scrollHeight;
    }

    // Mettre a jour les stats
    function updateStats() {
        fetch('{{ route("api.status") }}', {
            headers: {
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('pa-display').textContent =
                    'PA: ' + data.pa.actuel + '/' + data.pa.max;
            }
        })
        .catch(() => {});
    }

    // Rafraichir les stats periodiquement
    setInterval(updateStats, 60000);
</script>
@endpush
@endsection
