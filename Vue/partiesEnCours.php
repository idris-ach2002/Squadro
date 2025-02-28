<?php
session_start();

require_once '../Modele/plateau_squadro.php';
require_once '../skel/PDOSquadro.skel.php';
require_once '../env/db.php';
require_once '../Modele/partieSquadro.php';

PDOSquadro::initPDO(getenv('sgbd'), getenv('host'), getenv('database'), getenv('user'), getenv('password'));
$tab_parties = PDOSquadro::getAllPartieSquadroByPlayerNameNonTerminees($_SESSION['joueur']);


$_SESSION["etat"] = "waitingForPlayer";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rejoindre une partie</title>
</head>
<body>
    <h1>Rejoindre une partie</h1>
    <?php if (!empty($tab_parties)): ?>
        <form action="rejoindre_partie.php" method="post">
            <label for="partie">Sélectionnez une partie :</label>
            <select name="partie" id="partie">
                <?php foreach ($tab_parties as $partie): ?>
                    <?php $partie_conv = PartieSquadro::fromJson($partie) ?>
                    <option value="<?php echo htmlspecialchars($partie_conv->getPartieID()); ?>">
                        <?php echo htmlspecialchars($partie_conv->getPartieID() . ' - ' . $partie_conv->getGameStatus()); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Rejoindre</button>
        </form>
    <?php else: ?>
        <p>Aucune partie disponible pour le moment.</p>
    <?php endif; ?>
</body>
</html>
