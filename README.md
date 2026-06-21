# Squadro - Arène Spartiate

Projet de jeu web réalisé en **Licence 3 Informatique** à l'**Université Le Havre Normandie**. Le dépôt contient une implémentation jouable du jeu Squadro, enrichie par une interface thématique grecque/spartiate, une persistance PostgreSQL et un environnement Docker reproductible.

L'objectif du projet est double : proposer une expérience de jeu utilisable immédiatement dans un navigateur, et conserver une organisation de code suffisamment lisible pour rendre le moteur, la persistance et l'interface maintenables séparément.

---

## 1. Présentation générale

Squadro est un jeu abstrait à deux camps. Chaque joueur dispose de cinq pièces. Une pièce avance selon une vitesse associée à sa ligne ou à sa colonne ; lorsqu'elle atteint l'extrémité opposée, elle change de sens et doit revenir jusqu'à sa zone de départ. Le premier camp qui réussit à faire sortir quatre pièces remporte la partie.

Cette version ajoute une couche applicative complète autour du moteur de base :

- accueil et lobby thématisés ;
- parties locales ;
- parties persistées en base de données ;
- reprise d'une partie enregistrée ;
- assistance tactique par Oracle ;
- statistiques de partie ;
- historique des coups ;
- annulation limitée ;
- export JSON ;
- interface stabilisée afin que le plateau ne se décale pas après un coup.

Le projet est volontairement exécutable avec Docker afin d'éviter l'installation manuelle de PHP, d'extensions PHP ou de PostgreSQL sur la machine de développement.

---

## 2. Stack technique

| Couche | Choix technique | Rôle |
| --- | --- | --- |
| Runtime applicatif | PHP 8.x avec Apache | Exécution serveur, sessions, rendu HTML côté serveur |
| Base de données | PostgreSQL 16 | Persistance des joueurs, parties, plateaux sérialisés et métadonnées |
| Administration DB | Adminer | Inspection rapide de la base en développement |
| Conteneurisation | Docker Compose | Lancement reproductible de l'application et de la base |
| Interface | HTML, CSS, JavaScript natif | Plateau interactif, thème graphique, raccourcis, AJAX |
| Tests | PHP lint, smoke tests, vérification JS | Contrôle minimal de non-régression |

Le projet n'utilise pas de framework PHP lourd. Ce choix réduit le bruit structurel et rend le découpage MVC plus visible dans le cadre d'un projet universitaire.

---

## 3. Lancement rapide

### 3.1. Prérequis

- Docker Engine ;
- Docker Compose ;
- un navigateur récent.

### 3.2. Démarrage propre

```bash
docker compose down -v
docker compose up --build
```

Application : <http://localhost:8080>  
Adminer : <http://localhost:8081>

### 3.3. Identifiants Adminer

```text
Système     : PostgreSQL
Serveur     : db
Utilisateur : squadro_user
Mot de passe: password
Base        : squadro_db
```

L'option `-v` supprime le volume PostgreSQL existant. Elle est utile lorsque le schéma ou les données d'initialisation ont changé. Pour conserver les parties déjà créées, lancer simplement :

```bash
docker compose up --build
```

---

## 4. Parcours utilisateur

### 4.1. Accueil

L'utilisateur saisit un nom de joueur. Le nom est validé côté serveur : il est obligatoire et limité à quarante caractères. Après création ou récupération du profil joueur, la session applicative est initialisée et l'utilisateur rejoint le lobby.

### 4.2. Lobby

Le lobby donne accès aux principaux modes :

- **Duel local** : deux camps sont joués sur la même machine ;
- **Oracle** : le joueur joue contre une aide tactique automatique ;
- **Parties en attente** : une table créée par un joueur peut être rejointe par un second joueur ;
- **Parties en cours** : une partie persistée peut être consultée ou reprise selon son état.

### 4.3. Partie

Le plateau est l'écran central du jeu. Les pièces jouables sont activées uniquement lorsque leur couleur correspond au tour courant. Par défaut, un clic sur une pièce applique directement le coup. Un mode avec confirmation existe également, mais sa barre est fixe afin d'éviter les déplacements de page.

### 4.4. Victoire

Une victoire est détectée lorsqu'un camp a terminé le trajet aller-retour d'au moins quatre pièces. La partie est alors marquée comme terminée en session et, si elle est liée à une partie persistée, dans PostgreSQL.

---

## 5. Règles implémentées

### 5.1. Plateau

Le plateau logique est représenté par une grille `7 x 7`. Les bordures servent de zones de départ, de retour ou de sortie. Les cases centrales forment l'espace de croisement des trajectoires.

### 5.2. Vitesses

Les vitesses de déplacement sont portées par le plateau :

- les pièces blanches utilisent des vitesses associées à leurs lignes ;
- les pièces noires utilisent des vitesses associées à leurs colonnes ;
- une pièce n'a pas forcément la même vitesse à l'aller et au retour.

### 5.3. Direction et retournement

