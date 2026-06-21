<?php
require_once __DIR__ . '/../Modele/action_squadro.php';

session_start();

function verifierConnexion(): void
{
    if (!isset($_SESSION['joueur'])) {
        header('Location: ../Vue/login.php');
        header('HTTP/1.1 303 See Other');
        exit;
    }
}

function traiterChoix(string $couleur): void
{
    $_SESSION['position'] = explode('-', explode('btn', $_REQUEST[$couleur])[1]);
    $_SESSION['couleur'] = $couleur;
    $_SESSION['etat'] = 'ConfirmationPiece';
}

function traiterAnnulation(): void
{
    unset($_SESSION['position']);
    $_SESSION['etat'] = 'choixPiece';
}

function traiterErreur(): void
{
    unset($_SESSION['position']);
    $_SESSION['etat'] = 'choixPiece';
}

function rejouer(): void
{
    $_SESSION = [];
}

function traiterConfirmation(string $couleur): void
{
    $couleurInt = $couleur === 'blanc' ? PieceSquadro::BLANC : PieceSquadro::NOIR;

    if (!isset($_SESSION['plateau'])) {
        $_SESSION['plateau'] = new PlateauSquadro();
    }

    if (!isset($_SESSION['position'])) {
        $_SESSION['etat'] = 'erreur';
        return;
    }

    $action = new ActionSquadro($_SESSION['plateau']);
    if (!$action->jouePiece((int) $_SESSION['position'][0], (int) $_SESSION['position'][1])) {
        $_SESSION['etat'] = 'erreur';
        return;
    }

    unset($_SESSION['position']);

    if ($action->remporteVictoire($couleurInt)) {
        $_SESSION['etat'] = 'Victoire';
        return;
    }

    $_SESSION['couleur'] = $couleur === 'blanc' ? 'noir' : 'blanc';
    $_SESSION['etat'] = 'choixPiece';
}

if (!isset($_SESSION['etat'])) {
    $_SESSION['etat'] = 'login';
}

if ($_SESSION['etat'] === 'login') {
    $_SESSION['etat'] = 'choixPiece';
    $_SESSION['couleur'] = 'blanc';
    verifierConnexion();
}

if (isset($_REQUEST['blanc'])) {
    traiterChoix('blanc');
    header('Location: index_squadro.php');
    header('HTTP/1.1 303 See Other');
    exit;
}

if (isset($_REQUEST['noir'])) {
    traiterChoix('noir');
    header('Location: index_squadro.php');
    header('HTTP/1.1 303 See Other');
    exit;
}

if (isset($_REQUEST['choix'])) {
    switch ($_REQUEST['choix']) {
        case 'PRESEED':
            traiterConfirmation($_SESSION['couleur'] ?? 'blanc');
            break;
        case 'ABORT':
            traiterAnnulation();
            break;
    }

    header('Location: index_squadro.php');
    header('HTTP/1.1 303 See Other');
    exit;
}

if (isset($_REQUEST['rejouer'])) {
    rejouer();
    header('Location: ../index.php');
    header('HTTP/1.1 303 See Other');
    exit;
}

if (isset($_REQUEST['erreur'])) {
    traiterErreur();
    header('Location: index_squadro.php');
    header('HTTP/1.1 303 See Other');
    exit;
}

header('Location: index_squadro.php');
header('HTTP/1.1 303 See Other');
exit;
