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
require_once __DIR__ . '/../Modele/partieSquadro.php';

PDOSquadro::initPDO(getenv('sgbd'), getenv('host'), getenv('database'), getenv('user'), getenv('password'));

if (isset($_POST['partie'])) {
    $partieId = (int) $_POST['partie'];
    $partie = PDOSquadro::getPartieSquadroById($partieId);

    if ($partie !== null) {
        PDOSquadro::addPlayerToPartieSquadro($_SESSION['joueur'], $partie->getPlateau()->toJson(), $partieId);
        $_SESSION['plateau'] = $partie->getPlateau();
        $_SESSION['etat'] = 'choixPiece';
        $_SESSION['couleur'] = 'noir';

        header('Location: ../Controlleur/index_squadro.php');
        header('HTTP/1.1 303 See Other');
        exit;
    }
}

$tab = PDOSquadro::getAllPartieSquadroEnAttente($_SESSION['joueur']);
$_SESSION['etat'] = 'waitingForPlayer';

function getPage(array $tab_parties): string
{
    $res = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rejoindre une partie en attente</title>
</head>
<body>
    <h1>Rejoindre une partie en attente</h1>';

    if (!empty($tab_parties)) {
        $res .= '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
            <label for="partie">Sélectionnez une partie :</label>
            <select name="partie" id="partie">';

        foreach ($tab_parties as $partieJson) {
            $partie = PartieSquadro::fromJson($partieJson);
            $id = $partie->getPartieID();
            $res .= '<option value="' . $id . '">Jeu ' . $id . '</option>';
        }

        $res .= '</select>
            <button type="submit">Rejoindre</button>
        </form>';
    } else {
        $res .= '<p>Aucune partie disponible pour le moment.</p>';
    }

    return $res . '<p><a href="choixAction.php">Retour au menu</a></p></body></html>';
}

echo getPage($tab ?? []);
