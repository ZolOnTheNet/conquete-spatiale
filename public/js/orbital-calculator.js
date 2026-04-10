/**
 * Calculateur de positions orbitales des planètes
 * Calcul côté client pour alléger le serveur et avoir un affichage temps réel
 *
 * Date de référence du jeu : 1er janvier 3000 à 00:00:00
 * Coordonnées stockées optimisées pour calcul rapide
 */

class OrbitalCalculator {
    /**
     * Date de référence du jeu (timestamp Unix en millisecondes)
     */
    static DATE_REFERENCE = new Date('3000-01-01T00:00:00Z').getTime();

    /**
     * Conversion UA vers AL (1 UA ≈ 0.0000158 AL)
     */
    static UA_VERS_AL = 0.0000158;

    /**
     * Conversion AL vers UA (1 AL ≈ 63241 UA)
     */
    static AL_VERS_UA = 63241;

    /**
     * Convertir une date JavaScript en jours depuis la référence 3000-01-01
     *
     * @param {Date} date Date in-game
     * @returns {number} Nombre de jours depuis 3000-01-01 (peut être décimal)
     */
    static dateToJours(date) {
        const timestampMs = date.getTime();
        const millisecondesParJour = 24 * 60 * 60 * 1000;
        return (timestampMs - this.DATE_REFERENCE) / millisecondesParJour;
    }

    /**
     * Convertir un nombre de jours en date JavaScript
     *
     * @param {number} jours Nombre de jours depuis 3000-01-01
     * @returns {Date} Date in-game
     */
    static joursToDate(jours) {
        const millisecondesParJour = 24 * 60 * 60 * 1000;
        return new Date(this.DATE_REFERENCE + (jours * millisecondesParJour));
    }

    /**
     * Calculer la position orbitale d'une planète à un instant donné
     *
     * @param {Object} planete Données orbitales de la planète
     *   - distance_etoile: number (cUA depuis migration 2025_12_30_160337)
     *   - angle_orbital_initial: number (radians)
     *   - vitesse_angulaire: number (rad/jour)
     *   - cache_position_x: number (cUA, optionnel)
     *   - cache_position_y: number (cUA, optionnel)
     *   - cache_timestamp_jours: number (optionnel)
     * @param {number} timestampJours Temps actuel en jours depuis 3000-01-01
     * @returns {Object} Position orbitale {x, y, z} en cUA par rapport à l'étoile
     */
    static calculerPositionOrbitale(planete, timestampJours) {
        // Si cache valide, utiliser le cache comme base
        if (planete.cache_timestamp_jours !== null && planete.cache_validite_jours) {
            const joursDepuisCache = Math.abs(timestampJours - planete.cache_timestamp_jours);

            // Cache valide, utiliser directement
            if (joursDepuisCache < planete.cache_validite_jours) {
                return {
                    x: planete.cache_position_x,
                    y: planete.cache_position_y,
                    z: planete.cache_position_z || 0,
                    fromCache: true,
                };
            }
        }

        // Calculer l'angle actuel
        const angleActuel = planete.angle_orbital_initial +
                          (planete.vitesse_angulaire * timestampJours);

        // Position orbitale (orbite circulaire dans plan XY)
        // IMPORTANT: distance_etoile est en cUA, pas en UA
        return {
            x: planete.distance_etoile * Math.cos(angleActuel),
            y: planete.distance_etoile * Math.sin(angleActuel),
            z: 0, // Plan orbital simplifié
            angle: angleActuel % (2 * Math.PI),
            fromCache: false,
        };
    }

