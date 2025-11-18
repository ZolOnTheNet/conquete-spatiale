<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Conquête Galactique - Console</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background: #000;
            color: #00ff00;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: #001100;
            border-bottom: 2px solid #00ff00;
            padding: 15px;
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            text-shadow: 0 0 10px #00ff00;
        }

        .main-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        .panel {
            border: 1px solid #00ff00;
            margin: 10px;
            padding: 10px;
            overflow-y: auto;
        }

        .panel-left {
            width: 20%;
        }

        .panel-center {
            width: 60%;
            display: flex;
            flex-direction: column;
        }

        .panel-right {
            width: 20%;
        }

        .output {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
            background: #001100;
            margin-bottom: 10px;
            white-space: pre-wrap;
            font-size: 14px;
        }

        .command-input {
            display: flex;
            gap: 5px;
            padding: 10px;
            background: #002200;
        }

        .command-input input {
            flex: 1;
            background: #000;
            border: 1px solid #00ff00;
            color: #00ff00;
            padding: 10px;
            font-family: 'Courier New', monospace;
            font-size: 16px;
        }

        .command-input input:focus {
            outline: none;
            box-shadow: 0 0 10px #00ff00;
        }

        .command-input button {
            background: #003300;
            border: 1px solid #00ff00;
            color: #00ff00;
            padding: 10px 20px;
            cursor: pointer;
            font-family: 'Courier New', monospace;
            font-size: 16px;
        }

        .command-input button:hover {
            background: #004400;
        }

        .prompt {
            color: #00ff00;
            font-weight: bold;
        }

        h3 {
            color: #00ff00;
            margin-bottom: 10px;
            text-align: center;
            border-bottom: 1px solid #00ff00;
            padding-bottom: 5px;
        }

        .stat-line {
            margin: 5px 0;
            font-size: 13px;
        }

        .stat-label {
            color: #00aa00;
        }

        .stat-value {
            color: #00ff00;
            font-weight: bold;
        }

        .progress-bar {
            width: 100%;
            height: 12px;
            background: #002200;
            border: 1px solid #00ff00;
            margin-top: 3px;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #004400, #00ff00);
            transition: width 0.3s ease;
        }

        .welcome-message {
            color: #00ff00;
            text-align: center;
            padding: 20px;
            border: 2px solid #00ff00;
            margin: 10px;
            background: #001100;
        }

        .system-list {
            margin-top: 10px;
            max-height: 300px;
            overflow-y: auto;
        }

        .system-item {
            padding: 5px;
            margin: 5px 0;
            background: #001100;
            border-left: 2px solid #00ff00;
            font-size: 12px;
        }

        .system-item:hover {
            background: #002200;
        }

        .loading {
            color: #00aa00;
            font-style: italic;
        }

        .panel-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .refresh-icon {
            cursor: pointer;
            font-size: 12px;
            color: #00aa00;
        }

        .refresh-icon:hover {
            color: #00ff00;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⭐ CONQUÊTE GALACTIQUE ⭐</h1>
        <p>Système Daggerheart 2D12 | Univers Procédural</p>
    </div>

    <div class="main-container">
        <!-- Panneau Gauche: Statut & Vaisseau -->
        <div class="panel panel-left">
            <div class="panel-title">
                <h3>STATUT</h3>
                <span class="refresh-icon" onclick="updateStatus()" title="Rafraîchir">🔄</span>
            </div>
            <div id="statusPanel" class="loading">Chargement...</div>

            <h3 style="margin-top: 20px;">VAISSEAU</h3>
            <div id="vaisseauPanel" class="loading">Chargement...</div>
        </div>

        <!-- Panneau Central: Console -->
        <div class="panel panel-center">
            <div class="output" id="output">
                <div class="welcome-message">
╔═══════════════════════════════════════════════════════════════╗
║          BIENVENUE DANS CONQUÊTE GALACTIQUE                    ║
║                                                                ║
║  Jeu d'exploration spatiale basé sur le système Daggerheart   ║
║  Interface Console AJAX - Version Alpha 0.2                   ║
╚═══════════════════════════════════════════════════════════════╝

Tapez 'help' ou 'aide' pour voir les commandes disponibles.
Tapez 'status' pour voir votre personnage.
Les panneaux se mettent à jour automatiquement toutes les 5 secondes.
                </div>
            </div>

            <div class="command-input">
                <span class="prompt">&gt;</span>
                <input type="text" id="commandInput" placeholder="Entrez votre commande..." autofocus>
                <button onclick="executeCommand()">ENVOYER</button>
            </div>
        </div>

        <!-- Panneau Droit: Carte Galactique -->
        <div class="panel panel-right">
            <div class="panel-title">
                <h3>CARTE GALACTIQUE</h3>
                <span class="refresh-icon" onclick="updateCarte()" title="Rafraîchir">🔄</span>
            </div>
            <div id="cartePanel" class="loading">Chargement...</div>
        </div>
    </div>

    <script>
        const outputDiv = document.getElementById('output');
        const commandInput = document.getElementById('commandInput');
        const statusPanel = document.getElementById('statusPanel');
        const vaisseauPanel = document.getElementById('vaisseauPanel');
        const cartePanel = document.getElementById('cartePanel');

        // Mise à jour automatique toutes les 5 secondes
        setInterval(() => {
            updateStatus();
            updateVaisseau();
            updateCarte();
        }, 5000);

        // Mise à jour initiale au chargement
        window.addEventListener('load', () => {
            updateStatus();
            updateVaisseau();
            updateCarte();
        });

        // === FONCTIONS AJAX ===

        function updateStatus() {
            fetch('/api/status')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const p = data.personnage;
                        const pa = data.pa;
                        const j = data.jetons;
                        const pos = data.position;

                        let html = '';
                        html += `<div class="stat-line"><span class="stat-label">Nom:</span> <span class="stat-value">${p.nom} ${p.prenom || ''}</span></div>`;
                        html += `<div class="stat-line"><span class="stat-label">Niveau:</span> <span class="stat-value">${p.niveau}</span> (${p.experience} XP)</div>`;
                        html += `<hr style="border-color: #00ff00; margin: 10px 0;">`;

                        html += `<div class="stat-line"><span class="stat-label">Points d'Action:</span></div>`;
                        html += `<div class="stat-line"><span class="stat-value">${pa.actuel} / ${pa.max} PA</span></div>`;
                        html += `<div class="progress-bar"><div class="progress-fill" style="width: ${(pa.actuel / pa.max) * 100}%"></div></div>`;

                        if (pa.prochaine_recup) {
                            html += `<div class="stat-line" style="font-size: 11px; color: #00aa00;">Prochain PA dans ${pa.prochaine_recup.minutes} min</div>`;
                        }

                        html += `<hr style="border-color: #00ff00; margin: 10px 0;">`;
                        html += `<div class="stat-line"><span class="stat-label">Hope:</span> <span class="stat-value">${j.hope}</span> | <span class="stat-label">Fear:</span> <span class="stat-value">${j.fear}</span></div>`;

                        if (pos) {
                            html += `<hr style="border-color: #00ff00; margin: 10px 0;">`;
                            html += `<div class="stat-line"><span class="stat-label">Position:</span></div>`;
                            html += `<div class="stat-line" style="font-size: 11px;">Secteur: (${pos.secteur_x}, ${pos.secteur_y}, ${pos.secteur_z})</div>`;
                        }

                        statusPanel.innerHTML = html;
                    }
                })
                .catch(error => {
                    statusPanel.innerHTML = `<div class="loading">Erreur de chargement</div>`;
                });
        }

        function updateVaisseau() {
            fetch('/api/vaisseau')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const v = data.vaisseau;
                        let html = '';

                        html += `<div class="stat-line"><span class="stat-label">Modèle:</span> <span class="stat-value">${v.modele}</span></div>`;
                        html += `<hr style="border-color: #00ff00; margin: 10px 0;">`;

                        html += `<div class="stat-line"><span class="stat-label">Énergie:</span></div>`;
                        html += `<div class="stat-line"><span class="stat-value">${v.energie.actuelle} / ${v.energie.max} UE</span></div>`;
                        html += `<div class="progress-bar"><div class="progress-fill" style="width: ${v.energie.pourcentage}%"></div></div>`;

                        html += `<hr style="border-color: #00ff00; margin: 10px 0;">`;
                        html += `<div class="stat-line"><span class="stat-label">Scanner:</span></div>`;
                        html += `<div class="stat-line" style="font-size: 11px;">Portée: ${v.scan.portee} AL</div>`;
                        html += `<div class="stat-line" style="font-size: 11px;">Puissance: ${v.scan.puissance_effective}</div>`;
                        if (v.scan.niveau_actuel > 0) {
                            html += `<div class="stat-line" style="font-size: 11px; color: #00ff00;">⚡ Scan actif: ${v.scan.niveau_actuel}</div>`;
                        }

                        vaisseauPanel.innerHTML = html;
                    }
                })
                .catch(error => {
                    vaisseauPanel.innerHTML = `<div class="loading">Pas de vaisseau actif</div>`;
                });
        }

        function updateCarte() {
            fetch('/api/carte')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = `<div class="stat-line"><span class="stat-label">Systèmes découverts:</span> <span class="stat-value">${data.total}</span></div>`;

                        if (data.systemes.length > 0) {
                            html += '<div class="system-list">';
                            data.systemes.forEach(sys => {
                                html += '<div class="system-item">';
                                html += `<strong>${sys.nom}</strong><br>`;
                                if (sys.distance_actuelle !== undefined) {
                                    html += `📍 ${sys.distance_actuelle} AL<br>`;
                                }
                                if (sys.type_etoile) {
                                    html += `⭐ ${sys.type_etoile} (${sys.couleur})<br>`;
                                }
                                if (sys.nb_planetes) {
                                    html += `🌍 ${sys.nb_planetes} planètes`;
                                    if (sys.habite) {
                                        html += ' (habité)';
                                    }
                                }
                                html += '</div>';
                            });
                            html += '</div>';
                        } else {
                            html += '<div class="stat-line" style="margin-top: 10px; color: #00aa00;">Aucun système découvert.</div>';
                            html += '<div class="stat-line" style="font-size: 11px;">Utilisez "scan" pour explorer</div>';
                        }

                        cartePanel.innerHTML = html;
                    }
                })
                .catch(error => {
                    cartePanel.innerHTML = `<div class="loading">Erreur de chargement</div>`;
                });
        }

        // === GESTION COMMANDES ===

        commandInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                executeCommand();
            }
        });

        function executeCommand() {
            const command = commandInput.value.trim();
            if (!command) return;

            addOutput(`\n<span class="prompt">&gt; ${command}</span>`);

            fetch('/command', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ command: command })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    addOutput(data.message);
                }
                if (!data.success && data.message) {
                    addOutput(`\n[ERREUR] ${data.message}`);
                }

                // Mettre à jour les panneaux après une commande
                setTimeout(() => {
                    updateStatus();
                    updateVaisseau();
                    updateCarte();
                }, 500);
            })
            .catch(error => {
                addOutput(`\n[ERREUR SYSTÈME] ${error.message}`);
            });

            commandInput.value = '';
        }

        function addOutput(text) {
            outputDiv.innerHTML += text;
            outputDiv.scrollTop = outputDiv.scrollHeight;
        }
    </script>
</body>
</html>
