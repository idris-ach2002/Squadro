<?php
    session_start();


    // require_once 'plateau_squadro.php';


    // function actionChoisirPiece () : void
    // {
    //     $_SESSION['action'] = 'confirmationPiece';

    //     foreach ($_POST as $key => $value) {
    //         if (strpos($key, 'white') === 0 || strpos($key, 'black') === 0) {
    //             list(, $x, $y) = explode('-', $key);
    //             $_SESSION['position'] = [$x, $y];
    //             break;
    //         }
    //     }

    //     print(PlateauSquadro::afficher_confirmation("traiteActionSquadro.php"));
    // }

    print("SESSION<br>\n\n");
    print_r($_SESSION);
    print("POST<br>\n\n");
    print_r($_POST);

    // if ($_SESSION['action'] == 'choixPiece')
    //     actionChoisirPiece();



    // if ($_SESSION['action'] == 'confirmationPiece')
    //     if (isset($_POST['bouton'])) {
    //         $boutonClique = $_POST['bouton'];
    //         echo "Le bouton cliqué est";
    //     }
?>