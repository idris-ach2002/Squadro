```
Pour un projet universitaire, je dois développer le jeu Squadro. Ce jeu a différentes règles à suivre, qui sont les suivantes : 
- les pièces sont soient de couleur blanche, soit de couleur noire
- sur le plateau, les pièces noires partent de la ligne 7, et doivent revenir à la ligne 1, et les pièces blanches partent de la colonne 7 et font leur retour sur la ligne 1
- une pièce se déplace du nombre de cases indiquées coté départ à l'aller, coté retournement lors du retour ;
- une pièce peut se déplacer si sa case d'arrivée est libre ;
- si une pièce saute au dessus d'une ou deux pièces adverses lors de son mouvement, alors la ou les pièces adverses enjambées sont renvoyées à leur case départ ou à leur case de retournement (si elles sont dans leur phase retour) ;
- Lorsqu'une pièce a achevé son aller-retour, elle est retirée de la partie

De haut en bas, les cinq lignes où circulent les pièces blanches permettent de les déplacer à l'aller sur 1, 3, 2, 3 et 1 case ; au retour la vitesse des déplacements changent et se font sur 3, 1, 2, 1, 3 cases.

De gauche à droite, les cinq colonnes où circulent les pièces noires permettent de les déplacer à l'aller sur 3, 1, 2, 1 et 3 cases ; au retour les déplacement changent et se font sur 1, 3, 2, 3, 1 case.

Dis moi lorsque tu auras lu ce texte, et si tu l'as compris. Ensuite, je te demdanderais de développer certaines méthodes de ce projet. 

Dis moi quand tu es prêt.
```

Ce premier prompt permet à l'IA de connaitre le projet sur lequel nous travaillons. Il lui explique les règles, le fonctionnement, et ce qui est attendu.


```
Bien, maintenant, je vais te demander de me développer le code PHP des méthodes d'initilisation de la classe PieceSquadro.
Cette classe gère à la fois les pièces du jeu et les emplacements du plateau. On considère donc également les emplacements vides et neutres du plateau comme des pièces. Ainsi, une pièce (ou une case du plateau) est caractérisée par une couleur et une direction. Pour les représenter, on utilise des constantes entières : BLANC, NOIR, VIDE, NEUTRE, NORD, EST, SUD et OUEST. Le constructeur est privé pour imposer l'utilisation des méthodes statiques initVide, initNoirNord, etc. pour générer des pièces correctes à l'aide des constantes de classe.

Ici, je veux que tu me développes les méthodes d'initialisation de ces pièces. Ces méthodes doivent être static et public. Chacune d'entre elle doit retourner une PieceSquadro

Voici la liste des méthodes que tu dois implémenter : 
- initVide
- initNeutre
- initNoirNord
- initNoirSud
- initBlancEst
- initBlancOuest
```

L'IA a bien développé le code attendu. Les méthodes sont bien avec la bonne visibilité, et les méthodes devant être statiques le sont bien aussi. 
Ensuite, l'IA a bien fait des `return` dans ces différentes méthodes, avec la création de notre objet.



```
Maintenant, il faut que tu me développes les méthodes suivantes : 
- toJSon : qui doit retourner un String, elle permet de formater notre objet PieceSquadro comme un fichier JSon
- fromJSon : qui prend en paramètre un String (le json a reformatter), elle retorune une PieceSquadro à partir de ce qui a été mis en paramètre

fromJSon est une méthode static

toJson et fromJSon doivent etre public
```

L'IA a bien développé le code attendu pour ces deux méthodes. Cependant, elle n'a pas vérifié les exceptions que pourraient retourner ces deux méthodes.



```
Maintenant, nous allons passer à la classe PlateauSquadro. Cette classe permet de gérer le plateau de jeu. 

Cette fois, je vais te demander de code cette classe. 
Cette classe contient les attributs suivants : 
- BLANC_V_ALLER : constante qui doivent avoir les valeurs suivantes : {0, 1, 3, 2, 3, 1, 0}
- NOIR_V_RETOUR : constante qui doivent avoir les valeurs suivantes : {0, 1, 3, 2, 3, 1, 0}
- BLANC_V_RETOUR : constante qui doivent avoir les valeurs suivantes : {0, 3, 1, 2, 1, 3, 0}
- NOIR_V_ALLER : constante qui doivent avoir les valeurs suivantes : {0, 3, 1, 2, 1, 3, 0}
- plateau : un tableau qui doit avoir 7 instances d'ArrayPieceSquadro (un par ligne), la première ligne est la ligne de retournement des pièces noires, la septième ligne est la ligne de départ des pièces noires. Les colonnes sont organisées selon le même principe : la première colonne contient les cases de départ des pièces blanches...
- lignesJouables : tableau qui doit être initialiser avec les valeurs suivantes : {1, 2, 3, 4, 5}
- colonnesJouables : tableau qui doit être initialiser avec les valeurs suivantes : {1, 2, 3, 4, 5}

Ici, lignesJouables et colonnesJouables ne sont pas des constantes. 

BLANC_V_ALLER, NOIR_V_RETOUR, BLANC_V_RETOUR, NOIR_V_ALLER sont des constantes public
plateau, lignesJouables et colonnesJouables sont des des attributs privés


Ensuite, tu trouveras en image le diagramme de classe, tu auras donc seulement la classe PlateauSquadro. Dans cette dernière, tu devras coder les différentes méthodes et constructeurs. 
Le moins signifie privé, le plus public, et lorsqu'elles sont soulignés, elles sont statics. Dans les parenthèses, se sont les paramètres des méthodes,et après les : le type de retour (void signifie qu'elle ne doit rien retourner)


Les tableaux lignesJouables et colonnesJouables indiquent les indices des lignes et des colonnes des pièces encore en jeu (n'ayant pas fini leur aller-retour).

Le constructeur par défaut produit le plateau à son état initial ; il utilisent les méthodes privées initCasesVides, initCasesNeutres, ... 

Les méthodes retireLigneJouable() et retireColonneJouable() éliminent respectivement l'index passé en paramètres des tableaux lignesjouables et colonnesJouables.

La méthode getCoordDestination() calcule les coordonnées de déplacement de la pièce située en (x,y) ; la méthode  getDestination() retourne la pièce située aux coordonnées calculées par la méthode précédente.


Si tu as compris toutes les consignes, je te laisse faire ce code php.
```