#!/usr/bin/env python3
"""
Analyse de la distribution des systèmes stellaires pour déterminer
la taille optimale d'un secteur.

Analyse également la taille des systèmes (distance max planète-étoile)
pour s'assurer qu'un secteur peut contenir un système ENTIER.
"""

import sqlite3
import math
from pathlib import Path
from collections import defaultdict

# Chemin vers la base de données
DB_PATH = Path(__file__).parent.parent / 'database' / 'database.sqlite'

# Constante de conversion UA → AL
UA_TO_AL = 1.0 / 63241.0  # 1 AL ≈ 63 241 UA

def calculate_distance(x1, y1, z1, x2, y2, z2):
    """Calcule la distance euclidienne 3D en années-lumière."""
    dx = x2 - x1
    dy = y2 - y1
    dz = z2 - z1
    return math.sqrt(dx*dx + dy*dy + dz*dz)

def analyze_planet_distances(conn):
    """Analyse les distances des planètes par rapport à leur étoile."""
    cursor = conn.cursor()

    # Récupérer toutes les planètes avec leur système
    cursor.execute("""
        SELECT
            s.nom as systeme_nom,
            p.nom as planete_nom,
            p.distance_etoile,
            s.type_etoile
        FROM planetes p
        JOIN systemes_stellaires s ON p.systeme_stellaire_id = s.id
        ORDER BY s.nom, p.distance_etoile
    """)

    planetes = cursor.fetchall()

    if not planetes:
        print("⚠️  Aucune planète trouvée dans la base de données.")
        return None

    # Analyser par système
    systemes_data = defaultdict(list)
    for systeme_nom, planete_nom, distance, type_etoile in planetes:
        systemes_data[systeme_nom].append({
            'planete': planete_nom,
            'distance_ua': distance,
            'distance_al': distance * UA_TO_AL,
            'type_etoile': type_etoile
        })

    print(f"\n🪐 ANALYSE DES DISTANCES PLANÈTES-ÉTOILE:")
    print(f"{'='*80}\n")
    print(f"📊 Nombre de planètes: {len(planetes)}")
    print(f"📊 Nombre de systèmes avec planètes: {len(systemes_data)}\n")

    max_distance_ua = 0
    max_distance_al = 0
    max_systeme = None
    max_planete = None

    # Afficher chaque système
    for systeme_nom, planetes_list in sorted(systemes_data.items()):
        print(f"⭐ {systeme_nom} (Type {planetes_list[0]['type_etoile']}):")

        for p in planetes_list:
            print(f"   └─ {p['planete']:<15} : {p['distance_ua']:>8.2f} UA = {p['distance_al']:>12.8f} AL")

            if p['distance_ua'] > max_distance_ua:
                max_distance_ua = p['distance_ua']
                max_distance_al = p['distance_al']
                max_systeme = systeme_nom
                max_planete = p['planete']

        # Distance max du système (rayon)
        max_dist_systeme = max(p['distance_ua'] for p in planetes_list)
        max_dist_systeme_al = max_dist_systeme * UA_TO_AL
        diametre_systeme_al = 2 * max_dist_systeme_al

        print(f"   → Rayon max: {max_dist_systeme:.2f} UA = {max_dist_systeme_al:.8f} AL")
        print(f"   → Diamètre:  {2*max_dist_systeme:.2f} UA = {diametre_systeme_al:.8f} AL\n")

    print(f"\n{'='*80}")
    print(f"📏 DISTANCE MAXIMALE PLANÈTE-ÉTOILE:")
    print(f"{'='*80}")
    print(f"   Système: {max_systeme}")
    print(f"   Planète: {max_planete}")
    print(f"   Distance: {max_distance_ua:.2f} UA")
    print(f"   Distance: {max_distance_al:.8f} AL")
    print(f"   Diamètre du système: {2*max_distance_al:.8f} AL")
    print(f"{'='*80}\n")

    # Vérification unités
    if max_distance_ua > 100:
        print(f"⚠️  ATTENTION: Distance de {max_distance_ua:.2f} UA semble très élevée !")
        print(f"   Neptune dans notre système solaire est à ~30 UA.")
        print(f"   Vérifiez que les distances sont bien en UA et non en AL.\n")

    if max_distance_al > 0.01:
        print(f"⚠️  ATTENTION: Distance de {max_distance_al:.6f} AL semble élevée !")
        print(f"   Le système solaire fait ~0.00063 AL de diamètre (80 UA).")
        print(f"   Il y a peut-être une confusion d'unités dans la base de données.\n")

    return {
        'max_distance_ua': max_distance_ua,
        'max_distance_al': max_distance_al,
        'max_systeme': max_systeme,
        'max_planete': max_planete,
        'diametre_max_al': 2 * max_distance_al
    }


