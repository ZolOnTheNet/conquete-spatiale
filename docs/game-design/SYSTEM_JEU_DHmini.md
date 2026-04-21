# Système de Jeu
Pour Conquete spatiale, le système de jeu est basé sur DaggerHeart, et mon adaptation de starwars FFGU AoE sur ce système. mais possède des automatismes plus poussé.

## Carractéristique
**Agilité (AG)**, **Force(FOR)**, **Finesse (FIN)**, **Instinct (INS)**, **Présence (PRE), Connaissance(Cn)**, les abrévations sont de moi.

### création
les Carractéristique seront affecté suivant les profils, est en utilisant l'idée de répartition : -1, 0, 0, +1, +1, +2
#### profils
pilote* :     FOR :-1 , AG:0 , FIN: 2, INS:0 , PRE: 1, CN:1
militaire :   FOR : 2 , AG:1 , FIN: 1, INS: 0, PRE: 0, CN: -1
navigateur :  FOR : -1, AG: 0, FIN: 0, INS: 1, PRE: 1, CN: 2
bourlingeur : FOR : 1 , AG: 0, FIN: 0, INS: 1, PRE: 0, CN: 1
explorateur : FOR : 0,  AG: 1, FIN: 1, INS: 0, PRE: 0, CN: 1

*Pilote est celui de base, càd si pas défini ou étape sauté etc...

## Mécanique de base (Daggerheart)
- Lancer 2d12+Attribut+Compétences (Hope + Fear)
- Résultat > seuil = succès
- Résultat < seuil = échec
Si le dé Hope est plus grand que le dé de Fear, alors le joueur gagne un jeton de d'espoir (Hope), si le dé de Fear est plus grand alors le Joueur "gagne" un jeton de Peur (Fear)
- Égalité des dés = succès critique avec Hope
- Jetons Fear déclenchent des événements narratifs cachés
- Utilisation des point de Hope si moins de 50% de réussite (beaucoup de jets se feront de manière automatique) et silencieuse

## Mécanique appliqué d'un lancer de dé

### Le système détermine la difficulté
1/ difficulté intiale
    A/ soit connu d'avance 5,10,15,20,25,30,35 ou calculé par rapport à des paramètres (champs d'astéroide).
    B/ Lié à un adversaire ou à un environnement, dans ce cas le système lance les dés.
2/ la peur intervient-t-elle ?
    Le système lance un D20 sous le capital de point de peur (Fear), si fait moins ou égale, le système va utiliser un point de peur (réduisant le capital de 1), cela ajoute 1d6 à la difficulté (la difficulté est caché)
3/ calcul de l'ensemble des bonus du jet
    calcul des différents éléments pouvant apporter des bonus : choix de la caractéristique, avantage lié aux modules, aux armes et autres éléments.
4/ Chance de réussite et espoir :
    Pour déterminer si automatiquement un point d'espoir est utilé pour ajouter la compétence (si elle est déterminé), on regarde si on a avec la compétence une chance de réussir raisonnablement.
    si 24+bonus+Compétence est supérieur à la difficulté (au moins une chance de réussir), et si 12+bonus est inférieur à la difficulté, et si le joueur a un point d'espoir, alors le point d'espoir est utilisé, et la compétence est ajouté au bonus
