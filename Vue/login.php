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
    <title>Squadro — Spartan Arena</title>

    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/sparta-home.css">
    <script defer src="/assets/js/sparta-effects.js"></script>
</head>

<body class="sparta-home">
<main class="sparta-shell">
    <section class="sparta-card">
        <div class="sparta-hero">
            <div class="sparta-mark" aria-hidden="true">
                <span>Λ</span>
            </div>

            <p class="sparta-eyebrow">Squadro Arena</p>

            <h1 class="sparta-title">
                Entre dans
                <strong>l’arène</strong>
            </h1>

            <p class="sparta-lead">
                Un plateau. Deux camps. Aucun recul inutile.
                Chaque avancée engage ton honneur, chaque retour peut renverser la bataille.
            </p>

            <div class="sparta-line" aria-hidden="true"></div>

            <div class="sparta-oath">
                <span><i></i>Avance avec discipline.</span>
                <span><i></i>Frappe au bon moment.</span>
                <span><i></i>Reviens victorieux.</span>
            </div>
        </div>

        <div class="sparta-form-panel">
            <div class="sparta-form-inner">
                <p class="sparta-eyebrow">Porte des guerriers</p>

                <h2>Prends ton nom.</h2>

                <p>
                    Inscris ton nom, choisis ton camp, puis entre dans la bataille.
                    Le reste se jouera sur le plateau.
                </p>

                <?php foreach ($flashes as $flash): ?>
                    <div class="sparta-alert <?= App::e($flash['type']); ?>">
                        <?= App::e($flash['message']); ?>
                    </div>
                <?php endforeach; ?>

                <form method="post" class="sparta-form">
                    <div class="sparta-field">
                        <label for="playerName">Nom du guerrier</label>

                        <input
                            class="sparta-input"
                            id="playerName"
                            name="playerName"
                            maxlength="40"
                            required
                            autocomplete="nickname"
                            placeholder="Ex. Léonidas"
                        >
                    </div>

                    <button class="sparta-button" type="submit">
                        Entrer dans l’arène
                    </button>
                </form>

                <div class="sparta-footer-note">
                    La stratégie commence avant le premier mouvement.
                </div>
            </div>
        </div>
    </section>
</main>
</body>
</html>
