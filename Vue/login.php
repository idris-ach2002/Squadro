<?php
require_once __DIR__ . '/../skel/PDOSquadro.skel.php';

session_start();

function getPageLogin(): string
{
    $error = $_SESSION['login_error'] ?? '';
    unset($_SESSION['login_error']);

    return '<!DOCTYPE html>
<html class="no-js" lang="fr" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Accès à la salle de jeux</title>
    <style>
        body { font-family: Arial, sans-serif; min-height: 100vh; display: grid; place-items: center; margin: 0; background: #f5f5f5; }
        .card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,.12); min-width: 320px; }
        input[type=text] { width: 100%; box-sizing: border-box; padding: .75rem; margin: .5rem 0 1rem; }
        input[type=submit] { width: 100%; padding: .75rem; cursor: pointer; }
        .error { color: #a40000; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <main class="card">
        <h1>Salon Squadro</h1>
        <h2>Identification du joueur</h2>' .
        ($error !== '' ? '<p class="error">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</p>' : '') .
        '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
            <label for="playerName">Nom</label>
            <input id="playerName" type="text" name="playerName" required maxlength="255" />
            <input type="submit" name="action" value="connecter" />
        </form>
    </main>
</body>
</html>';
}

if (isset($_POST['playerName'])) {
    $playerName = trim($_POST['playerName']);

    if ($playerName === '') {
        $_SESSION['login_error'] = 'Le nom du joueur est obligatoire.';
        header('Location: login.php');
        header('HTTP/1.1 303 See Other');
        exit;
    }

    require_once __DIR__ . '/../env/db.php';
    PDOSquadro::initPDO(getenv('sgbd'), getenv('host'), getenv('database'), getenv('user'), getenv('password'));

    $player = PDOSquadro::getPlayerByName($playerName);
    if ($player === null) {
        $player = PDOSquadro::createPlayer($playerName);
    }

    $_SESSION['joueur'] = $player->toJson();
    $_SESSION['etat'] = 'choixPiece';
    $_SESSION['couleur'] = 'blanc';

    header('Location: choixAction.php');
    header('HTTP/1.1 303 See Other');
    exit;
}

echo getPageLogin();