5/ le jet lui même, en suivant les règles
    Bonus total + 2d12
    calcul du résultat en fonction de la situation et de la notion de peur ou d'espoir.
    Si la difficulté est plus gande de le résulat, alors échec, sinon réussite. Si un point d'espoir, avantage dans la réussite. Si un point de peur, désavantages dans la réussite.

    Si c'est une réussite, l'action prinicipale est réussi, l'objectif atteint
    Si c'est un echec, l'action principale est raté, suivant la gravité de la situation ou de l'action, l'opération peut être retentée
    Sur un autre plan, si un point de peur est obtenue (jet avec peur), alors des soucis peuvent s'ajouter : temps, dépense energétique plus importante, argent (ammende), réparation, blessure de la personne ou de l'équipage, controle, arraisonnement etc... qui ne rentre pas dans le champs de l'action. Si un point d'espoir est obtenue, c'est l'inverse, des gains peuvent être obtenue,temps, réduction de frais, réduction de dommages (ce n'était pas si grave), dépense énérgétique moindre.
    Ici c'est véritablement laissé dans le flou car il faudra voir les cas d'utilisation.

## Compétences
comme le jeu sera plus dirigiste que Daggerheart, les compétences ne seront pas libre, elle sont reprise de SWFFGU (génésys), mais épurée et ajusté dans un univers tel que conquete spatiale. Les voici

* Arme de poing : Ag, si doit se battre en surface ou dans des lieux mal-famés
* Arme montée : Fin, tir à partir de la position du pilote, arme dans l'axe du vaisseau
* Artillerie : Fin, utilisation des tourelles, et des armes non dirigé par le pilote
* Astrogation : Cn, compétence pour faire les sauts, connaître un système etc...
* Bas-fond : Ins, accès au marché noir, négociation dans ce cas
* Bidouillage : Fin, réparation de fortune, volontaire ou par ce qu'il que cela fonctionne
* Commandement: Pre, role d'organisation du vaisseau, capitaine de plusieurs personnes
* Corp à Corp : For, si doit se battre sans armes (bar, bagarre)
* Discretion : Fin, si doit passer inaperçu (n'importe quelle situation)
* Informatique : Cn, gestion des ordinateur, clé de sécurité etc...
* Ingénieurie : Cn, connaissances des technologie, autre que informatique, lié aux vaisseaux généralement
* Medecine : Cn, connaissance médical pour se soigner, et utiliser tous les outils sofistiqués associés.
* Négociation : Pre, négocation de contrat, ou d'achat ou de vente (si possible) et hors marché noir.
* Perception : Ins, au besoin, détection, rechercher etc...
* Pilotage : Fin, manipulation des vaisseaux
* Réparation : Ag, réparation physique avec outillage et matériel
* Resistance: For, résistance physique en cas de problème ou de dommage
* Tromperie : Ins, capaciter à mentir clairement, baratiner, faire croire à l'autre.



### Pour les vaisseaux :
Les compétences utilisé dans le cardre des manipulation de vaisseaux sont :
    * Arme montée, Artillerie, Astrogration, Bidouillage, Commandement, Informatique, Ingénieurie, Perception, Pilotage, Réparation.

#### Arme montée (fin)
    Cette compétence est utilisé par le pilote du vaisseau pour faire feu des armes qu'il controle. Celles-ci sont liées au mouvement du vaisseau. généralement dans l'axe de celui-ci. Certaine tourelle peuvent être asservies, et donc être ajouter au tir.
#### Artillerie (fin)
    Cette compétence est utilisé par les cannoniers pour toutes les armes non asservies par le pilote. généralement, il faut un canonier pour un système d'arme. Un système d'arme représente un ensemble d'arme, généralement du même type, pouvant couvrir une zone. Il y a les torpilles et les armes sur tourelles, ou cardassés.
#### Astrogration (Cn)
    Cette compétence permet d'améliorer le calculs des scanners de détection, des sauts et déplacement des vaisseaux. Elle est prioritaire sur informatique pour l'utilisation d'outils logiciels de détection, de sauts et de déplacement.
#### Bidouillage (Fin)
    La compétence peut être utilisé dans trois cas : tentative de réparation en urgence, tentative de réparation sans assez de pièce (au lieu de réparation), tentative d'amélioration. Elle sous entends une réduction de la fiabilité car généralement, bidouiller, c'est modifié hors zone standard d'utilisation.
#### Commandement (Pre)
    Compétence de coordination du vaisseau, en cas de multiple personnages dans la timonerie.
#### Discrétion (fin)
    Bizzarrement, pour un vaisseau, comme pour une personne, la compétence discrétion du pilote sera utilisé pour essayer de camoufler le vaisseau dans les zones dangereuses pour réduire sa détection.
#### Informatique (Cn)
    Compétences d'utilisation des moyens informatiques, de communications du vaisseau. Tant pour l'intrusion que pour l'utilisation standard des logiciels (survie, achat etc...)
#### Ingénieurie (Cn)
    Compétence de connaissance du fonctionnement des outils de haute technologies, moins prioritaire que l'informatique pour le travail sur les ordinateurs, l'ingénieurie permet de savoir quoi faire, la réparation et bidouillage permet de le réaliser.
#### Perception (Ins)
    Compétence qui sera sollicité pour tout ce qui détection, repérage, acquisition par senseur ou outils d'alarme.
#### Pilotage (Fin)
    La compétence maitresse du pilote, manoeuvre du vaisseau, tant pour s'approcher, s'ammarer, que d'éviter les tirs, poursuivre un autre vaisseau
#### Réparation (Ag)
    Compétence des réparations standards, sans urgence, avec le matériel et l'outillage adéquate. Reparer un bidoullage, c'est de le remettre en fonctionnement standard, avec la fiabilité attendu.

### Pour les personnages :
    Bien que techniquement, l'ensemble des compétences sont liés au personnage, les compétences suivantes touche l'individu :
    * Arme de poing, Corp à Corp, Discretion, Informatique, Medecine, Perception, Resistance

#### Arme de poing
    Si le personnage doit se défendre, ou attaquer une cible au sol, en ville, et qu'il peut être armé, il utilisera arme de poing. cela comprend aussi l'utilisation d'attaque au corps à corps avec l'utilisation d'une arme.
#### Corp à Corp
    Si le personnage doit se défendre, ou attaquer une cible au sol, en ville et qu'il n'est pas armé, il utilisera Corps à Corps.
#### Discretion
    capactité à se rendre invisible pour passer différent lieu, controle et autre zone dangereuse pour éviter combat ou d'attirer l'attention
#### Informatique
    utilisation des moyens informatiques pour communiquer, trouver un renseignement, pirater, acceder à des bases de données
#### Medecine (cn)
    connaissance médicale dans le but de soigner, pharmacologie etc... utilisation des outils liés à la médecine.
#### Perception
    capaciter à voir les choses dangereuses, qui sortent de l'ordinaire, à être sensible à son environnement.
#### Resistance
    capaciter à resister à toutes sortes d'agréssion: maladie, manque d'air, accélération brutale, dommage et autre bléssure. C'est aussi le sang froid, la maitrise de soi.
#### Tromperie
    La tromperie est la compétence qui est utilisé pour baratiner, mentir sciemment, tromper un personne

### Pour les commerces
    Compétences liés au commerce : Bas-fond, Négociation, Tromperie

#### Bas-fond
    Principalement permettre de trouver dans un lieu assez grand, et de façon peu légale, une marchandise, un service, une demande de missions de contrebande, de taffic ou autre.
#### Négociation
    La négociation intervient dans tout ce qui peut être commercialement négocié, amélioration des bénéfices, réduction des
#### Tromperie
    La tromperie est la compétence qui est utilisé pour retomber sur ses pattes, par le mensonge, le baratinage et l'embobinage

# cas d'utilisation
Pour information :  µPA n'est pas micron de PA, mais dixième de PA. en fonction du système de PA mis en place soit de la perte directe (gestion des µPA) ou perte d'un PA pour 10 µPA et un jet 1D10 sous le nombre de µPa restant, si réussi perte d'1 PA.

D'autres jet peuvent être demandés.

## manoeuvres standard de vaisseau
sont décrit les mouvements, et les actions que l'on entreprend avec un vaisseau classique en situation normale (hors combat)
### appontement d'une station
jet de pilotage(Fin) vs difficulté de station, cout : temps en PA, energie et argent.
* difficulté de la station : nombre de vaisseau présents (max 10) + diff de la station (base, ou 5) + diff environnement (0) + diff reglement (0)
* diff de la station : chaque station a une difficulté lié à sa constuction, le nombre de hangar disponible et leur taille, c'est une valeur qui représente la difficulté de trouver et d'attérir dans le temps.
* diff environnement : ce qui rajoute en difficulté et lié au lieu. Astéroïde, mauvaise visibilité suite à des nuages de poussières, pretubation des détecteurs etc... peut aller de 0 à 10.
* diff reglement : les différents réglements qui ne simplifie pas la vie, passage obliger, feu de circulation, protocol d'apontage, ou de déplacement, cela rajoute à la difficulté. Elle soit fixe soit lié à une formule de dés.
Objectif : attérire dans un hangare ou un espace cargo pour accéder à la station
Echec : le vaisseau ne peut s'apporcher en temps et en heure, plus l'échec est important, plus le cout, le temps et l'energie perdu est importante.
* En cas de Peur: lancer dans la table ci dessous
    * accident non personnel : perte de 1d3+1 µPA
    * accident personnel : dommage, ammende, temps : 1d6+1 µPA
    * embouteillage : 1d10+1 µPA
    * controle doannier : en fonction de la quanité de marchandise Dé(NbSCU).Test de detection de contrebande.
    * controle de police: 1d10+1 *PA
    *
### attérissage sur une planete

### appontement entre vaisseau

### calcul d'un saut hyper spatial

### pilotage conventionnel

## manoeuvre de combat de vaisseau

### pilote en combat

### tir en tant que pilote

### tir en tant que gunner

### regle de fuite

## Jet environnement :
### Control de police
### Control de douane
### dommage lvl
### Amende lvl


# ----- hors jeu, pour info --------------------

## Compétences de SW FFGU
| Compétence | Genesys | DaggerHeart | Justification |
| --- | --- | --- | --- |
| Artillerie | Agilité | Finesse | Manipulation précise d’un système mécanique (ex : canon). |
| Astrogation | Intelligence | Connaissance | Navigation spatiale (savoir). |
| Athlétisme | Vigueur | Force | Effort physique. |
| Calme | Présence | Présence | Maîtrise de soi (fusion avec Sang-froid). |
| Charme | Présence | Présence | Influence sociale. |
| Coercition | Volonté | Présence | Intimidation par l’apparence/charisme. |
| Commandement | Présence | Présence | Leadership. |
| Coordination | Agilité | Agilité | Mouvement global du corps (ex : esquive, équilibre). |
| Corps à Corps | Vigueur | Force | Combat physique. |
| Culture | Intelligence | Connaissance | Savoir culturel. |
| Discrétion | Agilité | Finesse | Précision des mouvements pour passer inaperçu. |
| Arme légère | Agilité | Finesse | Précision œil-main (ex : tir à l’arc, pistolet). |
| Arme lourde | Agilité | Agilité | Manipulation d’armes lourdes (mouvement corporel global). |
| Éducation | Intelligence | Connaissance | Savoir académique. |
| Informatique | Intelligence | Connaissance | Technologie. |
| Magouille | Ruse | Instinct | Connaissance instinctive des milieux criminels. |
| Mécanique | Intelligence | Finesse | Bricolage, réparation (précision technique). |
| Médecine | Intelligence | Connaissance | Savoir médical. |
| Négociation | Présence | Présence | Persuasion. |
| Bas-fond (ex-Système D) | Ruse | Instinct | Connaissance instinctive des milieux criminels. |
| Perception | Ruse | Instinct | Observation intuitive. |
| Pilotage | Agilité | Finesse | Coordination œil-main (manipulation du joystick). |
| Pugilat | Vigueur | Force | Combat à mains nues. |
| Résistance | Vigueur | Force | Endurance physique. |
| Sang-froid | Volonté | Présence | Maîtrise mentale (fusion avec Calme). |
| Survie | Ruse | Force | Endurance en milieu hostile. |
| Tromperie | Ruse | Instinct | Mensonge (intuition sociale). |