    /**
     * Calculer la position absolue d'une planète dans l'espace (en cUA)
     *
     * IMPORTANT: Dans un même secteur, pas besoin de convertir en AL !
     * Tous les calculs se font directement en cUA pour éviter les pertes de précision.
     *
     * @param {Object} planete Données orbitales de la planète
     * @param {Object} systeme Position du système stellaire {position_x, position_y, position_z} en cUA
     * @param {number} timestampJours Temps actuel en jours
     * @returns {Object} Position absolue {x, y, z} en cUA
     */
    static calculerPositionAbsolue(planete, systeme, timestampJours) {
        // Position orbitale relative (en cUA depuis migration 2025_12_30_160337)
        const positionOrbitale = this.calculerPositionOrbitale(planete, timestampJours);

        // Addition directe en cUA (pas de conversion nécessaire !)
        return {
            x: systeme.position_x + positionOrbitale.x,
            y: systeme.position_y + positionOrbitale.y,
            z: systeme.position_z + positionOrbitale.z,
            angle: positionOrbitale.angle,
        };
    }

    /**
     * Calculer la distance entre une planète et un vaisseau (en UA)
     *
     * IMPORTANT: Calcul direct en cUA sans passer par AL (plus précis)
     *
     * @param {Object} planete Données orbitales de la planète
     * @param {Object} systeme Position du système stellaire {position_x, position_y, position_z} en cUA
     * @param {Object} vaisseau Position du vaisseau {position_x, position_y, position_z} en cUA
     * @param {number} timestampJours Temps actuel en jours
     * @returns {number} Distance en UA
     */
    static calculerDistance(planete, systeme, vaisseau, timestampJours) {
        // Position absolue planète (en cUA)
        const posPlanete = this.calculerPositionAbsolue(planete, systeme, timestampJours);

        // Distance en cUA (calcul direct sans conversion)
        const dx = posPlanete.x - vaisseau.position_x;
        const dy = posPlanete.y - vaisseau.position_y;
        const dz = posPlanete.z - vaisseau.position_z;
        const distanceCua = Math.sqrt(dx * dx + dy * dy + dz * dz);

        // Conversion cUA → UA pour l'affichage (1 UA = 100 cUA)
        return distanceCua / 100;
    }

    /**
     * Mettre à jour les positions de plusieurs planètes en temps réel
     * Utile pour affichage dynamique (carte, timonerie, etc.)
     *
     * @param {Array} planetes Liste de planètes avec données orbitales
     * @param {Object} systeme Position du système stellaire
     * @param {Object} vaisseau Position du vaisseau (optionnel)
     * @param {Date} dateJeu Date actuelle du jeu (optionnel, défaut: now)
     * @returns {Array} Planètes avec positions calculées
     */
    static mettreAJourPositions(planetes, systeme, vaisseau = null, dateJeu = null) {
        const timestampJours = dateJeu ? this.dateToJours(dateJeu) : this.dateToJours(new Date());

        return planetes.map(planete => {
            const position = this.calculerPositionAbsolue(planete, systeme, timestampJours);

            const result = {
                ...planete,
                position_absolue: position,
            };

            if (vaisseau) {
                result.distance_ua = this.calculerDistance(planete, systeme, vaisseau, timestampJours);
            }

            return result;
        });
    }

    /**
     * Animation temps réel : calculer la prochaine position d'une planète
     * Pour affichage fluide avec requestAnimationFrame
     *
     * @param {Object} planete Données orbitales
     * @param {number} deltaTemps Temps écoulé depuis dernier frame (ms)
     * @returns {Object} Nouvelle position {angle, x, y}
     */
    static animerPlanete(planete, deltaTemps) {
        // Convertir millisecondes en jours
        const deltaJours = deltaTemps / (24 * 60 * 60 * 1000);

        // Incrémenter l'angle
        const nouvelAngle = (planete._angleActuel || planete.angle_orbital_initial) +
                           (planete.vitesse_angulaire * deltaJours);

        return {
            angle: nouvelAngle % (2 * Math.PI),
            x: planete.distance_etoile * Math.cos(nouvelAngle),
            y: planete.distance_etoile * Math.sin(nouvelAngle),
            z: 0,
        };
    }
}

// Export pour utilisation en module (optionnel)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = OrbitalCalculator;
}

// Export global pour utilisation directe dans le navigateur
if (typeof window !== 'undefined') {
    window.OrbitalCalculator = OrbitalCalculator;
}
