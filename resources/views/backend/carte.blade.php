<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Backend - Carte Stellaire 3D</title>
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
            overflow: hidden;
        }

        .header {
            background: #001122;
            border-bottom: 2px solid #00ccff;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
            text-shadow: 0 0 10px #00ccff;
        }

        .nav {
            display: flex;
            gap: 15px;
        }

        .nav a {
            color: #00ccff;
            text-decoration: none;
            padding: 8px 15px;
            border: 1px solid #00ccff;
            background: #002233;
            font-size: 14px;
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

        .main-container {
            display: flex;
            height: calc(100vh - 70px);
        }

        #canvas-container {
            flex: 1;
            position: relative;
        }

        .sidebar {
            width: 300px;
            background: #001122;
            border-left: 2px solid #00ccff;
            padding: 20px;
            overflow-y: auto;
        }

        .sidebar h2 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #00ccff;
            text-shadow: 0 0 8px #00ccff;
        }

        .info-box {
            background: #002233;
            border: 1px solid #00ccff;
            padding: 15px;
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-size: 13px;
        }

        .info-label {
            color: #0099cc;
        }

        .controls {
            margin-bottom: 20px;
        }

        .control-group {
            margin: 15px 0;
        }

        .control-group label {
            display: block;
            margin-bottom: 8px;
            color: #0099cc;
            font-size: 13px;
        }

        .control-group input[type="checkbox"] {
            margin-right: 8px;
        }

        .btn {
            width: 100%;
            padding: 10px;
            background: #002233;
            border: 1px solid #00ccff;
            color: #00ccff;
            cursor: pointer;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            margin: 5px 0;
            transition: all 0.3s;
        }

        .btn:hover {
            background: #003344;
            box-shadow: 0 0 10px #00ccff;
        }

        .legend {
            margin-top: 20px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin: 8px 0;
            font-size: 12px;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            border: 1px solid #00ccff;
        }

        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            text-shadow: 0 0 20px #00ccff;
            z-index: 10;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🗺️ CARTE STELLAIRE 3D</h1>
        <div class="nav">
            <a href="{{ route('backend.dashboard') }}">Dashboard</a>
            <a href="{{ route('backend.carte') }}" class="active">Carte Stellaire 3D</a>
            <a href="{{ route('backend.backup.index') }}">Backup & Restore</a>
        </div>
    </div>

    <div class="main-container">
        <div id="canvas-container">
            <div id="loading" class="loading">⏳ CHARGEMENT DONNÉES STELLAIRES...</div>
        </div>

        <div class="sidebar">
            <h2>🎛️ CONTRÔLES</h2>

            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Systèmes:</span>
                    <span id="count-systems">0</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Systèmes GAIA:</span>
                    <span id="count-gaia">0</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Personnages:</span>
                    <span id="count-players">0</span>
                </div>
            </div>

            <div class="controls">
                <div class="control-group">
                    <label>
                        <input type="checkbox" id="toggle-systems" checked>
                        Afficher systèmes stellaires
                    </label>
                </div>
                <div class="control-group">
                    <label>
                        <input type="checkbox" id="toggle-gaia" checked>
                        Mettre en évidence GAIA
                    </label>
                </div>
                <div class="control-group">
                    <label>
                        <input type="checkbox" id="toggle-players" checked>
                        Afficher joueurs
                    </label>
                </div>
                <div class="control-group">
                    <label>
                        <input type="checkbox" id="toggle-grid" checked>
                        Afficher grille
                    </label>
                </div>
            </div>

            <button class="btn" onclick="resetCamera()">↺ Réinitialiser Caméra</button>
            <button class="btn" onclick="refreshData()">🔄 Rafraîchir Données</button>

            <div class="legend">
                <h2>🎨 LÉGENDE</h2>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ffff00;"></div>
                    <span>Étoile Type G (Sol)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #0088ff;"></div>
                    <span>Étoile Type O/B</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ffffff;"></div>
                    <span>Étoile Type A/F</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ff8800;"></div>
                    <span>Étoile Type K</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ff0000;"></div>
                    <span>Étoile Type M</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #00ff00;"></div>
                    <span>Système GAIA (encadré)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ff00ff;"></div>
                    <span>Joueurs</span>
                </div>
            </div>

            <div class="info-box" style="margin-top: 20px;">
                <h2 style="font-size: 14px; margin-bottom: 10px;">🕹️ NAVIGATION</h2>
                <div style="font-size: 11px; line-height: 1.6;">
                    <strong>Souris:</strong><br>
                    - Clic gauche + déplacer: Rotation<br>
                    - Molette: Zoom<br>
                    - Clic droit + déplacer: Pan<br>
                    <br>
                    <strong>Clic sur étoile:</strong><br>
                    - Affiche informations système
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.160.0/examples/js/controls/OrbitControls.js"></script>

    <script>
        // Variables globales
        let scene, camera, renderer, controls;
        let systemsData = [];
        let playersData = [];
        let systemObjects = [];
        let playerObjects = [];
        let gridHelper;

        // Couleurs par type d'étoile
        const starColors = {
            'O': 0x0088ff,
            'B': 0x4444ff,
            'A': 0xffffff,
            'F': 0xffffaa,
            'G': 0xffff00,
            'K': 0xff8800,
            'M': 0xff0000,
        };

        // Initialisation
        function init() {
            const container = document.getElementById('canvas-container');
            const width = container.clientWidth;
            const height = container.clientHeight;

            // Scène
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0x000000);

            // Caméra
            camera = new THREE.PerspectiveCamera(75, width / height, 0.1, 10000);
            camera.position.set(50, 50, 50);

            // Renderer
            renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize(width, height);
            container.appendChild(renderer.domElement);

            // Contrôles
            controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.05;

            // Grille
            gridHelper = new THREE.GridHelper(200, 20, 0x00ccff, 0x003344);
            scene.add(gridHelper);

            // Axes (Sol au centre)
            const axesHelper = new THREE.AxesHelper(100);
            scene.add(axesHelper);

            // Sol marker (grosse sphère jaune)
            const solGeometry = new THREE.SphereGeometry(2, 32, 32);
            const solMaterial = new THREE.MeshBasicMaterial({ color: 0xffff00 });
            const sol = new THREE.Mesh(solGeometry, solMaterial);
            scene.add(sol);

            // Lumière
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
            scene.add(ambientLight);

            // Resize
            window.addEventListener('resize', onWindowResize, false);

            // Charger les données
            loadData();

            // Animation loop
            animate();
        }

        function animate() {
            requestAnimationFrame(animate);
            controls.update();
            renderer.render(scene, camera);
        }

        function onWindowResize() {
            const container = document.getElementById('canvas-container');
            camera.aspect = container.clientWidth / container.clientHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(container.clientWidth, container.clientHeight);
        }

        async function loadData() {
            try {
                // Charger systèmes stellaires
                const systemsResponse = await fetch('{{ route('backend.api.systemes') }}');
                const systemsJson = await systemsResponse.json();
                systemsData = systemsJson.systemes;

                // Charger joueurs
                const playersResponse = await fetch('{{ route('backend.api.joueurs') }}');
                const playersJson = await playersResponse.json();
                playersData = playersJson.personnages;

                // Mettre à jour UI
                document.getElementById('count-systems').textContent = systemsData.length;
                document.getElementById('count-gaia').textContent = systemsData.filter(s => s.source_gaia).length;
                document.getElementById('count-players').textContent = playersData.length;

                // Afficher dans la scène
                renderSystems();
                renderPlayers();

                document.getElementById('loading').classList.add('hidden');
            } catch (error) {
                console.error('Erreur chargement données:', error);
                document.getElementById('loading').textContent = '❌ ERREUR CHARGEMENT DONNÉES';
            }
        }

        function renderSystems() {
            // Supprimer anciens objets
            systemObjects.forEach(obj => scene.remove(obj));
            systemObjects = [];

            systemsData.forEach(system => {
                const x = system.secteur_x + system.position_x;
                const y = system.secteur_y + system.position_y;
                const z = system.secteur_z + system.position_z;

                // Couleur selon type
                const color = starColors[system.type_etoile] || 0xffffff;

                // Sphère pour l'étoile
                const geometry = new THREE.SphereGeometry(0.5, 16, 16);
                const material = new THREE.MeshBasicMaterial({ color: color });
                const star = new THREE.Mesh(geometry, material);
                star.position.set(x, y, z);
                star.userData = { type: 'system', data: system };

                // Si GAIA, ajouter un wireframe
                if (system.source_gaia) {
                    const wireframeGeometry = new THREE.SphereGeometry(1, 8, 8);
                    const wireframeMaterial = new THREE.MeshBasicMaterial({
                        color: 0x00ff00,
                        wireframe: true
                    });
                    const wireframe = new THREE.Mesh(wireframeGeometry, wireframeMaterial);
                    wireframe.position.copy(star.position);
                    scene.add(wireframe);
                    systemObjects.push(wireframe);
                }

                scene.add(star);
                systemObjects.push(star);
            });
        }

        function renderPlayers() {
            // Supprimer anciens objets
            playerObjects.forEach(obj => scene.remove(obj));
            playerObjects = [];

            playersData.forEach(player => {
                const geometry = new THREE.ConeGeometry(0.8, 2, 4);
                const material = new THREE.MeshBasicMaterial({ color: 0xff00ff });
                const cone = new THREE.Mesh(geometry, material);
                cone.position.set(player.systeme.x, player.systeme.y + 3, player.systeme.z);
                cone.userData = { type: 'player', data: player };

                scene.add(cone);
                playerObjects.push(cone);
            });
        }

        function resetCamera() {
            camera.position.set(50, 50, 50);
            controls.target.set(0, 0, 0);
            controls.update();
        }

        function refreshData() {
            document.getElementById('loading').classList.remove('hidden');
            loadData();
        }

        // Toggles
        document.getElementById('toggle-systems').addEventListener('change', (e) => {
            systemObjects.forEach(obj => obj.visible = e.target.checked);
        });

        document.getElementById('toggle-grid').addEventListener('change', (e) => {
            gridHelper.visible = e.target.checked;
        });

        document.getElementById('toggle-players').addEventListener('change', (e) => {
            playerObjects.forEach(obj => obj.visible = e.target.checked);
        });

        // Démarrer
        init();
    </script>
</body>
</html>
