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
        if (isset($_POST['bouton']) && $_POST['bouton'] == 'Annuler')
        {
            $_SESSION['action'] = 'choixPiece';
            $_SESSION['position'] = [];

            header('Location: jeu.php');
            header('HTTP/1.1 303 See Other');
            exit();

            print_r($_SESSION);
        }

        if (isset($_POST['bouton']) && $_POST['bouton'] == 'Confirmer')
        {
            // Déplace la piece
            getPiece($_SESSION['position'][0], $_SESSION['position'][1])->setPiece($_SESSION['position'][0], $_SESSION['position'][1], $_POST['x'], $_POST['y']);
            
            // On oublie la position de l'ancienne piece
            $_SESSION['position'] = []; 

            // On teste si la partie est terminée
            // ??

            if (true) {
                $_SESSION['action'] = 'victoire';

                // On affiche le message de victoire
                print("??");
            } else {
                $_SESSION['action'] = 'erreur';

                // A faire avec le mvc
            }

            print("Confirmer");
        }
    }



    if ($_SESSION['action'] == 'victoire' || $_SESSION['action'] == 'erreur')
    {
        session_destroy();
        header('Location: jeu.php');
        header('HTTP/1.1 303 See Other');
    }
?>