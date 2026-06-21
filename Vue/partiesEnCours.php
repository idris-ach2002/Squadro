<?php
session_start();

if (!isset($_SESSION['joueur'])) {
    header('Location: login.php');
    header('HTTP/1.1 303 See Other');
    exit;
}

require_once __DIR__ . '/../Modele/plateau_squadro.php';
require_once __DIR__ . '/../skel/PDOSquadro.skel.php';
require_once __DIR__ . '/../env/db.php';
require_once __DIR__ . '/../Modele/partieSquadro.php';

PDOSquadro::initPDO(getenv('sgbd'), getenv('host'), getenv('database'), getenv('user'), getenv('password'));

if (isset($_POST['partie'])) {
    $partie = PDOSquadro::getPartieSquadroById((int) $_POST['partie']);
    if ($partie !== null) {
        $_SESSION['plateau'] = $partie->getPlateau();
        $_SESSION['etat'] = 'choixPiece';
        $_SESSION['couleur'] = 'blanc';

        header('Location: ../Controlleur/index_squadro.php');
        header('HTTP/1.1 303 See Other');
        exit;
    }
}

$tab_parties = PDOSquadro::getAllPartieSquadroByPlayerNameNonTerminees($_SESSION['joueur']);
$_SESSION['etat'] = 'waitingForPlayer';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Parties en cours</title>
</head>
<body>
    <h1>Parties en cours</h1>
    <?php if (!empty($tab_parties)): ?>
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="post">
            <label for="partie">Sélectionnez une partie :</label>
            <select name="partie" id="partie">
                <?php foreach ($tab_parties as $partieJson): ?>
                    <?php $partie = PartieSquadro::fromJson($partieJson); ?>
                    <option value="<?= htmlspecialchars((string) $partie->getPartieID(), ENT_QUOTES, 'UTF-8'); ?>">
                        <?= htmlspecialchars($partie->getPartieID() . ' - ' . $partie->getGameStatus(), ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Ouvrir</button>
        </form>
    <?php else: ?>
        <p>Aucune partie disponible pour le moment.</p>
    <?php endif; ?>
    <p><a href="choixAction.php">Retour au menu</a></p>
</body>
</html>
