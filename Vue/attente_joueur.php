<?php
session_start();

require_once '../Modele/plateau_squadro.php';
require_once '../skel/PDOSquadro.skel.php';
require_once '../env/db.php';

//génrération d'un plateau;
$plateau = new PlateauSquadro();


//stocker le plateau dans une variable de session
if(!isset($_SESSION["plateau"]))
    $_SESSION["plateau"] = $plateau;


print_r($_SESSION['joueur']);

PDOSquadro::initPDO(getenv('sgbd'),getenv('host'),getenv('database'),getenv('user'),getenv('password'));
PDOSquadro::creerPartieSquadro($_SESSION['joueur'], $plateau->toJson());



header("Location: ../Controlleur/index_squadro.php"); // Redirige vers l'accueil ou la page de connexion
header('HTTP/1.1 303 See Other');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Attente d'un Nouveau Joueur</title>
</head>
<body>
    <h1>En attente d'un second joueur ...</h1>
</body>
</html>
