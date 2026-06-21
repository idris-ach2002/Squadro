<?php
session_start();

if (!isset($_SESSION['joueur'])) {
    header('Location: login.php');
    header('HTTP/1.1 303 See Other');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'nouvelle_partie':
            header('Location: attente_joueur.php');
            header('HTTP/1.1 303 See Other');
            exit;
        case 'parties_en_cours':
            $_SESSION['etat'] = 'consultePartieEnCours';
            header('Location: partiesEnCours.php');
            header('HTTP/1.1 303 See Other');
            exit;
        case 'parties_attente':
            header('Location: partieAttente.php');
            header('HTTP/1.1 303 See Other');
            exit;
        case 'parties_terminees':
            $_SESSION['etat'] = 'consultePartieVictoire';
            header('Location: choixAction.php');
            header('HTTP/1.1 303 See Other');
            exit;
        case 'quitter':
            session_unset();
            session_destroy();
            header('Location: ../index.php');
            header('HTTP/1.1 303 See Other');
            exit;
    }
}

function getPage(): string
{
    return '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu du Jeu</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f4f4f4; margin: 0; }
        .menu { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,.1); text-align: center; }
        form { margin: 10px 0; }
        button { width: 100%; padding: 10px; margin: 5px 0; border: none; border-radius: 5px; background: blue; color: white; font-size: 16px; cursor: pointer; }
        button:hover { background: darkblue; }
    </style>
</head>
<body>
    <div class="menu">
        <h1>Menu du Jeu</h1>
        <form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
            <button type="submit" name="action" value="nouvelle_partie">Nouvelle Partie</button>
            <button type="submit" name="action" value="parties_en_cours">Parties en Cours</button>
            <button type="submit" name="action" value="parties_attente">Parties en Attente</button>
            <button type="submit" name="action" value="parties_terminees">Parties Terminées</button>
            <button type="submit" name="action" value="quitter">Quitter la Session</button>
        </form>
    </div>
</body>
</html>';
}

echo getPage();
