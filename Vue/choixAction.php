<?php

session_start();


function getPage () : string
{
$form = '
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Menu du Jeu</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                background-color: #f4f4f4;
                margin: 0;
            }
            .menu {
                background: white;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                text-align: center;
            }
            h1 {
                margin-bottom: 20px;
            }
            form {
                margin: 10px 0;
            }
            button {
                width: 100%;
                padding: 10px;
                margin: 5px 0;
                border: none;
                border-radius: 5px;
                background: blue;
                color: white;
                font-size: 16px;
                cursor: pointer;
            }
            button:hover {
                background: darkblue;
            }
        </style>
    </head>
    <body>
        <div class="menu">
            <h1>Menu du Jeu</h1>
            <form action="'.$_SERVER['PHP_SELF'].'" method="post">
                <button type="submit" name="action" value="nouvelle_partie">Nouvelle Partie</button>
                <button type="submit" name="action" value="parties_en_cours">Parties en Cours</button>
                <button type="submit" name="action" value="parties_attente">Parties en Attente</button>
                <button type="submit" name="action" value="parties_terminees">Parties Terminées</button>
                <button type="submit" name="action" value="quitter">Quitter la Session</button>
            </form>
        </div>
    </body>
    </html>
    ';

return $form;
}


echo getPage();


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    switch ($_POST["action"]) {
        case "nouvelle_partie":
            header("Location: ../Controlleur/index_squadro.php");
            header('HTTP/1.1 303 See Other');
            exit();
        case "parties_en_cours":
            $_SESSION["etat"] = "consultePartieEnCours";
            //header("Location: parties_en_cours.php");
            //header('HTTP/1.1 303 See Other');
            exit();
        case "parties_attente":
            header("Location: parties_attente.php");
            header('HTTP/1.1 303 See Other');
            exit();
        case "parties_terminees":
            $_SESSION["etat"] = "consultePartieVictoire";
            // header("Location: parties_terminees.php");
            // header('HTTP/1.1 303 See Other');
            exit();
        case "quitter":
            session_unset(); // Vide les variables de session
            session_destroy(); // Détruit la session
            header("Location: ../index.php"); // Redirige vers l'accueil ou la page de connexion
            header('HTTP/1.1 303 See Other');
            exit();
        default:
            echo "Action non reconnue.";
            exit();
    }
}

?>