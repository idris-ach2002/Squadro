# Squadro — Arène Spartiate

Version Dockerisée et refactorisée du jeu Squadro en PHP/PostgreSQL, avec interface grecque/spartiate, lobby, parties locales, parties persistées et assistance tactique.

## Lancement

```bash
docker compose down -v
docker compose up --build
```

Application : <http://localhost:8080>  
Adminer : <http://localhost:8081>

Identifiants Adminer :

```text
Système : PostgreSQL
Serveur : db
Utilisateur : squadro_user
Mot de passe : password
Base : squadro_db
```

## Nouveautés majeures

- Interface globale grecque/spartiate : accueil, agora, plateau, panels, historique, victoire.
- Déplacement instantané par défaut : cliquer une pièce applique le coup, sans bouton de confirmation en bas de page.
- Mode confirmation fixe disponible : la barre de validation reste collée en bas de l’écran, sans scroll.
- Assistance tactique “Oracle” : meilleur coup calculé, score, effets, menaces détectées.
- Mode contre l’Oracle : le joueur joue les blancs, l’Oracle répond automatiquement avec les noirs.
- Liste des 5 meilleurs coups jouables à chaque tour.
- Statistiques de bataille : nombre de coups, reculs infligés, sorties, coups Oracle, plus long déplacement.
- Historique enrichi avec destination, recul, demi-tour et sortie.
- Export JSON de la partie courante.
- Raccourcis clavier : `1–5`, `O`, `U`, `F`, `M`, `Entrée`, `Échap`.
- Plein écran plateau.
- Paramètres in-game : déplacement instantané/confirmation, assistance, coordonnées, effets, Oracle automatique.

## Structure principale

```text
Core/App.php                      Bootstrap applicatif, session, settings, stats
Controlleur/traiteActionSquadro.php Contrôleur d’actions de jeu
Modele/action_squadro.php         Moteur de déplacement
Modele/plateau_squadro.php        Plateau et vitesses
Modele/SquadroAnalyzer.php        Analyse tactique, coups légaux, Oracle
Modele/PieceSquadroUI.php         Rendu HTML du jeu
assets/css/app.css                Base UI
assets/css/sparta-home.css        Page d’accueil spartiate
assets/css/greek-game.css         Skin globale grecque/spartiate
assets/js/app.js                  Prévisualisation, raccourcis, fullscreen
SQL/squadro.sql                   Schéma PostgreSQL
```

## Tests rapides

```bash
find . -path './.git' -prune -o -name '*.php' -print0 | xargs -0 -n1 php -l
php tests/smoke.php
```
