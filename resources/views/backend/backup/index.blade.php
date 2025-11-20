<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Backend - Backup & Restore</title>
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

        .create-backup {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            margin-bottom: 30px;
        }

        .form-group {
            flex: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #0099cc;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            background: #000;
            border: 1px solid #00ccff;
            color: #00ccff;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }

        .form-group input:focus {
            outline: none;
            box-shadow: 0 0 10px #00ccff;
        }

        .btn {
            padding: 12px 25px;
            background: #002233;
            border: 1px solid #00ccff;
            color: #00ccff;
            cursor: pointer;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn:hover {
            background: #003344;
            box-shadow: 0 0 10px #00ccff;
        }

        .btn-danger {
            border-color: #ff4444;
            color: #ff4444;
        }

        .btn-danger:hover {
            background: #331111;
            box-shadow: 0 0 10px #ff4444;
        }

        .btn-success {
            border-color: #00ff88;
            color: #00ff88;
        }

        .btn-success:hover {
            background: #113322;
            box-shadow: 0 0 10px #00ff88;
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

        .actions {
            display: flex;
            gap: 10px;
        }

        .actions button {
            padding: 6px 12px;
            font-size: 12px;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border: 2px solid;
            background: #001122;
        }

        .message.success {
            border-color: #00ff88;
            color: #00ff88;
        }

        .message.error {
            border-color: #ff4444;
            color: #ff4444;
        }

        .loading {
            text-align: center;
            padding: 30px;
            font-size: 18px;
            color: #0099cc;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border: 1px solid #00ccff;
            font-size: 11px;
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
        <h1>💾 BACKUP & RESTORE</h1>
        <div class="nav">
            <a href="{{ route('backend.dashboard') }}">Dashboard</a>
            <a href="{{ route('backend.carte') }}">Carte Stellaire 3D</a>
            <a href="{{ route('backend.backup.index') }}" class="active">Backup & Restore</a>
        </div>
    </div>

    <div class="container">
        <!-- Messages -->
        <div id="message-container"></div>

        <!-- Créer nouvelle sauvegarde -->
        <div class="section">
            <h2>📦 CRÉER UNE SAUVEGARDE</h2>
            <form id="create-backup-form" class="create-backup">
                <div class="form-group">
                    <label for="description">Description (optionnel)</label>
                    <input type="text" id="description" name="description" placeholder="Ex: Avant Phase 2 Économie">
                </div>
                <button type="submit" class="btn btn-success">💾 Créer Backup</button>
            </form>
        </div>

        <!-- Liste des sauvegardes -->
        <div class="section">
            <h2>📋 SAUVEGARDES DISPONIBLES</h2>
            <div id="backups-loading" class="loading">⏳ Chargement des sauvegardes...</div>
            <div id="backups-list" style="display: none;">
                <table>
                    <thead>
                        <tr>
                            <th>Fichier</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Taille</th>
                            <th>Version DB</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="backups-tbody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="footer">
        Conquête Spatiale - Backup & Restore System v1.0
    </div>

    <script>
        // CSRF Token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Charger les sauvegardes au démarrage
        loadBackups();

        // Créer backup
        document.getElementById('create-backup-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const description = document.getElementById('description').value;
            showMessage('⏳ Création de la sauvegarde en cours...', 'info');

            try {
                const response = await fetch('{{ route('backend.backup.create') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ description }),
                });

                const data = await response.json();

                if (data.success) {
                    showMessage('✅ ' + data.message, 'success');
                    document.getElementById('description').value = '';
                    loadBackups();
                } else {
                    showMessage('❌ ' + data.message, 'error');
                }
            } catch (error) {
                showMessage('❌ Erreur réseau: ' + error.message, 'error');
            }
        });

        async function loadBackups() {
            document.getElementById('backups-loading').style.display = 'block';
            document.getElementById('backups-list').style.display = 'none';

            try {
                const response = await fetch('{{ route('backend.backup.list') }}');
                const data = await response.json();

                if (data.success) {
                    renderBackups(data.backups);
                    document.getElementById('backups-loading').style.display = 'none';
                    document.getElementById('backups-list').style.display = 'block';
                }
            } catch (error) {
                document.getElementById('backups-loading').innerHTML = '❌ Erreur chargement: ' + error.message;
            }
        }

        function renderBackups(backups) {
            const tbody = document.getElementById('backups-tbody');
            tbody.innerHTML = '';

            if (backups.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #006688;">Aucune sauvegarde disponible</td></tr>';
                return;
            }

            backups.forEach(backup => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${backup.filename}</td>
                    <td>${backup.description || '<em>Aucune</em>'}</td>
                    <td>${backup.created_at}</td>
                    <td>${formatSize(backup.size)}</td>
                    <td><span class="badge">v${backup.db_version || 'N/A'}</span></td>
                    <td class="actions">
                        <button class="btn" onclick="downloadBackup('${backup.filename}')">📥 Télécharger</button>
                        <button class="btn btn-success" onclick="restoreBackup('${backup.filename}')">♻️ Restaurer</button>
                        <button class="btn btn-danger" onclick="deleteBackup('${backup.filename}')">🗑️ Supprimer</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function downloadBackup(filename) {
            window.location.href = '{{ route('backend.backup.download', '') }}/' + filename;
        }

        async function restoreBackup(filename) {
            if (!confirm('⚠️ ATTENTION: Restaurer cette sauvegarde remplacera TOUTES les données actuelles. Continuer?')) {
                return;
            }

            showMessage('⏳ Restauration en cours...', 'info');

            try {
                const response = await fetch(`{{ route('backend.backup.restore', '') }}/${filename}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });

                const data = await response.json();

                if (data.success) {
                    showMessage('✅ ' + data.message, 'success');
                } else {
                    showMessage('❌ ' + data.message, 'error');
                }
            } catch (error) {
                showMessage('❌ Erreur: ' + error.message, 'error');
            }
        }

        async function deleteBackup(filename) {
            if (!confirm('Supprimer cette sauvegarde définitivement?')) {
                return;
            }

            try {
                const response = await fetch(`{{ route('backend.backup.delete', '') }}/${filename}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });

                const data = await response.json();

                if (data.success) {
                    showMessage('✅ ' + data.message, 'success');
                    loadBackups();
                } else {
                    showMessage('❌ ' + data.message, 'error');
                }
            } catch (error) {
                showMessage('❌ Erreur: ' + error.message, 'error');
            }
        }

        function formatSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function showMessage(text, type) {
            const container = document.getElementById('message-container');
            const message = document.createElement('div');
            message.className = `message ${type}`;
            message.textContent = text;
            container.innerHTML = '';
            container.appendChild(message);

            if (type === 'success' || type === 'info') {
                setTimeout(() => {
                    message.remove();
                }, 5000);
            }
        }
    </script>
</body>
</html>
