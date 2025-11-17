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
        }

        .welcome-message {
            color: #00ff00;
            text-align: center;
            padding: 20px;
            border: 2px solid #00ff00;
            margin: 10px;
            background: #001100;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⭐ CONQUÊTE GALACTIQUE ⭐</h1>
        <p>Système Daggerheart 2D12 | Univers Procédural</p>
    </div>

    <div class="main-container">
        <!-- Panneau Gauche: Navigation -->
        <div class="panel panel-left">
            <h3>NAVIGATION</h3>
            <div class="stat-line">► Position</div>
            <div class="stat-line">► Vaisseau</div>
            <div class="stat-line">► Personnage</div>
            <div class="stat-line">► Inventaire</div>
            <div class="stat-line">► Carte</div>
        </div>

        <!-- Panneau Central: Console -->
        <div class="panel panel-center">
            <div class="output" id="output">
                <div class="welcome-message">
╔═══════════════════════════════════════════════════════════════╗
║          BIENVENUE DANS CONQUÊTE GALACTIQUE                    ║
║                                                                ║
║  Jeu d'exploration spatiale basé sur le système Daggerheart   ║
║  Interface Console - Version Alpha 0.1                        ║
╚═══════════════════════════════════════════════════════════════╝

Tapez 'help' ou 'aide' pour voir les commandes disponibles.
Tapez 'status' pour voir votre personnage.
                </div>
            </div>

            <div class="command-input">
                <span class="prompt">&gt;</span>
                <input type="text" id="commandInput" placeholder="Entrez votre commande..." autofocus>
                <button onclick="executeCommand()">ENVOYER</button>
            </div>
        </div>

        <!-- Panneau Droit: Info Contextuelle -->
        <div class="panel panel-right">
            <h3>INFO</h3>
            <div class="stat-line">Secteur: (0, 0, 0)</div>
            <div class="stat-line">PA restants: --</div>
            <div class="stat-line">Énergie: --</div>
            <div class="stat-line">Hope: --</div>
            <div class="stat-line">Fear: --</div>
        </div>
    </div>

    <script>
        const outputDiv = document.getElementById('output');
        const commandInput = document.getElementById('commandInput');

        // Gérer l'entrée avec la touche Enter
        commandInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                executeCommand();
            }
        });

        function executeCommand() {
            const command = commandInput.value.trim();
            if (!command) return;

            // Afficher la commande
            addOutput(`\n<span class="prompt">&gt; ${command}</span>`);

            // Envoyer la commande au serveur
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
            })
            .catch(error => {
                addOutput(`\n[ERREUR SYSTÈME] ${error.message}`);
            });

            // Vider l'input
            commandInput.value = '';
        }

        function addOutput(text) {
            outputDiv.innerHTML += text;
            // Scroll vers le bas
            outputDiv.scrollTop = outputDiv.scrollHeight;
        }
    </script>
</body>
</html>
