<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/bootstrap.php';
require_once __DIR__ . '/../Modele/PieceSquadroUI.php';
require_once __DIR__ . '/../Modele/partieSquadro.php';

App::requirePlayer();
App::initDatabase();

$game = null;
if (isset($_SESSION[App::SESSION_GAME_ID])) {
    $game = PDOSquadro::getPartieSquadroById((int) $_SESSION[App::SESSION_GAME_ID]);
    if ($game instanceof PartieSquadro) {
        $_SESSION[App::SESSION_BOARD] = $game->getPlateau();
        $_SESSION[App::SESSION_TURN] = $game->getCurrentTurn();
        if ($game->getGameStatus() === 'finished' && $game->getWinner() !== null) {
            $_SESSION[App::SESSION_STATE] = 'Victoire';
            $_SESSION[App::SESSION_TURN] = $game->getWinner();
        }
    } else {
        unset($_SESSION[App::SESSION_GAME_ID], $_SESSION[App::SESSION_PLAYER_COLOR]);
        App::flash('warning', 'La partie demandée n’existe plus. Une partie locale a été créée.');
    }
}

$plateau = App::ensureBoard();
$_SESSION[App::SESSION_STATE] = $_SESSION[App::SESSION_STATE] ?? 'choixPiece';
$_SESSION[App::SESSION_TURN] = $_SESSION[App::SESSION_TURN] ?? 'blanc';
$_SESSION[App::SESSION_MODE] = $_SESSION[App::SESSION_MODE] ?? ($game ? 'online' : 'local');

$activeColor = App::activeTurnLabel();
$mode = (string) $_SESSION[App::SESSION_MODE];
$playerColor = $_SESSION[App::SESSION_PLAYER_COLOR] ?? null;
$allowMoves = true;

if ($mode === 'online' && is_string($playerColor)) {
    $allowMoves = $playerColor === $activeColor;
}

$selected = null;
if (isset($_SESSION['position']) && is_array($_SESSION['position']) && count($_SESSION['position']) >= 2) {
    $selected = [(int) $_SESSION['position'][0], (int) $_SESSION['position'][1]];
}

$destination = PieceSquadroUI::destinationOf($plateau, $selected);

if ($_SESSION[App::SESSION_STATE] === 'Victoire') {
    $allowMoves = false;
}

echo PieceSquadroUI::renderGamePage($plateau, [
    'mode' => $mode,
    'player' => App::currentPlayer(),
    'playerColor' => is_string($playerColor) ? $playerColor : null,
    'activeColor' => $activeColor,
    'allowMoves' => $allowMoves,
    'state' => (string) $_SESSION[App::SESSION_STATE],
    'selected' => $selected,
    'destination' => $destination,
    'game' => $game,
    'flashes' => App::consumeFlash(),
]);
