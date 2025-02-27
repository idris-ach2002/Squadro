<?php
require_once '../Modele/PieceSquadroUI.php'; 
require_once '../Modele/plateau_squadro.php'; 

session_start();

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Jeu Squadro</title>
    <!-- Lier le fichier CSS -->
    <style>" .
    PieceSquadroUI::createStyle()
     .
    "</style>
</head>
<body>";


//génrération d'un plateau;
$plateau = new PlateauSquadro();


//stocker le plateau dans une variable de session
if(!isset($_SESSION["plateau"]))
    $_SESSION["plateau"] = $plateau;


if(!isset($_SESSION["etat"])) {
    //stocker la toute première action 
    $_SESSION["etat"] = "choixPiece";
    // Afficher l'interface du plateau
   echo PieceSquadroUI::debForm("traiteActionSquadro.php") . 
        PieceSquadroUI::plateauUI($_SESSION["plateau"], "enabled", "enabled").
        PieceSquadroUI::finForm();
}else {
    switch($_SESSION["etat"]) {
        case "ConfirmationPiece": {
            $id = ' ['. $_SESSION["position"][0] . ' , '. $_SESSION["position"][1] . ']';
            echo PieceSquadroUI::confirmationDeplacement("traiteActionSquadro.php", $id, $_SESSION["plateau"]); 
            break;
        }
        case "choixPiece" : {
            $blanc = $_SESSION["joueur"] == "blanc" ? "enabled" : "disabled";
            $noir = $_SESSION["joueur"] == "noir" ? "enabled" : "disabled";
            echo PieceSquadroUI::debForm("traiteActionSquadro.php") . 
            PieceSquadroUI::plateauUI($_SESSION["plateau"], $noir, $blanc).
            PieceSquadroUI::finForm();
            break;
        }

        case "Victoire": echo PieceSquadroUI::afficherVictoire($_SESSION["joueur"], "traiteActionSquadro.php"); break;
    }
}



// Fermer les balises HTML
echo "</body>
</html>";
?>

