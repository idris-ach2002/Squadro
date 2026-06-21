<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/bootstrap.php';
require_once __DIR__ . '/../Modele/action_squadro.php';
require_once __DIR__ . '/../Modele/SquadroAnalyzer.php';
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

/** @return ?array{0:string,1:int,2:int} */
function parseMoveToken(?string $token): ?array
{
    if (!is_string($token) || !preg_match('/^(blanc|noir):([0-6]):([0-6])$/', $token, $matches)) {
        return null;
    }

    return [$matches[1], (int) $matches[2], (int) $matches[3]];
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
            App::flash('warning', 'En ligne, vous devez attendre le tour de votre couleur.');
            return false;
        }
    }

    return true;
}

function pieceMatchesColor(PlateauSquadro $plateau, string $color, int $x, int $y): bool
{
    try {
        $piece = $plateau->getPiece($x, $y);
    } catch (Throwable) {
        return false;
    }

    $expected = $color === 'blanc' ? PieceSquadro::BLANC : PieceSquadro::NOIR;
    return $piece->isPlayable() && $piece->getCouleur() === $expected;
}

function playMove(string $color, int $x, int $y, string $source = 'manual'): bool
{
    if (!ensureColorPlayable($color)) {
        $_SESSION[App::SESSION_STATE] = 'choixPiece';
        return false;
    }

    $plateau = App::ensureBoard();
    if (!pieceMatchesColor($plateau, $color, $x, $y)) {
        $_SESSION[App::SESSION_STATE] = 'erreur';
        App::flash('danger', 'Cette pièce ne peut pas être jouée pour le camp ' . $color . '.');
        return false;
    }

    $move = SquadroAnalyzer::analyzeMove($plateau, $color, $x, $y);
    if ($move === null) {
        $_SESSION[App::SESSION_STATE] = 'erreur';
        App::flash('danger', 'Déplacement impossible : destination invalide.');
        return false;
    }

    App::pushUndo($plateau, $color);

    $action = new ActionSquadro($plateau);
    if (!$action->jouePiece($x, $y)) {
        $_SESSION[App::SESSION_STATE] = 'erreur';
        App::flash('danger', 'Déplacement impossible : cette pièce ne peut pas être jouée.');
        return false;
    }

    unset($_SESSION['position']);
    $_SESSION[App::SESSION_BOARD] = $plateau;

    $isOracle = in_array($source, ['oracle', 'bot'], true);
    App::recordMove($move, $isOracle);

    $moveLabel = (string) $move['label'];
    if (!empty($move['summary'])) {
        $moveLabel .= ' · ' . $move['summary'];
    }
    if ($isOracle) {
        $moveLabel = 'Oracle · ' . $moveLabel;
    }
    App::addHistory($moveLabel);

    $colorInt = $color === 'blanc' ? PieceSquadro::BLANC : PieceSquadro::NOIR;
    if ($action->remporteVictoire($colorInt)) {
        $_SESSION[App::SESSION_STATE] = 'Victoire';
        $_SESSION[App::SESSION_TURN] = $color;
        syncGameToDatabase($color, $moveLabel);
        return true;
    }

    $_SESSION[App::SESSION_TURN] = App::oppositeColor($color);
    $_SESSION[App::SESSION_STATE] = 'choixPiece';
    syncGameToDatabase(null, $moveLabel);
    return true;
}

function playOracleMove(string $source = 'oracle'): bool
{
    $plateau = App::ensureBoard();
    $color = App::activeTurnLabel();
    $move = SquadroAnalyzer::bestMove($plateau, $color);

    if ($move === null) {
        App::flash('warning', 'L’Oracle ne trouve aucun coup légal pour ' . $color . '.');
        return false;
    }

    [$x, $y] = $move['origin'];
    return playMove($color, (int) $x, (int) $y, $source);
}

function runBotIfNeeded(): void
{
    $settings = App::settings();
    $mode = (string) ($_SESSION[App::SESSION_MODE] ?? 'local');
    if ($mode !== 'bot' || empty($settings['bot'])) {
        return;
    }

    if ($_SESSION[App::SESSION_STATE] !== 'choixPiece') {
        return;
    }

    if (App::activeTurnLabel() === $settings['botColor']) {
        playOracleMove('bot');
    }
}

