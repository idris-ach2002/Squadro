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