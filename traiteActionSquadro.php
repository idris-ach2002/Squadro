<?php
    session_start();


    require_once 'plateau_squadro.php';


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

    print("SESSION<br>\n\n");
    print_r($_SESSION);
    print("POST<br>\n\n");
    print_r($_POST);

    if ($_SESSION['action'] == 'choixPiece')
        actionChoisirPiece();



    if ($_SESSION['action'] == 'confirmationPiece')
    {
        if (isset($_POST['bouton']) && $_POST['bouton'] == 'Annuler') {
            $_SESSION['action'] = 'choixPiece';
            $_SESSION['position'] = [];

            print(PlateauSquadro::afficher_plateau("traiteActionSquadro.php"));

            print_r($_SESSION);
        }

        if (isset($_POST['bouton']) && $_POST['bouton'] == 'Confirmer') {
            // $_SESSION['action'] = 'choixPiece';
            // $_SESSION['position'] = [];
            // print(PlateauSquadro::afficher_choix_piece("traiteActionSquadro.php"));
            // print_r($_SESSION);

            print("Confirmer");
        }
    }
?>