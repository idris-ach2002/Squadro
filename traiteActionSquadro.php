<?php
    session_start();


    require_once 'plateau_squadro.php';


    print_r($_SESSION);

    if ($_SESSION['action'] == 'choixPiece')
        actionChoisirPiece();



    if ($_SESSION['action'] == 'confirmationPiece')
        if (isset($_POST['bouton'])) {
            $boutonClique = $_POST['bouton'];
            echo "Le bouton cliqué est";
        }


    function actionChoisirPiece () : void
    {
        $_SESSION['action'] = 'confirmationPiece';

        foreach ($_POST as $key => $value) {
            if (strpos($key, 'white') === 0 || strpos($key, 'black') === 0) {
                list(, $x, $y) = explode('-', $key);
                $_SESSION['position'] = [$x, $y];
                break;
            }
        }

        print(PlateauSquadro::afficher_confirmation("traiteActionSquadro.php"));
    }
    


    print_r($_SESSION);
?>