function selectPiece(string $color): void
{
    if (!ensureColorPlayable($color)) {
        $_SESSION[App::SESSION_STATE] = 'choixPiece';
        return;
    }

    $position = parsePositionFromRequest($color);
    if ($position === null) {
        $_SESSION[App::SESSION_STATE] = 'erreur';
        App::flash('danger', 'Position de pièce invalide.');
        return;
    }

    $settings = App::settings();
    if ($settings['moveFlow'] === 'instant') {
        playMove($color, (int) $position[0], (int) $position[1], 'manual');
        runBotIfNeeded();
        return;
    }

    $_SESSION['position'] = $position;
    $_SESSION[App::SESSION_TURN] = $color;
    $_SESSION[App::SESSION_STATE] = 'ConfirmationPiece';
}

function confirmMove(): void
{
    $position = $_SESSION['position'] ?? null;
    if (!is_array($position) || count($position) < 2) {
        $_SESSION[App::SESSION_STATE] = 'erreur';
        App::flash('danger', 'Aucune pièce n’était sélectionnée.');
        return;
    }

    playMove(App::activeTurnLabel(), (int) $position[0], (int) $position[1], 'manual');
    runBotIfNeeded();
}

function exportCurrentGame(): never
{
    $payload = [
        'exportedAt' => date(DATE_ATOM),
        'mode' => $_SESSION[App::SESSION_MODE] ?? 'local',
        'turn' => App::activeTurnLabel(),
        'state' => $_SESSION[App::SESSION_STATE] ?? 'choixPiece',
        'settings' => App::settings(),
        'stats' => App::stats(),
        'history' => App::history(),
        'board' => json_decode(App::ensureBoard()->toJson(), true, 512, JSON_THROW_ON_ERROR),
    ];

    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="squadro-export-' . date('Ymd-His') . '.json"');
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

if (isset($_POST['menu'])) {
    App::redirect('/Vue/choixAction.php');
}

if (isset($_POST['sync'])) {
    App::flash('success', 'Synchronisation effectuée.');
    App::redirect('/Controlleur/index_squadro.php');
}

if (isset($_POST['export'])) {
    exportCurrentGame();
}

if (($_POST['action'] ?? '') === 'settings') {
    App::updateSettings($_POST);
    $settings = App::settings();
    if (($_SESSION[App::SESSION_MODE] ?? 'local') !== 'online') {
        $_SESSION[App::SESSION_MODE] = !empty($settings['bot']) ? 'bot' : 'local';
    }
    runBotIfNeeded();
    App::flash('success', 'Paramètres de bataille mis à jour.');
    App::redirect('/Controlleur/index_squadro.php');
}

if (isset($_POST['oracle'])) {
    playOracleMove('oracle');
    runBotIfNeeded();
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
    $settings = App::settings();
    App::resetRuntimeGame(true);
    if ($player !== null) {
        $_SESSION[App::SESSION_PLAYER] = $player;
    }
    $_SESSION[App::SESSION_SETTINGS] = $settings;
    $_SESSION[App::SESSION_BOARD] = new PlateauSquadro();
    $_SESSION[App::SESSION_STATE] = 'choixPiece';
    $_SESSION[App::SESSION_TURN] = 'blanc';
    $_SESSION[App::SESSION_MODE] = !empty($settings['bot']) ? 'bot' : 'local';
    App::resetStats();
    App::flash('success', 'Nouvelle partie initialisée.');
    App::redirect('/Controlleur/index_squadro.php');
}

$moveToken = $_POST['move'] ?? $_GET['move'] ?? null;
$move = parseMoveToken(is_string($moveToken) ? $moveToken : null);
if ($move !== null) {
    [$color, $x, $y] = $move;
    playMove($color, $x, $y, 'manual');
    runBotIfNeeded();
    App::redirect('/Controlleur/index_squadro.php');
}

if (isset($_POST['blanc']) || isset($_GET['blanc'])) {
    selectPiece('blanc');
    App::redirect('/Controlleur/index_squadro.php');
}

if (isset($_POST['noir']) || isset($_GET['noir'])) {
    selectPiece('noir');
    App::redirect('/Controlleur/index_squadro.php');
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
