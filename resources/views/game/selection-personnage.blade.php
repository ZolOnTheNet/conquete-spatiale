<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sélection des personnages - Conquête Galactique</title>
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            width: 100%;
            border: 2px solid #00ff00;
            padding: 30px;
            background: #001100;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            text-shadow: 0 0 10px #00ff00;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #00ff00;
            background: #002200;
        }

        .message.success {
            border-color: #00ff00;
            color: #00ff00;
        }

        .message.error {
            border-color: #ff0000;
            color: #ff0000;
        }

        .section {
            margin-bottom: 40px;
        }

        .section h2 {
            margin-bottom: 20px;
            color: #00ff00;
            border-bottom: 1px solid #00ff00;
            padding-bottom: 10px;
        }

        .personnages-list {
            display: grid;
            gap: 15px;
        }

        .personnage-card {
            border: 1px solid #00ff00;
            padding: 15px;
            background: #000;
            transition: all 0.3s;
        }

        .personnage-card:hover {
            background: #002200;
            box-shadow: 0 0 10px #00ff00;
        }

        .personnage-card.active {
            border-color: #ffff00;
            background: #003300;
            border-width: 2px;
        }

        .personnage-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .personnage-stats {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .button-group {
            display: flex;
            gap: 10px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-row {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .form-row .form-group {
            flex: 1;
        }

        label {
            color: #00ff00;
            font-size: 14px;
        }

        input[type="text"], select {
            background: #000;
            border: 1px solid #00ff00;
            color: #00ff00;
            padding: 10px;
            font-family: 'Courier New', monospace;
            font-size: 16px;
        }

        input[type="text"]:focus, select:focus {
            outline: none;
            box-shadow: 0 0 10px #00ff00;
        }

        button, .btn {
            background: #003300;
            border: 1px solid #00ff00;
            color: #00ff00;
            padding: 12px 24px;
            cursor: pointer;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        button:hover, .btn:hover {
            background: #004400;
            box-shadow: 0 0 10px #00ff00;
        }

        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-primary {
            background: #004400;
            border-color: #00ff00;
        }

        .btn-secondary {
            background: #003300;
            border-color: #888;
            color: #888;
        }

        .btn-secondary:hover {
            border-color: #aaa;
            color: #aaa;
        }

        .btn-small {
            padding: 8px 16px;
            font-size: 14px;
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            color: #888;
        }

        select option {
            background: #000;
            color: #00ff00;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⭐ SÉLECTION DES PERSONNAGES ⭐</h1>

        @if(session('success'))
            <div class="message success">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="message error">
                ✗ {{ session('error') }}
            </div>
        @endif

        @if(session('info'))
            <div class="message">
                ℹ {{ session('info') }}
            </div>
        @endif

        <!-- Liste des personnages existants -->
        <div class="section">
            <h2>VOS PERSONNAGES</h2>

            @if($personnages->count() > 0)
                <div class="personnages-list">
                    @foreach($personnages as $personnage)
                        <div class="personnage-card {{ $compte->perso_principal == $personnage->id ? 'active' : '' }}">
                            <div class="personnage-name">
                                {{ $personnage->nom }} {{ $personnage->prenom }}
                                @if($compte->perso_principal == $personnage->id)
                                    <span style="color: #ffff00;">[ACTIF]</span>
                                @endif
                            </div>
                            <div class="personnage-stats">
                                Niveau: {{ $personnage->niveau }} | XP: {{ $personnage->experience }}<br>
                                Hope: {{ $personnage->jetons_hope }} | Fear: {{ $personnage->jetons_fear }}
                            </div>

                            <div class="button-group">
                                @if($compte->perso_principal != $personnage->id)
                                    <form action="{{ route('personnage.activer', $personnage) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="btn-primary">✓ VALIDER</button>
                                    </form>
                                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">✗ ANNULER</a>
                                @else
                                    <a href="{{ route('dashboard') }}" class="btn btn-primary">▶ CONTINUER</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    Aucun personnage. Créez-en un ci-dessous.
                </div>
            @endif
        </div>

        <!-- Formulaire de création -->
        <div class="section">
            <h2>CRÉER UN NOUVEAU PERSONNAGE</h2>

            <form action="{{ route('personnage.creer') }}" method="POST" id="create-form">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom de famille *</label>
                        <input type="text" id="nom" name="nom" required maxlength="50" placeholder="Ex: Stark">
                        @error('nom')
                            <span style="color: #ff0000; font-size: 12px;">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="button" class="btn btn-small" onclick="genererNom()" style="white-space: nowrap;">
                        🎲 Générer
                    </button>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" maxlength="50" placeholder="Ex: John">
                        @error('prenom')
                            <span style="color: #ff0000; font-size: 12px;">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="button" class="btn btn-small" onclick="genererPrenom()" style="white-space: nowrap;">
                        🎲 Générer
                    </button>
                </div>

                <div class="form-group">
                    <label for="station_depart_id">Station de départ</label>
                    <select id="station_depart_id" name="station_depart_id">
                        @if($stationsDepart->count() > 0)
                            @foreach($stationsDepart as $station)
                                <option value="{{ $station->id }}" {{ $station->nom === 'Lunastar Station' ? 'selected' : '' }}>
                                    {{ $station->nom }}{{ $station->nom === 'Lunastar Station' ? ' (par défaut)' : '' }}
                                </option>
                            @endforeach
                        @else
                            <option value="">Aucune station disponible</option>
                        @endif
                    </select>
                </div>

                <button type="submit" class="btn-primary">✨ CRÉER PERSONNAGE</button>
            </form>
        </div>

        <!-- Bouton retour -->
        <div style="text-align: center;">
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                ← Retour au tableau de bord
            </a>
        </div>
    </div>

    <script>
        // Listes de noms et prénoms pour génération aléatoire
        const noms = [
            'Stark', 'Shepard', 'Vega', 'Phoenix', 'Ryder', 'Reyes',
            'Nova', 'Atlas', 'Orion', 'Drake', 'Archer', 'Hunter',
            'Sterling', 'Knox', 'Bishop', 'Cross', 'Graves', 'Stone',
            'Cole', 'Kane', 'Rook', 'Steele', 'Wolf', 'Hawk',
            'Caine', 'Lynch', 'Blake', 'Mason', 'Reed', 'Ford'
        ];

        const prenoms = [
            'Alex', 'Morgan', 'Jordan', 'Casey', 'Riley', 'Taylor',
            'Kai', 'Nova', 'River', 'Ash', 'Sky', 'Phoenix',
            'Echo', 'Atlas', 'Orion', 'Vega', 'Sirius', 'Lyra',
            'Zara', 'Kira', 'Rex', 'Max', 'Jax', 'Cole',
            'Drew', 'Sage', 'Quinn', 'Rowan', 'Blake', 'Jules'
        ];

        function genererNom() {
            const nomAleatoire = noms[Math.floor(Math.random() * noms.length)];
            document.getElementById('nom').value = nomAleatoire;
        }

        function genererPrenom() {
            const prenomAleatoire = prenoms[Math.floor(Math.random() * prenoms.length)];
            document.getElementById('prenom').value = prenomAleatoire;
        }

        // Générer nom et prénom aléatoires ensemble
        function genererTout() {
            genererNom();
            genererPrenom();
        }

        // Optionnel: Utilisation d'une API externe pour générer des noms plus variés
        async function genererNomAPI() {
            try {
                const response = await fetch('https://randomuser.me/api/?nat=us,gb,fr');
                const data = await response.json();
                const user = data.results[0];

                document.getElementById('prenom').value = user.name.first.charAt(0).toUpperCase() + user.name.first.slice(1);
                document.getElementById('nom').value = user.name.last.charAt(0).toUpperCase() + user.name.last.slice(1);
            } catch (error) {
                console.error('Erreur lors de la génération du nom:', error);
                // Fallback sur génération locale
                genererTout();
            }
        }
    </script>
</body>
</html>
