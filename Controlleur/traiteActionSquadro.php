<?php
require_once '../Modele/action_squadro.php';


session_start();


function login() {
    if (!isset($_SESSION["login"]))
    {
        header('Location: ../Vue/login.php');
        header('HTTP/1.1 303 See Other');
    }
    else
    {
        print("Données");
    }
}


function traiterChoix(string $couleur)
{
    //stocker la position de la pièce dans un tableau [abcisse, ordonnée] (btn$x-$y) -> ["", "$x-$y"] -> [$x,$y]
    $_SESSION["position"] = explode("-", explode("btn", $_REQUEST[$couleur])[1]);
    //stocker le joueur courant
    $_SESSION["joueur"] = $couleur;
    $_SESSION["etat"] = "ConfirmationPiece";
}

function traiterAnnulation()
{
    $_SESSION["position"] = "";
    $_SESSION["etat"] = "choixPiece";
}

function traiterErreur()
{
    session_unset();
    $_SESSION["etat"] = "choixPiece";
}

function rejouer() : void {
    $_SESSION = []; //il suffit juste d'oublier la session tout est fais dans index
}


function traiterConfiramtion(string $couleur)
{
    echo "<h1>X" . $_SESSION["position"][0] . ", Y" . $_SESSION["position"][1] . "</h1>";
    echo $_SESSION["plateau"]->__toString();
    $couleurInt = $couleur === 'blanc' ? PieceSquadro::BLANC : PieceSquadro::NOIR;

    if (isset($_SESSION["plateau"])) {
        if (isset($_SESSION["position"])) {
            $bouger = new ActionSquadro($_SESSION["plateau"]);
            $bouger->jouePiece($_SESSION["position"][0], $_SESSION["position"][1]);
            unset($_SESSION["position"]);
            if ($bouger->remporteVictoire($couleurInt))
                $_SESSION["etat"] = "Victoire";
            else {
                $_SESSION["joueur"] = $couleur === 'blanc' ? 'noir' : 'blanc';
                $_SESSION["etat"] = "choixPiece";
            }
        } else {
            echo "position de la pièce à faire bouger non sauvgardé impossible de faire les déplacements<br/>";
            exit(1);
        }
    } else {
        echo "plateau non sauvgardé impossible de faire les déplacements<br/>";
        exit(1);
    }
}




login();


if (isset($_REQUEST["blanc"])) {
    traiterChoix("blanc");
    header('Location: index_squadro.php');
} else if (isset($_REQUEST["noir"])) {
    traiterChoix("noir");
    header('Location: index_squadro.php');
}


if(isset($_REQUEST["choix"])) {
    switch ($_REQUEST["choix"]) {
        case "PRESEED": traiterConfiramtion($_SESSION["joueur"]); break;
        case "ABORT": traiterAnnulation(); break;
    }
    header('Location: index_squadro.php');
}



if(isset($_REQUEST["rejouer"])) {
    rejouer();
    header('Location: index_squadro.php');
}

