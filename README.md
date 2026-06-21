# Squadro Game

Application PHP permettant de jouer à Squadro avec une persistance PostgreSQL pour les joueurs et les parties.

## Lancement avec Docker

Prérequis unique : Docker Desktop ou Docker Engine avec Docker Compose.

```bash
docker compose up --build
```

Puis ouvrir :

- Application : http://localhost:8080
- Adminer : http://localhost:8081

Identifiants Adminer :

```text
Système : PostgreSQL
Serveur : db
Utilisateur : squadro_user
Mot de passe : password
Base : squadro_db
```

## Arrêt

```bash
docker compose down
```

Pour supprimer aussi les données PostgreSQL :

```bash
docker compose down -v
```

## Architecture Docker

- `app` : Apache + PHP 8.3 + extensions PDO/PostgreSQL.
- `db` : PostgreSQL 16 avec initialisation depuis `SQL/squadro.sql`.
- `adminer` : interface web optionnelle pour consulter la base.

La configuration de base de données est fournie par Docker Compose via les variables :

```text
sgbd=pgsql
host=db
database=squadro_db
user=squadro_user
password=password
```

Un fichier `.env.php` fournit les mêmes valeurs par défaut pour éviter une configuration manuelle.

## Structure du projet

```text
Controlleur/  Contrôleurs et routing applicatif
Modele/       Classes métier Squadro
Vue/          Pages PHP affichées à l'utilisateur
SQL/          Schéma PostgreSQL
skel/         Couche PDO
Logo/         Assets graphiques
```

## Corrections intégrées

- Ajout d'un `Dockerfile`, d'un `docker-compose.yml` et d'une `.dockerignore`.
- Ajout de la configuration `.env.php` compatible Docker.
- Suppression des sorties de debug qui bloquaient les redirections HTTP.
- Correction du `require_once` cassé dans le contrôleur principal.
- Correction des chemins SQL avec `__DIR__` pour fiabiliser l'exécution Docker/Apache.
- Correction de la sérialisation JSON du plateau : les objets `PieceSquadro` sont maintenant correctement persistés et restaurés.
- Correction du flux de connexion et des sessions (`joueur`, `etat`, `couleur`, `plateau`).
- Correction des pages de menu et de parties pour éviter les `header already sent`.
- Correction du lien vers une page inexistante pour reprendre une partie.
- Initialisation automatique du plateau si la session n'en contient pas.

## Commandes de vérification locale sans Docker

Ces commandes vérifient au minimum que les fichiers PHP sont syntaxiquement valides :

```bash
find . -name '*.php' -not -path './.git/*' -print0 | xargs -0 -I{} php -l {}
```

Test rapide de la sérialisation du plateau :

```bash
php -r 'require "Modele/plateau_squadro.php"; $p=new PlateauSquadro(); $q=PlateauSquadro::fromJson($p->toJson()); echo get_class($q->getPiece(1,0)).PHP_EOL;'
```

## Correctif du 21/06/2026

- Suppression des balises `?>` finales dans les fichiers PHP purs afin d'éviter toute sortie blanche avant `session_start()` et `header()`.
- Suppression du bloc de tests non protégé dans `Modele/array_piece_squadro.php`.
- Ajout de `ServerName localhost` dans l'image Apache pour supprimer l'avertissement `AH00558`.
