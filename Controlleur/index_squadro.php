<?php
require_once __DIR__ . '/../Modele/PieceSquadroUI.php';
require_once __DIR__ . '/../Modele/plateau_squadro.php';

session_start();

if (!isset($_SESSION['joueur'])) {
    header('Location: ../Vue/login.php');
    header('HTTP/1.1 303 See Other');
    exit;
}

if (!isset($_SESSION['plateau']) || !($_SESSION['plateau'] instanceof PlateauSquadro)) {
    $_SESSION['plateau'] = new PlateauSquadro();
}

$_SESSION['etat'] = $_SESSION['etat'] ?? 'choixPiece';
$_SESSION['couleur'] = $_SESSION['couleur'] ?? 'blanc';

$plateau = $_SESSION['plateau'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeu Squadro</title>
    <style><?= PieceSquadroUI::createStyle(); ?></style>
</head>
<body>
<?php
switch ($_SESSION['etat']) {
    case 'ConfirmationPiece':
        $id = '[' . $_SESSION['position'][0] . ' , ' . $_SESSION['position'][1] . ']';
        echo PieceSquadroUI::confirmationDeplacement('traiteActionSquadro.php', $id, $plateau);
        break;

    case 'erreur':
        $id = isset($_SESSION['position']) ? '[' . $_SESSION['position'][0] . ' , ' . $_SESSION['position'][1] . ']' : '';
        echo PieceSquadroUI::afficher_erreur('traiteActionSquadro.php', $id, $plateau);
        break;

    case 'Victoire':
        echo PieceSquadroUI::afficherVictoire($_SESSION['couleur'], 'traiteActionSquadro.php');
        break;

    case 'choixPiece':
    default:
        $blanc = $_SESSION['couleur'] === 'blanc' ? 'enabled' : 'disabled';
        $noir = $_SESSION['couleur'] === 'noir' ? 'enabled' : 'disabled';
        echo PieceSquadroUI::debForm('traiteActionSquadro.php')
            . PieceSquadroUI::plateauUI($plateau, $noir, $blanc)
            . PieceSquadroUI::finForm();
        break;
}
?>
</body>
</html>
