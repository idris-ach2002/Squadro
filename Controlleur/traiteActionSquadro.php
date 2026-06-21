<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/bootstrap.php';
require_once __DIR__ . '/../Modele/action_squadro.php';
require_once __DIR__ . '/../Modele/PieceSquadroUI.php';

App::requirePlayer();
App::initDatabase();

function currentGame(): ?PartieSquadro
{
    if (!isset($_SESSION[App::SESSION_GAME_ID])) {
        return null;
    }

    return PDOSquadro::getPartieSquadroById((int) $_SESSION[App::SESSION_GAME_ID]);
}

function syncGameToDatabase(?string $winner = null, ?string $lastMove = null): void
{
    if (!isset($_SESSION[App::SESSION_GAME_ID])) {
        return;
    }

    $game = currentGame();
    if (!$game instanceof PartieSquadro) {
        return;
    }

    $plateau = App::ensureBoard();
    $status = $winner !== null ? 'finished' : ($game->hasSecondPlayer() ? 'active' : $game->getGameStatus());
    $moveCount = $game->getMoveCount() + ($lastMove !== null ? 1 : 0);

    PDOSquadro::savePartieSquadro(
        $status,
        $plateau->toJson(),
        (int) $_SESSION[App::SESSION_GAME_ID],
        App::activeTurnLabel(),
        $winner,
        $lastMove,
        $moveCount
    );
}

function parsePositionFromRequest(string $color): ?array
{
    $raw = $_POST[$color] ?? $_GET[$color] ?? null;
    if (!is_string($raw) || !preg_match('/^btn([0-6])-([0-6])$/', $raw, $matches)) {
        return null;
    }

    return [(int) $matches[1], (int) $matches[2]];
}

function ensureColorPlayable(string $color): bool
{
    $active = App::activeTurnLabel();
    if ($color !== $active) {
        App::flash('warning', 'Ce n’est pas le tour des pièces ' . $color . '.');
        return false;
    }

    if (($_SESSION[App::SESSION_MODE] ?? 'local') === 'online') {
        $playerColor = $_SESSION[App::SESSION_PLAYER_COLOR] ?? null;
        if (is_string($playerColor) && $playerColor !== $active) {
            App::flash('warning', 'En mode en ligne, vous devez attendre le tour de votre couleur.');
            return false;
        }
    }

    return true;
}

function selectPiece(string $color): void
{
    if (!ensureColorPlayable($color)) {
        $_SESSION[App::SESSION_STATE] = 'choixPiece';
        App::redirect('/Controlleur/index_squadro.php');
    }

    $position = parsePositionFromRequest($color);
    if ($position === null) {
        $_SESSION[App::SESSION_STATE] = 'erreur';
        App::flash('danger', 'Position de pièce invalide.');
        App::redirect('/Controlleur/index_squadro.php');
    }

    $_SESSION['position'] = $position;
    $_SESSION[App::SESSION_TURN] = $color;
    $_SESSION[App::SESSION_STATE] = 'ConfirmationPiece';
    App::redirect('/Controlleur/index_squadro.php');
}

function confirmMove(): void
{
    $plateau = App::ensureBoard();
    $color = App::activeTurnLabel();
    $position = $_SESSION['position'] ?? null;

    if (!is_array($position) || count($position) < 2) {
        $_SESSION[App::SESSION_STATE] = 'erreur';
        App::flash('danger', 'Aucune pièce n’était sélectionnée.');
        return;
    }

    $x = (int) $position[0];
    $y = (int) $position[1];
    $destination = PieceSquadroUI::destinationOf($plateau, [$x, $y]);

    App::pushUndo($plateau, $color);

    $action = new ActionSquadro($plateau);
    if (!$action->jouePiece($x, $y)) {
        $_SESSION[App::SESSION_STATE] = 'erreur';
        App::flash('danger', 'Déplacement impossible : cette pièce ne peut pas être jouée.');
        return;
    }

    unset($_SESSION['position']);
    $_SESSION[App::SESSION_BOARD] = $plateau;

    $moveLabel = ucfirst($color) . ' : [' . $x . ',' . $y . ']';
    if ($destination) {
        $moveLabel .= ' → [' . $destination[0] . ',' . $destination[1] . ']';
    }
    App::addHistory($moveLabel);

    $colorInt = $color === 'blanc' ? PieceSquadro::BLANC : PieceSquadro::NOIR;
    if ($action->remporteVictoire($colorInt)) {
        $_SESSION[App::SESSION_STATE] = 'Victoire';
        $_SESSION[App::SESSION_TURN] = $color;
        syncGameToDatabase($color, $moveLabel);
        return;
    }

    $_SESSION[App::SESSION_TURN] = App::oppositeColor($color);
    $_SESSION[App::SESSION_STATE] = 'choixPiece';
    syncGameToDatabase(null, $moveLabel);
}

if (isset($_POST['menu'])) {
    App::redirect('/Vue/choixAction.php');
}

if (isset($_POST['sync'])) {
    App::flash('success', 'Synchronisation effectuée.');
    App::redirect('/Controlleur/index_squadro.php');
}

if (isset($_POST['undo'])) {
    $state = App::popUndo();
    if ($state === null) {
        App::flash('warning', 'Aucun coup à annuler dans cette session.');
    } else {
        $_SESSION[App::SESSION_BOARD] = PlateauSquadro::fromJson((string) $state['plateau']);
        $_SESSION[App::SESSION_TURN] = (string) $state['turn'];
        $_SESSION[App::SESSION_STATE] = 'choixPiece';
        unset($_SESSION['position']);
        App::addHistory('Annulation : retour au tour ' . $_SESSION[App::SESSION_TURN]);
        syncGameToDatabase(null, 'Annulation locale');
    }
    App::redirect('/Controlleur/index_squadro.php');
}

if (isset($_POST['rejouer']) || isset($_GET['rejouer'])) {
    $player = $_SESSION[App::SESSION_PLAYER] ?? null;
    App::resetRuntimeGame(true);
    if ($player !== null) {
        $_SESSION[App::SESSION_PLAYER] = $player;
    }
    $_SESSION[App::SESSION_BOARD] = new PlateauSquadro();
    $_SESSION[App::SESSION_STATE] = 'choixPiece';
    $_SESSION[App::SESSION_TURN] = 'blanc';
    $_SESSION[App::SESSION_MODE] = 'local';
    App::flash('success', 'Nouvelle partie locale initialisée.');
    App::redirect('/Controlleur/index_squadro.php');
}

if (isset($_POST['blanc']) || isset($_GET['blanc'])) {
    selectPiece('blanc');
}

if (isset($_POST['noir']) || isset($_GET['noir'])) {
    selectPiece('noir');
}

if (isset($_POST['choix'])) {
    if ($_POST['choix'] === 'PRESEED') {
        confirmMove();
    } elseif ($_POST['choix'] === 'ABORT') {
        unset($_SESSION['position']);
        $_SESSION[App::SESSION_STATE] = 'choixPiece';
    }
    App::redirect('/Controlleur/index_squadro.php');
}

if (isset($_POST['erreur']) || isset($_GET['erreur'])) {
    unset($_SESSION['position']);
    $_SESSION[App::SESSION_STATE] = 'choixPiece';
    App::redirect('/Controlleur/index_squadro.php');
}

App::redirect('/Controlleur/index_squadro.php');
