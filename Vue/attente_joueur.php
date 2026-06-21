<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/bootstrap.php';
require_once __DIR__ . '/../Modele/plateau_squadro.php';
require_once __DIR__ . '/../skel/PDOSquadro.skel.php';

App::requirePlayer();
App::initDatabase();
App::disableBot();
App::resetStats();

$plateau = new PlateauSquadro();
$gameId = PDOSquadro::creerPartieSquadro($_SESSION[App::SESSION_PLAYER], $plateau->toJson(), 'waitingForPlayer');

$_SESSION[App::SESSION_BOARD] = $plateau;
$_SESSION[App::SESSION_STATE] = 'choixPiece';
$_SESSION[App::SESSION_TURN] = 'blanc';
$_SESSION[App::SESSION_MODE] = 'online';
$_SESSION[App::SESSION_PLAYER_COLOR] = 'blanc';
$_SESSION[App::SESSION_GAME_ID] = $gameId;
$_SESSION[App::SESSION_HISTORY] = [];
$_SESSION[App::SESSION_UNDO] = [];
unset($_SESSION['position']);

App::flash('success', 'Partie #' . $gameId . ' créée. Vous jouez les blancs.');
App::redirect('/Controlleur/index_squadro.php');
