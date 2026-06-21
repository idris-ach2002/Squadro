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
            $_SESSION[App::SESSION_BOARD] = new PlateauSquadro();
            $_SESSION[App::SESSION_STATE] = 'choixPiece';
            $_SESSION[App::SESSION_TURN] = 'blanc';
            $_SESSION[App::SESSION_MODE] = 'local';
            unset($_SESSION[App::SESSION_GAME_ID], $_SESSION[App::SESSION_PLAYER_COLOR], $_SESSION[App::SESSION_HISTORY], $_SESSION[App::SESSION_UNDO], $_SESSION['position']);
            App::redirect('/Controlleur/index_squadro.php');

        case 'creer_online':
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
    <title>Squadro — Arène de Sparte</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/greek-theme.css">
    <script defer src="/assets/js/sparta-effects.js"></script>
</head>
<body class="app-bg greek-theme">
<main class="shell single">
    <section class="lobby-card">
        <div class="hero-card">
            <p class="eyebrow">Agora des guerriers</p>
            <h1>Salut <?= App::e($player?->getNomJoueur()); ?></h1>
            <p>Choisis ton entrée dans l’arène. Duel d’entraînement, arène ouverte ou campagne reprise : une seule règle compte, faire sortir quatre étendards avant l’adversaire.</p>
            <?php foreach ($flashes as $flash): ?>
                <div class="alert <?= App::e($flash['type']); ?>"><?= App::e($flash['message']); ?></div>
            <?php endforeach; ?>
            <div class="stat-grid">
                <div><strong><?= App::e($stats['waiting']); ?></strong><span>Arènes ouvertes</span></div>
                <div><strong><?= App::e($stats['active']); ?></strong><span>Duels en marche</span></div>
                <div><strong><?= App::e($stats['finished']); ?></strong><span>Victoires gravées</span></div>
                <div><strong>4</strong><span>Étendards à sortir</span></div>
            </div>
        </div>
        <form method="post" class="form-card">
            <p class="eyebrow">Ordres du stratège</p>
            <h2>Choisis ton combat</h2>
            <div class="menu-grid">
                <button class="menu-action" name="action" value="duel_local">
                    <strong>Duel d’entraînement</strong>
                    <span>Deux camps sur le même poste. Rapide, brutal, immédiat.</span>
                </button>
                <button class="menu-action" name="action" value="creer_online">
                    <strong>Ouvrir une arène</strong>
                    <span>Tu prends les blancs et tu attends un adversaire.</span>
                </button>
                <button class="menu-action" name="action" value="parties_attente">
                    <strong>Rejoindre un duel</strong>
                    <span>Entre dans une arène ouverte et prends les noirs.</span>
                </button>
                <button class="menu-action" name="action" value="parties_en_cours">
                    <strong>Reprendre une campagne</strong>
                    <span>Retourne sur les batailles déjà commencées.</span>
                </button>
                <button class="menu-action danger" name="action" value="quitter">
                    <strong>Quitter le camp</strong>
                    <span>Ferme ta session et retourne aux portes de l’arène.</span>
                </button>
            </div>
        </form>
    </section>
</main>
</body>
</html>
