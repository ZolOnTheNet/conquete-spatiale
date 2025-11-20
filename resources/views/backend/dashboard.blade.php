<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Backend - Dashboard Administratif</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background: #000;
            color: #00ccff;
            min-height: 100vh;
        }

        .header {
            background: #001122;
            border-bottom: 2px solid #00ccff;
            padding: 20px;
        }

        .header h1 {
            font-size: 28px;
            text-shadow: 0 0 10px #00ccff;
            margin-bottom: 10px;
        }

        .nav {
            display: flex;
            gap: 20px;
            margin-top: 15px;
        }

        .nav a {
            color: #00ccff;
            text-decoration: none;
            padding: 8px 15px;
            border: 1px solid #00ccff;
            background: #002233;
            transition: all 0.3s;
        }

        .nav a:hover {
            background: #003344;
            box-shadow: 0 0 10px #00ccff;
        }

        .nav a.active {
            background: #004455;
            box-shadow: 0 0 15px #00ccff;
        }

        .container {
            padding: 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: #001122;
            border: 2px solid #00ccff;
            padding: 25px;
            text-align: center;
        }

        .stat-card h3 {
            font-size: 16px;
            margin-bottom: 15px;
            color: #0099cc;
        }

        .stat-card .value {
            font-size: 42px;
            font-weight: bold;
            text-shadow: 0 0 15px #00ccff;
        }

        .section {
            background: #001122;
            border: 2px solid #00ccff;
            padding: 25px;
            margin-bottom: 30px;
        }

        .section h2 {
            font-size: 22px;
            margin-bottom: 20px;
            color: #00ccff;
            text-shadow: 0 0 8px #00ccff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #003344;
        }

        table th {
            background: #002233;
            color: #0099cc;
            font-weight: bold;
        }

        table tr:hover {
            background: #002233;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border: 1px solid #00ccff;
            font-size: 12px;
            margin: 0 5px;
        }

        .badge.gaia {
            border-color: #00ff88;
            color: #00ff88;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #006688;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚙️ BACKEND ADMINISTRATIF</h1>
        <div class="nav">
            <a href="{{ route('backend.dashboard') }}" class="active">Dashboard</a>
            <a href="{{ route('backend.carte') }}">Carte Stellaire 3D</a>
            <a href="{{ route('backend.backup.index') }}">Backup & Restore</a>
        </div>
    </div>

    <div class="container">
        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>COMPTES TOTAL</h3>
                <div class="value">{{ $stats['comptes_total'] }}</div>
            </div>
            <div class="stat-card">
                <h3>PERSONNAGES TOTAL</h3>
                <div class="value">{{ $stats['personnages_total'] }}</div>
            </div>
            <div class="stat-card">
                <h3>SYSTÈMES STELLAIRES</h3>
                <div class="value">{{ $stats['systemes_total'] }}</div>
            </div>
            <div class="stat-card">
                <h3>SYSTÈMES GAIA</h3>
                <div class="value">{{ $stats['systemes_gaia'] }}</div>
            </div>
            <div class="stat-card">
                <h3>PERSONNAGES ACTIFS</h3>
                <div class="value">{{ $stats['personnages_actifs'] }}</div>
            </div>
        </div>

        <!-- Derniers personnages -->
        <div class="section">
            <h2>📊 DERNIERS PERSONNAGES CRÉÉS</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Compte</th>
                        <th>Vaisseau</th>
                        <th>Système Actuel</th>
                        <th>Créé</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($derniersPersonnages as $personnage)
                        <tr>
                            <td>{{ $personnage->id }}</td>
                            <td>{{ $personnage->nom }}</td>
                            <td>{{ $personnage->compte->nom_login }}</td>
                            <td>{{ $personnage->vaisseau?->nom_modele ?? 'Aucun' }}</td>
                            <td>
                                @if($personnage->systemeActuel)
                                    {{ $personnage->systemeActuel->nom }}
                                    @if($personnage->systemeActuel->source_gaia)
                                        <span class="badge gaia">GAIA</span>
                                    @endif
                                @else
                                    <em>Aucun</em>
                                @endif
                            </td>
                            <td>{{ $personnage->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #006688;">
                                Aucun personnage trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        Conquête Spatiale - Backend Administratif v1.0 | Session: {{ auth()->user()->nom_login }}
    </div>
</body>
</html>