Chaque pièce possède une direction. Lorsqu'elle atteint son extrémité opposée, sa direction est inversée. Elle doit ensuite revenir vers sa zone de départ. La sortie finale de la pièce contribue au score de victoire du camp.

### 5.4. Recul d'une pièce adverse

Lorsqu'un déplacement rencontre une pièce adverse sur sa trajectoire, la pièce adverse est renvoyée vers sa position de départ logique selon les règles du jeu. Cette mécanique est prise en compte par le moteur d'action et par l'analyse tactique de l'Oracle.

---

## 6. Architecture du projet

```text
squadro_game/
|-- Core/
|   |-- App.php                  session, flash messages, settings, stats, sécurité
|   `-- bootstrap.php            chargement central des classes métier
|-- Controlleur/
|   |-- index_squadro.php        entrée historique du plateau
|   `-- traiteActionSquadro.php  contrôleur principal des actions de jeu
|-- Modele/
|   |-- piece_squadro.php        état d'une pièce, couleur, direction, sérialisation
|   |-- plateau_squadro.php      grille logique, vitesses, métriques de progression
|   |-- action_squadro.php       application effective d'un coup
|   |-- SquadroAnalyzer.php      analyse tactique et Oracle
|   |-- PieceSquadroUI.php       rendu HTML du plateau et des panneaux de jeu
|   |-- joueurSquadro.php        entité joueur
|   `-- partieSquadro.php        entité partie persistée
|-- Vue/
|   |-- login.php                page d'accueil
|   |-- choixAction.php          lobby principal
|   |-- partieAttente.php        création de partie en attente
|   |-- partiesEnCours.php       liste des parties persistées
|   `-- attente_joueur.php       attente du second joueur
|-- skel/
|   `-- PDOSquadro.skel.php      accès PostgreSQL
|-- SQL/
|   `-- squadro.sql              schéma et migrations idempotentes
|-- assets/
|   |-- css/                     thème, plateau, lobby, accueil
|   `-- js/app.js                AJAX, raccourcis, stabilisation du plateau
|-- tests/
|   |-- lint.sh                  vérification syntaxique PHP
|   `-- smoke.php                tests rapides du moteur
|-- Dockerfile
|-- docker-compose.yml
`-- README.md
```

Le découpage reste volontairement simple : les modèles contiennent les règles, les vues construisent l'interface, les contrôleurs orchestrent les actions utilisateur et la couche `skel/PDOSquadro.skel.php` isole les requêtes SQL.

---

## 7. Cycle d'une action de jeu

1. L'utilisateur clique une pièce jouable ou déclenche un raccourci clavier.
2. Le JavaScript mémorise la position visuelle du plateau.
3. La requête est envoyée au contrôleur `traiteActionSquadro.php`.
4. Le contrôleur vérifie le tour actif, la couleur autorisée et la validité de la position.
5. `SquadroAnalyzer` produit une analyse prévisionnelle du coup.
6. `ActionSquadro` applique réellement le déplacement au plateau.
7. La session est mise à jour : plateau, tour, historique, statistiques, pile d'annulation.
8. Si la partie est persistée, PostgreSQL reçoit le nouvel état sérialisé.
9. En AJAX, seul le shell de jeu est remplacé dans le DOM.
10. Le JavaScript restaure la position visuelle du plateau pour éviter tout saut de page.

Cette séquence est importante : l'interface peut être dynamique, mais la règle de jeu reste validée côté serveur.

---

## 8. Persistance PostgreSQL

Le schéma principal repose sur deux tables :

- `JoueurSquadro` : identité minimale d'un joueur ;
- `PartieSquadro` : joueurs associés, état de la partie, plateau JSON, tour courant, vainqueur, dernier coup et compteur de coups.

Les colonnes ajoutées progressivement sont protégées par `IF NOT EXISTS`, ce qui rend le script SQL rejouable en développement.

États possibles d'une partie :

| État | Signification |
| --- | --- |
| `initialized` | partie locale ou structure créée mais non exposée |
| `waitingForPlayer` | partie en ligne créée par un joueur et disponible |
| `active` | deux joueurs sont présents ou la partie a démarré |
| `finished` | victoire détectée et partie clôturée |

Le plateau est sérialisé en JSON afin de conserver la structure complète sans multiplier les tables techniques. Pour un projet de plus grande taille, une normalisation plus fine pourrait être envisagée, mais la représentation JSON est adaptée à un état de jeu compact et fortement structuré.

---

## 9. Fonctionnalités de gameplay

### 9.1. Déplacement instantané

Le déplacement instantané est le mode par défaut. Il supprime l'ancien flux en deux temps qui obligeait à sélectionner une pièce puis à descendre dans la page pour confirmer. Cette correction améliore directement la jouabilité.

### 9.2. Confirmation fixe

Le mode confirmation reste disponible pour les utilisateurs qui veulent valider explicitement chaque action. La barre de confirmation est fixe et ne provoque plus de saut vertical.

### 9.3. Oracle tactique

L'Oracle évalue les coups disponibles et propose le meilleur coup selon une heuristique. L'analyse tient compte notamment :

- de la distance parcourue ;
- de la progression vers la sortie ;
- des reculs adverses ;
- des sorties immédiates ;
- des situations de menace.

L'Oracle peut être utilisé ponctuellement ou activé comme adversaire automatique.

### 9.4. Historique et statistiques

Chaque coup joué enrichit un historique de session. Les statistiques suivent les coups blancs, les coups noirs, les reculs, les sorties, les coups joués par l'Oracle et le plus long déplacement.

### 9.5. Annulation

Une pile d'annulation limitée conserve les derniers états de plateau. Elle permet de corriger une erreur de manipulation sans exposer l'application à une croissance non maîtrisée de la session.

### 9.6. Export JSON

L'export JSON donne une photographie de l'état de partie : mode, tour, paramètres, statistiques, historique et plateau. Cette fonctionnalité facilite le débogage et la reproduction d'un scénario.

---

## 10. Interface et expérience utilisateur

L'interface adopte une direction artistique grecque/spartiate : couleurs bronze, rouge sombre, noir obsidienne, panneaux d'arène, symboles angulaires et vocabulaire de bataille. Cette couche graphique est isolée dans les fichiers CSS afin de ne pas polluer le moteur de jeu.

Fichiers principaux :

- `assets/css/app.css` : base générale ;
- `assets/css/sparta-home.css` : écran d'accueil ;
- `assets/css/greek-game.css` : lobby, plateau, panneaux et interactions ;
- `assets/js/app.js` : comportement dynamique.

L'amélioration la plus sensible concerne la stabilité du plateau. Les actions de jeu sont traitées en AJAX lorsque c'est possible. Avant l'envoi, la position du plateau est mémorisée ; après le remplacement du contenu, le script corrige le scroll pour conserver la même position apparente. Le joueur n'a donc plus l'impression que le plateau descend ou remonte après chaque coup.

---

## 11. Raccourcis clavier

| Raccourci | Effet |
| --- | --- |
| `1` à `5` | joue l'une des pièces disponibles du camp actif |
| `O` | demande à l'Oracle de jouer un coup |
| `U` | annule le dernier coup si l'état est disponible |
| `F` | active le mode plein écran du plateau |
| `M` | retourne au menu |
| `Entrée` | confirme le coup en mode confirmation |
| `Échap` | annule la confirmation en cours |

Les raccourcis sont désactivés lorsque le focus se trouve dans un champ de formulaire afin de ne pas perturber la saisie utilisateur.

---

## 12. Qualité et vérification

### 12.1. Vérification syntaxique PHP

```bash
find . -path './.git' -prune -o -name '*.php' -print0 | xargs -0 -n1 php -l
```

### 12.2. Vérification JavaScript

```bash
node --check assets/js/app.js
```

### 12.3. Smoke tests métier

```bash
php tests/smoke.php
```

Les smoke tests ne remplacent pas une campagne complète de tests unitaires, mais ils vérifient rapidement que les classes essentielles peuvent être chargées et que le moteur produit un plateau exploitable.

---

## 13. Sécurité applicative

Le projet applique plusieurs précautions simples :

- sessions initialisées dans un bootstrap central ;
- régénération de session après identification ;
- sorties HTML échappées via `App::e()` ;
- validation serveur des actions ;
- requêtes SQL centralisées dans la couche PDO ;
- séparation des messages flash et du rendu ;
- limitation de l'undo stack pour éviter une session trop volumineuse.

Le projet reste un prototype universitaire. Une mise en production réelle nécessiterait au minimum HTTPS, gestion d'utilisateurs authentifiés, secrets hors dépôt, CSRF tokens et journalisation plus stricte.

---

## 14. Limites connues

- Le mode en ligne reste volontairement simple : il repose sur la persistance en base et non sur des WebSockets.
- L'Oracle est heuristique ; il ne calcule pas un arbre de recherche complet.
- L'état du plateau est sérialisé en JSON, ce qui est pratique mais moins interrogeable qu'un modèle relationnel entièrement normalisé.
- Les tests couvrent surtout le chargement et les scénarios critiques. Une suite PHPUnit complète serait la prochaine étape naturelle.

---

## 15. Pistes d'évolution

- Ajouter une authentification complète des joueurs.
- Introduire un vrai matchmaking avec attente asynchrone.
- Ajouter un mode spectateur.
- Remplacer le polling par WebSocket ou Server-Sent Events.
- Développer une IA plus profonde avec minimax ou Monte Carlo.
- Ajouter une suite PHPUnit structurée par classe métier.
- Introduire des migrations versionnées plutôt qu'un script SQL unique.
- Ajouter une page de replay coup par coup.
- Préparer un build de production avec variables d'environnement séparées.

---

## 16. Commande de commit proposée

```bash
git add . && git commit -m "docs: rédiger la documentation académique de Squadro"
```
