<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sélection Personnage - Conquête Galactique</title>
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
            max-width: 800px;
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
            cursor: pointer;
            transition: all 0.3s;
            background: #000;
        }

        .personnage-card:hover {
            background: #002200;
            box-shadow: 0 0 10px #00ff00;
        }

        .personnage-card.active {
            border-color: #00ff00;
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

        label {
            color: #00ff00;
            font-size: 14px;
        }

        input[type="text"] {
            background: #000;
            border: 1px solid #00ff00;
            color: #00ff00;
            padding: 10px;
            font-family: 'Courier New', monospace;
            font-size: 16px;
        }

        input[type="text"]:focus {
            outline: none;
            box-shadow: 0 0 10px #00ff00;
        }

        button {
            background: #003300;
            border: 1px solid #00ff00;
            color: #00ff00;
            padding: 12px 24px;
            cursor: pointer;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            transition: all 0.3s;
        }

        button:hover {
            background: #004400;
            box-shadow: 0 0 10px #00ff00;
        }

        .btn-activer {
            margin-top: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⭐ SÉLECTION PERSONNAGE ⭐</h1>

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

                            @if($compte->perso_principal != $personnage->id)
                                <form action="{{ route('personnage.activer', $personnage) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-activer">▶ ACTIVER CE PERSONNAGE</button>
                                </form>
                            @else
                                <a href="{{ route('dashboard') }}" style="color: #00ff00; text-decoration: none; display: inline-block; margin-top: 10px;">
                                    ► Continuer avec ce personnage
                                </a>
                            @endif
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

            <form action="{{ route('personnage.creer') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="nom">Nom de famille *</label>
                    <input type="text" id="nom" name="nom" required maxlength="50" placeholder="Ex: Stark">
                    @error('nom')
                        <span style="color: #ff0000; font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="prenom">Prénom (optionnel)</label>
                    <input type="text" id="prenom" name="prenom" maxlength="50" placeholder="Ex: John">
                    @error('prenom')
                        <span style="color: #ff0000; font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit">✨ CRÉER PERSONNAGE</button>
            </form>
        </div>
    </div>
</body>
</html>
