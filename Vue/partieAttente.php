<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/bootstrap.php';
require_once __DIR__ . '/../Modele/plateau_squadro.php';
require_once __DIR__ . '/../skel/PDOSquadro.skel.php';

App::requirePlayer();
App::initDatabase();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['partie'])) {
    $partieId = (int) $_POST['partie'];
    $partie = PDOSquadro::getPartieSquadroById($partieId);

    if ($partie instanceof PartieSquadro && $partie->getGameStatus() === 'waitingForPlayer') {
        PDOSquadro::addPlayerToPartieSquadro($_SESSION[App::SESSION_PLAYER], $partieId);
        $partie = PDOSquadro::getPartieSquadroById($partieId) ?? $partie;
        $_SESSION[App::SESSION_BOARD] = $partie->getPlateau();
        $_SESSION[App::SESSION_STATE] = 'choixPiece';
        $_SESSION[App::SESSION_TURN] = $partie->getCurrentTurn();
        $_SESSION[App::SESSION_MODE] = 'online';
        $_SESSION[App::SESSION_PLAYER_COLOR] = 'noir';
        $_SESSION[App::SESSION_GAME_ID] = $partieId;
        $_SESSION[App::SESSION_HISTORY] = [];
        $_SESSION[App::SESSION_UNDO] = [];
        App::flash('success', 'Arène #' . $partieId . ' rejointe. Tu prends les noirs.');
        App::redirect('/Controlleur/index_squadro.php');
    }

    App::flash('danger', 'Cette arène n’est plus disponible.');
    App::redirect('/Vue/partieAttente.php');
}

$parties = PDOSquadro::getAllPartieSquadroEnAttente($_SESSION[App::SESSION_PLAYER]);
$flashes = App::consumeFlash();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Squadro — Arènes ouvertes</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/greek-theme.css">
    <script defer src="/assets/js/sparta-effects.js"></script>
</head>
<body class="app-bg greek-theme">
<main class="shell single">
    <section class="list-card">
        <p class="eyebrow">Portes ouvertes</p>
        <h1>Rejoindre une arène</h1>
        <p>Ces duels attendent un second guerrier. En entrant, tu prends les noirs et la bataille commence.</p>
        <?php foreach ($flashes as $flash): ?>
            <div class="alert <?= App::e($flash['type']); ?>"><?= App::e($flash['message']); ?></div>
        <?php endforeach; ?>
        <div class="table-list">
            <?php if ($parties === []): ?>
                <div class="alert warning">Aucune arène disponible pour le moment.</div>
            <?php else: ?>
                <?php foreach ($parties as $partieJson): $partie = PartieSquadro::fromJson($partieJson); ?>
                    <form method="post" class="game-row">
                        <div>
                            <strong>Arène #<?= App::e($partie->getPartieID()); ?></strong>
                            <p>Ouverte par <?= App::e($partie->getPlayerOne()->getNomJoueur()); ?> · initiative <?= App::e($partie->getCurrentTurn()); ?></p>
                        </div>
                        <button class="btn primary" name="partie" value="<?= App::e($partie->getPartieID()); ?>">Entrer</button>
                    </form>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <p style="margin-top:18px"><a class="btn ghost" href="/Vue/choixAction.php">Retour à l’agora</a></p>
    </section>
</main>
</body>
</html>
