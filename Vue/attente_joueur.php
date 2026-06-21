<?php
session_start();

if (!isset($_SESSION['joueur'])) {
    header('Location: login.php');
    header('HTTP/1.1 303 See Other');
    exit;
}

require_once __DIR__ . '/../Modele/plateau_squadro.php';
require_once __DIR__ . '/../skel/PDOSquadro.skel.php';
require_once __DIR__ . '/../env/db.php';

$plateau = new PlateauSquadro();
$_SESSION['plateau'] = $plateau;
$_SESSION['etat'] = 'choixPiece';
$_SESSION['couleur'] = 'blanc';

PDOSquadro::initPDO(getenv('sgbd'), getenv('host'), getenv('database'), getenv('user'), getenv('password'));
PDOSquadro::creerPartieSquadro($_SESSION['joueur'], $plateau->toJson());

header('Location: ../Controlleur/index_squadro.php');
header('HTTP/1.1 303 See Other');
exit;
