# Squadro Game — version Dockerisée et refactorisée

Application PHP 8.3 / PostgreSQL permettant de jouer à Squadro en duel local ou via une table persistée en base. Cette version remplace l’interface initiale par une UI responsive, nettoie les modèles métier, centralise le bootstrap applicatif et durcit les opérations de session, de routage et de persistance.

## Lancement rapide

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

## Modes de jeu

### Duel local instantané

Une seule session contrôle les deux couleurs. C’est le mode le plus simple pour tester le plateau, les animations et les règles sans attendre un second joueur.

### Partie en ligne persistée

Le joueur créateur prend les blancs. La partie est enregistrée dans PostgreSQL et un second joueur peut la rejoindre depuis le lobby. Les coups sont sauvegardés dans la table `PartieSquadro` : plateau JSON, tour courant, statut, dernier coup, nombre de coups, vainqueur.

## Ce qui a été développé dans cette version

### UI / UX

- nouvelle interface globale `Squadro Arena` ;
- layout responsive desktop / tablette / mobile ;
- plateau 9×9 avec rails de vitesse autour du plateau 7×7 ;
- prévisualisation de destination au survol d’une pièce ;
- confirmation de coup avant exécution ;
- overlay de victoire ;
- panneaux latéraux : règles rapides, progression, historique, statistiques de partie ;
- badges de tour, mode, joueur et couleur ;
- lobby moderne : duel local, création en ligne, rejoindre une table, parties en cours ;
- feedback utilisateur par flash messages ;
- raccourcis clavier simples : `M` pour menu, `U` pour annuler le dernier coup de session.

### Fonctionnalités de jeu

- reset de partie ;
- annulation locale du dernier coup avec pile d’undo limitée ;
- historique de session ;
- synchronisation DB des coups si une partie persistée est ouverte ;
- calcul de progression blanc/noir ;
- stockage du tour courant ;
- stockage du vainqueur ;
- conservation du plateau en JSON robuste.

### Maintenabilité

- ajout de `Core/App.php` et `Core/bootstrap.php` pour centraliser : session, redirections, flash messages, DB, erreurs ;
- suppression des sorties parasites dans les modèles ;
- suppression des tests inline qui pouvaient polluer les headers HTTP ;
- modèles métier nettoyés : `PieceSquadro`, `PlateauSquadro`, `ActionSquadro`, `PartieSquadro`, `JoueurSquadro` ;
- accès DB réécrit dans `skel/PDOSquadro.skel.php` avec requêtes préparées et mapping explicite ;
- schéma SQL idempotent avec colonnes métier supplémentaires ;
- assets isolés dans `assets/css/app.css` et `assets/js/app.js` ;
- script de lint et smoke test dans `tests/` ;
- configuration Docker durcie : `session.use_strict_mode`, OPcache, `ServerName`, healthchecks.

## Arborescence principale

```text
Core/
  App.php              Bootstrap applicatif, sessions, flash, DB, undo
  bootstrap.php        Chargement minimal commun
Controlleur/
  index_squadro.php    Rendu principal du plateau
  traiteActionSquadro.php Actions de jeu
Modele/
  piece_squadro.php    Pièce : couleur, direction, sérialisation
  plateau_squadro.php  Plateau et vitesses
  action_squadro.php   Règles de déplacement et victoire
  PieceSquadroUI.php   Rendu HTML du plateau et de l’écran de jeu
  partieSquadro.php    Partie persistée
  joueurSquadro.php    Joueur
Vue/
  login.php            Connexion joueur
  choixAction.php      Lobby
  attente_joueur.php   Création partie en ligne
  partieAttente.php    Liste des parties en attente
  partiesEnCours.php   Liste des parties du joueur
SQL/
  squadro.sql          Schéma PostgreSQL idempotent
assets/
  css/app.css          Design system et plateau
  js/app.js            Prévisualisation destination / raccourcis
```

## Vérifications locales hors Docker

```bash
find . -name '*.php' -print0 | xargs -0 -n1 php -l
php tests/smoke.php
```

Le smoke test vérifie le modèle de plateau, un déplacement blanc de base et la sérialisation JSON.

## Notes techniques

Le mode en ligne reste volontairement simple : il n’utilise pas de WebSocket. Chaque joueur peut utiliser le bouton de synchronisation ou recharger la page pour récupérer l’état courant depuis PostgreSQL. La base contient déjà les champs nécessaires pour poursuivre vers une version temps réel plus avancée.
