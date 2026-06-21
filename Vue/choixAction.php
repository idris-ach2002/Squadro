<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/bootstrap.php';
require_once __DIR__ . '/../Modele/plateau_squadro.php';
require_once __DIR__ . '/../skel/PDOSquadro.skel.php';

App::requirePlayer();
App::initDatabase();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    switch ($action) {
        case 'duel_local':
            App::disableBot();
            $_SESSION[App::SESSION_BOARD] = new PlateauSquadro();
            $_SESSION[App::SESSION_STATE] = 'choixPiece';
            $_SESSION[App::SESSION_TURN] = 'blanc';
            $_SESSION[App::SESSION_MODE] = 'local';
            unset($_SESSION[App::SESSION_GAME_ID], $_SESSION[App::SESSION_PLAYER_COLOR], $_SESSION[App::SESSION_HISTORY], $_SESSION[App::SESSION_UNDO], $_SESSION['position']);
            App::resetStats();
            App::redirect('/Controlleur/index_squadro.php');

        case 'duel_bot':
            App::enableBot('noir');
            $_SESSION[App::SESSION_BOARD] = new PlateauSquadro();
            $_SESSION[App::SESSION_STATE] = 'choixPiece';
            $_SESSION[App::SESSION_TURN] = 'blanc';
            $_SESSION[App::SESSION_MODE] = 'bot';
            unset($_SESSION[App::SESSION_GAME_ID], $_SESSION[App::SESSION_PLAYER_COLOR], $_SESSION[App::SESSION_HISTORY], $_SESSION[App::SESSION_UNDO], $_SESSION['position']);
            App::resetStats();
            App::redirect('/Controlleur/index_squadro.php');

        case 'creer_online':
            App::disableBot();
            App::redirect('/Vue/attente_joueur.php');

        case 'parties_en_cours':
            App::redirect('/Vue/partiesEnCours.php');

        case 'parties_attente':
            App::redirect('/Vue/partieAttente.php');

        case 'quitter':
            session_unset();
            session_destroy();
            App::redirect('/Vue/login.php');
    }
}

$player = App::currentPlayer();
$stats = PDOSquadro::getDashboardStats();
$flashes = App::consumeFlash();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Agora Squadro</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/greek-game.css">
</head>
<body class="app-bg greek-game">
<main class="shell single">
    <section class="lobby-card">
        <div class="hero-card">
            <p class="eyebrow">Agora des stratèges</p>
            <h1>Salut <?= App::e($player?->getNomJoueur()); ?></h1>
            <p>Choisis ton arène. Tu peux jouer en duel local, affronter l’Oracle, rejoindre une table ou reprendre une campagne en cours.</p>
            <?php foreach ($flashes as $flash): ?>
                <div class="alert <?= App::e($flash['type']); ?>"><?= App::e($flash['message']); ?></div>
            <?php endforeach; ?>
            <div class="stat-grid">
                <div><strong><?= App::e($stats['waiting']); ?></strong><span>Tables en attente</span></div>
                <div><strong><?= App::e($stats['active']); ?></strong><span>Parties actives</span></div>
                <div><strong><?= App::e($stats['finished']); ?></strong><span>Parties terminées</span></div>
                <div><strong>4</strong><span>Pièces à sortir</span></div>
            </div>
        </div>
        <form method="post" class="form-card">
            <p class="eyebrow">Actions</p>
            <h2>Menu du jeu</h2>
            <div class="menu-grid">
                <button class="menu-action" name="action" value="duel_local">
                    <strong>Duel local instantané</strong>
                    <span>Deux camps sur le même écran, déplacement direct, aucune confirmation cachée en bas de page.</span>
                </button>
                <button class="menu-action" name="action" value="duel_bot">
                    <strong>Affronter l’Oracle</strong>
                    <span>Tu joues les blancs, l’Oracle répond automatiquement avec une heuristique tactique.</span>
                </button>
                <button class="menu-action" name="action" value="creer_online">
                    <strong>Créer une table persistée</strong>
                    <span>Ouvre une partie sauvegardée et prends les blancs.</span>
                </button>
                <button class="menu-action" name="action" value="parties_attente">
                    <strong>Rejoindre une table</strong>
                    <span>Entre dans une arène en attente d’un second joueur.</span>
                </button>
                <button class="menu-action" name="action" value="parties_en_cours">
                    <strong>Mes parties en cours</strong>
                    <span>Rouvre les parties associées à ton profil.</span>
                </button>
                <button class="menu-action danger" name="action" value="quitter">
                    <strong>Déconnexion</strong>
                    <span>Ferme la session PHP courante.</span>
                </button>
            </div>
        </form>
    </section>
</main>
</body>
</html>
