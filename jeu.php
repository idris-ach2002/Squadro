<?php
require_once 'PieceSquadroUI.php'; // Inclure la classe PieceSquadroUI

// Définir la page d'action pour le formulaire
$fich = "jeu.php"; // Tu peux modifier l'action en fonction de ta logique

// Afficher l'en-tête avec le lien vers le CSS
echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Jeu Squadro</title>
    <!-- Lier le fichier CSS -->
    <link rel='stylesheet' href='squadro.css' type='text/css'>
</head>
<body>";

// Afficher l'interface du plateau
echo PieceSquadroUI::plateauUI($fich);

// Fermer les balises HTML
echo "</body>
</html>";
?>
