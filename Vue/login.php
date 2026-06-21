<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/bootstrap.php';
require_once __DIR__ . '/../skel/PDOSquadro.skel.php';

App::initDatabase();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $playerName = trim((string) ($_POST['playerName'] ?? ''));
    if ($playerName === '') {
        App::flash('danger', 'Le nom du joueur est obligatoire.');
        App::redirect('/Vue/login.php');
    }

    if (strlen($playerName) > 40) {
        App::flash('danger', 'Le nom du joueur doit rester inférieur à 40 caractères.');
        App::redirect('/Vue/login.php');
    }

    $player = PDOSquadro::createPlayer($playerName);
    session_regenerate_id(true);
    $_SESSION[App::SESSION_PLAYER] = $player->toJson();
    $_SESSION[App::SESSION_STATE] = 'choixPiece';
    $_SESSION[App::SESSION_TURN] = 'blanc';
    $_SESSION[App::SESSION_MODE] = 'local';
    App::flash('success', 'Bienvenue ' . $player->getNomJoueur() . '.');
    App::redirect('/Vue/choixAction.php');
}

$flashes = App::consumeFlash();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Connexion Squadro</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app-bg">
<main class="shell single">
    <section class="lobby-card">
        <div class="hero-card">
            <p class="eyebrow">Squadro Arena</p>
            <h1>Jeu tactique, duel rapide, interface moderne.</h1>
            <p>Connecte un profil joueur pour créer une partie locale, ouvrir une partie sauvegardée ou rejoindre une table en attente dans PostgreSQL.</p>
            <div class="stat-grid">
                <div><strong>Docker</strong><span>PHP + PostgreSQL</span></div>
                <div><strong>Session</strong><span>Jeu instantané</span></div>
                <div><strong>DB</strong><span>Parties persistées</span></div>
                <div><strong>UI</strong><span>Responsive</span></div>
            </div>
        </div>
        <div class="form-card">
            <p class="eyebrow">Entrée joueur</p>
            <h2>Identification</h2>
            <?php foreach ($flashes as $flash): ?>
                <div class="alert <?= App::e($flash['type']); ?>"><?= App::e($flash['message']); ?></div>
            <?php endforeach; ?>
            <form method="post" class="form-grid">
                <div class="field">
                    <label for="playerName">Nom du joueur</label>
                    <input id="playerName" name="playerName" maxlength="40" required autocomplete="nickname" placeholder="Ex. Idris">
                </div>
                <button class="btn primary" type="submit">Entrer dans le lobby</button>
            </form>
        </div>
    </section>
</main>
</body>
</html>