def analyze_universe():
    """Analyse la distribution des systèmes stellaires."""

    if not DB_PATH.exists():
        print(f"❌ Base de données non trouvée: {DB_PATH}")
        print("   Assurez-vous d'être sur votre machine locale.")
        return

    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()

    # NOUVELLE SECTION: Analyser les distances des planètes
    planet_data = analyze_planet_distances(conn)

    # Récupérer tous les systèmes stellaires
    cursor.execute("""
        SELECT
            nom,
            secteur_x,
            secteur_y,
            secteur_z,
            position_x,
            position_y,
            position_z,
            poi_connu,
            puissance,
            type_etoile
        FROM systemes_stellaires
        ORDER BY nom
    """)

    systemes = cursor.fetchall()

    if not systemes:
        print("❌ Aucun système stellaire trouvé dans la base de données.")
        print("   Exécutez: php artisan db:seed --class=UniverseSeeder")
        conn.close()
        return

    print(f"\n🌌 ANALYSE DE L'UNIVERS")
    print(f"{'='*80}\n")
    print(f"📊 Nombre total de systèmes: {len(systemes)}")

    # Position de Sol (origine)
    sol_x, sol_y, sol_z = 0.0, 0.0, 0.0

    # Trouver Sol dans la base
    sol_info = None
    for sys in systemes:
        if sys[0] == 'Sol':
            sol_x = sys[1] * 10 + sys[4]
            sol_y = sys[2] * 10 + sys[5]
            sol_z = sys[3] * 10 + sys[6]
            sol_info = sys
            break

    if sol_info:
        print(f"☀️  Système Solaire trouvé:")
        print(f"   Position: secteur({sol_info[1]}, {sol_info[2]}, {sol_info[3]}) + position({sol_info[4]:.3f}, {sol_info[5]:.3f}, {sol_info[6]:.3f})")
        print(f"   Absolue: ({sol_x:.3f}, {sol_y:.3f}, {sol_z:.3f}) AL")
        print(f"   PoI connu: {'✅ Oui' if sol_info[7] else '❌ Non'}")
    else:
        print(f"⚠️  Système Solaire NON trouvé - utilisation de l'origine (0, 0, 0)")

    print(f"\n📏 DISTANCES DEPUIS SOL:")
    print(f"{'-'*80}")

    # Calculer les distances
    distances_data = []
    poi_connus = []

    for sys in systemes:
        nom = sys[0]
        abs_x = sys[1] * 10 + sys[4]
        abs_y = sys[2] * 10 + sys[5]
        abs_z = sys[3] * 10 + sys[6]

        distance = calculate_distance(sol_x, sol_y, sol_z, abs_x, abs_y, abs_z)

        distances_data.append({
            'nom': nom,
            'abs_x': abs_x,
            'abs_y': abs_y,
            'abs_z': abs_z,
            'distance': distance,
            'distance_int': int(distance),
            'poi_connu': sys[7],
            'puissance': sys[8],
            'type': sys[9]
        })

        if sys[7]:  # poi_connu
            poi_connus.append((nom, distance))

    # Trier par distance
    distances_data.sort(key=lambda x: x['distance'])

    # Afficher les 20 systèmes les plus proches
    print(f"\n🌟 Les 20 systèmes les plus proches de Sol:\n")
    print(f"{'Rang':<5} {'Nom':<25} {'Distance (AL)':<20} {'Entière':<10} {'Type':<6} {'PoI':<5}")
    print(f"{'-'*80}")

    for i, sys in enumerate(distances_data[:20], 1):
        poi = '✓' if sys['poi_connu'] else ''
        print(f"{i:<5} {sys['nom']:<25} {sys['distance']:<20.6f} {sys['distance_int']:<10} {sys['type']:<6} {poi:<5}")

    # Statistiques sur les distances
    distances = [s['distance'] for s in distances_data]
    distances_int = [s['distance_int'] for s in distances_data]

    print(f"\n📊 STATISTIQUES DES DISTANCES:")
    print(f"{'-'*80}")
    print(f"Distance minimale:     {min(distances):.6f} AL (entière: {min(distances_int)})")
    print(f"Distance maximale:     {max(distances):.6f} AL (entière: {max(distances_int)})")
    print(f"Distance moyenne:      {sum(distances)/len(distances):.6f} AL")
    print(f"Distance médiane:      {distances[len(distances)//2]:.6f} AL")

    # Distribution par tranches de distance
    print(f"\n📈 DISTRIBUTION PAR TRANCHES DE DISTANCE:")
    print(f"{'-'*80}")

    tranches = [10, 20, 50, 100, 200, 500, 1000]
    for i, tranche in enumerate(tranches):
        count = sum(1 for d in distances if d <= tranche)
        pourcent = (count / len(distances)) * 100
        print(f"≤ {tranche:4} AL: {count:4} systèmes ({pourcent:5.1f}%)")

    # Analyse pour la taille de secteur optimale
    print(f"\n🎯 ANALYSE POUR LA TAILLE DE SECTEUR:")
    print(f"{'-'*80}")

    # Tailles de secteur à tester : 1, 5, 10, 20, 50, 100 AL
    tailles_secteur = [1, 5, 10, 20, 50, 100]

    print(f"\n{'Taille secteur (AL)':<20} {'Nb secteurs utilisés':<25} {'Densité moy.':<20}")
    print(f"{'-'*80}")

    for taille in tailles_secteur:
        secteurs_utilises = defaultdict(int)

        for sys in distances_data:
            sect_x = int(sys['abs_x'] // taille)
            sect_y = int(sys['abs_y'] // taille)
            sect_z = int(sys['abs_z'] // taille)
            secteurs_utilises[(sect_x, sect_y, sect_z)] += 1

        nb_secteurs = len(secteurs_utilises)
        densite_moy = len(distances_data) / nb_secteurs if nb_secteurs > 0 else 0

        print(f"{taille:<20} {nb_secteurs:<25} {densite_moy:<20.2f} sys/secteur")

    # Systèmes PoI connus
    if poi_connus:
        print(f"\n⭐ SYSTÈMES PoI CONNUS (poi_connu = true):")
        print(f"{'-'*80}")
        poi_connus.sort(key=lambda x: x[1])
        for nom, dist in poi_connus:
            print(f"   {nom:<30} {dist:.6f} AL (entière: {int(dist)})")

    # Recommandations
    print(f"\n💡 RECOMMANDATIONS:")
    print(f"{'='*80}")

    max_dist_int = max(distances_int)
    max_dist_dec = max(distances)

    print(f"\n1. Distance maximale observée (entre systèmes):")
    print(f"   - Décimale: {max_dist_dec:.6f} AL")
    print(f"   - Entière:  {max_dist_int} AL")

    if planet_data:
        print(f"\n2. Taille maximale d'un système stellaire (planète la plus éloignée):")
        print(f"   - Système: {planet_data['max_systeme']}")
        print(f"   - Planète: {planet_data['max_planete']}")
        print(f"   - Rayon: {planet_data['max_distance_al']:.8f} AL ({planet_data['max_distance_ua']:.2f} UA)")
        print(f"   - Diamètre: {planet_data['diametre_max_al']:.8f} AL ({planet_data['max_distance_ua']*2:.2f} UA)")

        # CRITIQUE: Le secteur doit pouvoir contenir un système ENTIER
        taille_min_secteur = planet_data['diametre_max_al']
        print(f"\n   ⚠️  IMPORTANT: Un secteur doit pouvoir contenir un système ENTIER !")
        print(f"   → Taille minimale de secteur: {taille_min_secteur:.8f} AL")
        print(f"   → Taille minimale arrondie: {math.ceil(taille_min_secteur * 1000) / 1000:.3f} AL")

        if taille_min_secteur > 10:
            print(f"\n   ❌ PROBLÈME CRITIQUE: Taille de secteur actuelle (10 AL) trop petite !")
            print(f"      Les planètes dépassent les limites du secteur !")
            print(f"      → Il y a probablement une confusion d'unités (UA vs AL)")
        elif taille_min_secteur > 1:
            print(f"\n   ⚠️  Secteur de 10 AL peut contenir le système, mais vérifiez les unités")
        else:
            print(f"\n   ✅ Secteur de 10 AL peut largement contenir le plus grand système")
            print(f"      (Marge: {10 / taille_min_secteur:.0f}× le diamètre du système)")

    print(f"\n3. Taille de secteur actuelle: 10 AL")
    print(f"   - Couverture nécessaire: {math.ceil(max_dist_int / 10)} secteurs dans chaque direction")
    print(f"   - Secteurs utilisés: {len(set((int(s['abs_x']//10), int(s['abs_y']//10), int(s['abs_z']//10)) for s in distances_data))}")

    print(f"\n4. Analyse de la densité (systèmes par secteur):")
    secteurs_10 = defaultdict(int)
    for sys in distances_data:
        sect = (int(sys['abs_x']//10), int(sys['abs_y']//10), int(sys['abs_z']//10))
        secteurs_10[sect] += 1

    max_systemes_par_secteur = max(secteurs_10.values())
    secteurs_pleins = sum(1 for count in secteurs_10.values() if count > 1)

    print(f"   - Maximum de systèmes dans un secteur: {max_systemes_par_secteur}")
    print(f"   - Secteurs avec plusieurs systèmes: {secteurs_pleins}/{len(secteurs_10)}")
    print(f"   - Densité moyenne: {len(distances_data)/len(secteurs_10):.2f} systèmes/secteur")

    if max_systemes_par_secteur <= 3:
        print(f"\n   ✅ La taille de secteur de 10 AL semble appropriée (faible densité).")
    elif max_systemes_par_secteur <= 10:
        print(f"\n   ⚠️  Envisager d'augmenter la taille de secteur à 20-50 AL (densité moyenne).")
    else:
        print(f"\n   ❌ Taille de secteur trop petite ! Augmenter à 50-100 AL (haute densité).")

    # Résumé final
    print(f"\n{'='*80}")
    print(f"📋 RÉSUMÉ:")
    print(f"{'='*80}")
    if planet_data:
        print(f"   • Plus grand système: {planet_data['diametre_max_al']:.8f} AL de diamètre")
        print(f"   • Taille de secteur: 10 AL")
        print(f"   • Ratio: {10 / planet_data['diametre_max_al']:.1f}× (le secteur peut contenir le système)")
    print(f"   • Distance max entre systèmes: {max_dist_dec:.2f} AL")
    print(f"   • Systèmes par secteur (moy): {len(distances_data)/len(secteurs_10):.2f}")
    print(f"{'='*80}")

    conn.close()
    print(f"\n")

if __name__ == '__main__':
    try:
        analyze_universe()
    except sqlite3.OperationalError as e:
        print(f"\n❌ Erreur de base de données: {e}")
        print(f"   Vérifiez que la base est accessible et que les migrations sont faites.")
    except Exception as e:
        print(f"\n❌ Erreur: {e}")
        import traceback
        traceback.print_exc()
