<?php
session_start();

require_once '../Modele/plateau_squadro.php';
require_once '../skel/PDOSquadro.skel.php';
require_once '../env/db.php';
require_once '../Modele/partieSquadro.php';

PDOSquadro::initPDO(getenv('sgbd'), getenv('host'), getenv('database'), getenv('user'), getenv('password'));
$tab = PDOSquadro::getAllPartieSquadroEnAttente($_SESSION['joueur']);


$_SESSION["etat"] = "waitingForPlayer";



function getPage($tab_parties) : string
{

$res = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rejoindre une partie en attente d\'un joueur...</title>
</head>
<body>
    <h1>Rejoindre une partie en attente d\'un joueur...</h1>';

if (!empty($tab_parties)) {
    $res .= '<form action="'.$_SERVER['PHP_SELF'].'" method="post">
                <label for="partie">Sélectionnez une partie :</label>
                <select name="partie" id="partie">';

    foreach ($tab_parties as $partie) {
        $partie_conv = PartieSquadro::fromJson($partie);

        $res .= '<option value="' . $partie_conv->getPartieID() . '">';
        $res .= 'Jeu ' . $partie_conv->getPartieID();
        $res .= '</option>';
    }

    $res .= '        </select>
                <button type="submit">Rejoindre</button>
            </form>';
}

else {
    $res .= '<p>Aucune partie disponible pour le moment.</p>';
}

$res .= '</body>
</html>';


return $res;
}


echo getPage($tab);


if (isset($_REQUEST['partie'])) {
    PDOSquadro::addPlayerToPartieSquadro($_SESSION['joueur'], (PDOSquadro::getPartieSquadroById($_REQUEST['partie']))->getPlateau()->toJson(), $_REQUEST['partie']);

    $_SESSION["etat"] = "choixPiece";

    header("Location: ../Controlleur/index_squadro.php"); // Redirige vers l'accueil ou la page de connexion
    header('HTTP/1.1 303 See Other');
}


?>
