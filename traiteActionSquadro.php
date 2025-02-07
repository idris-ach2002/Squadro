<?php
    require_once 'plateau_squadro.php';


    // function actionChoisirPiece () : void
    // {
    //     $_SESSION['action'] = 'confirmationPiece';

    //     foreach ($_POST as $key => $value)
    //         if (strpos($key, 'btn') === 0 && $value == 'clicked')
    //             echo "Button $key cliqué.";
    // } 


    print_r($_POST);


    // // foreach ($_POST as $key => $value)
    print(PlateauSquadro::afficher_confirmation("traiteActionSquadro.php"));

?>