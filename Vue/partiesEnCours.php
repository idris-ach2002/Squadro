<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/bootstrap.php';
require_once __DIR__ . '/../Modele/plateau_squadro.php';
require_once __DIR__ . '/../skel/PDOSquadro.skel.php';

App::requirePlayer();
App::initDatabase();

$player = App::currentPlayer();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['partie'])) {
    $partieId = (int) $_POST['partie'];
    $partie = PDOSquadro::getPartieSquadroById($partieId);
    if ($partie instanceof PartieSquadro) {
        $_SESSION[App::SESSION_BOARD] = $partie->getPlateau();
        $_SESSION[App::SESSION_STATE] = $partie->getGameStatus() === 'finished' ? 'Victoire' : 'choixPiece';
        $_SESSION[App::SESSION_TURN] = $partie->getWinner() ?? $partie->getCurrentTurn();
        $_SESSION[App::SESSION_MODE] = 'online';
        $_SESSION[App::SESSION_GAME_ID] = $partieId;
        $playerId = $player?->getId();
        $_SESSION[App::SESSION_PLAYER_COLOR] = $partie->getPlayerOne()->getId() === $playerId ? 'blanc' : 'noir';
        $_SESSION[App::SESSION_HISTORY] = [];
        $_SESSION[App::SESSION_UNDO] = [];
        App::redirect('/Controlleur/index_squadro.php');
    }

    App::flash('danger', 'Impossible de rouvrir cette arène.');
    App::redirect('/Vue/partiesEnCours.php');
}

$parties = PDOSquadro::getAllPartieSquadroByPlayerNameNonTerminees($_SESSION[App::SESSION_PLAYER]);
$flashes = App::consumeFlash();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Squadro — Campagnes</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/greek-theme.css">
    <script defer src="/assets/js/sparta-effects.js"></script>
</head>
<body class="app-bg greek-theme">
<main class="shell single">
    <section class="list-card">
        <p class="eyebrow">Campagnes gravées</p>
        <h1>Mes batailles</h1>
        <p>Rouvre une arène active ou une campagne en attente liée à ton nom.</p>
        <?php foreach ($flashes as $flash): ?>
            <div class="alert <?= App::e($flash['type']); ?>"><?= App::e($flash['message']); ?></div>
        <?php endforeach; ?>
        <div class="table-list">
            <?php if ($parties === []): ?>
                <div class="alert warning">Aucune bataille en cours pour ce guerrier.</div>
            <?php else: ?>
                <?php foreach ($parties as $partieJson): $partie = PartieSquadro::fromJson($partieJson); ?>
                    <form method="post" class="game-row">
                        <div>
                            <strong>Arène #<?= App::e($partie->getPartieID()); ?> · <?= App::e($partie->getGameStatus()); ?></strong>
                            <p>Blanc : <?= App::e($partie->getPlayerOne()->getNomJoueur()); ?> · Noir : <?= App::e($partie->getPlayerTwo()?->getNomJoueur() ?? 'en attente'); ?> · initiative <?= App::e($partie->getCurrentTurn()); ?></p>
                        </div>
                        <button class="btn primary" name="partie" value="<?= App::e($partie->getPartieID()); ?>">Reprendre</button>
                    </form>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <p style="margin-top:18px"><a class="btn ghost" href="/Vue/choixAction.php">Retour à l’agora</a></p>
    </section>
</main>
</body>
</html>
