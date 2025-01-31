<?php
require_once 'plateau_squadro.php';

/**
 * Classe représentant l'interface utilisateur pour le jeu Squadro.
 * Cette classe gère l'affichage et les interactions avec le plateau et les pièces.
 */
class PieceSquadroUI {

    /**
     * @var PlateauSquadro Plateau de jeu utilisé pour gérer les états des cases et des pièces.
     */
    private PlateauSquadro $plate;

    /**
     * @var bool Indique si le joueur noir a pris des cases.
     */
    private bool $joueurNoirPris;

    /**
     * @var bool Indique si le joueur blanc a pris des cases.
     */
    private bool $joueurBlancPris;

    /**
     * Constructeur privé pour initialiser l'état par défaut de la classe.
     */

     private function __construct() {
        $this->plate = new PlateauSquadro();
        $this->joueurNoirPris = false; // Initialisation
        $this->joueurBlancPris = false; // Initialisation
    }

    /**
     * Met à jour l'état d'une case en fonction de la couleur du joueur.
     *
     * @param int $x Coordonnée x de la case.
     * @param int $y Coordonnée y de la case.
     * @param int $couleur Couleur du joueur (1 pour noir, 0 pour blanc).
     * @return void
     */
    public function updateEtatCase(int $x, int $y, int $couleur): void {
        if ($couleur == 1) {
            $this->joueurNoirPris = true;  // Le joueur noir a pris une case
        } elseif ($couleur == 0) {
            $this->joueurBlancPris = true; // Le joueur blanc a pris une case
        }
    }

    /**
     * Désactive les cases en fonction de l'état des joueurs.
     *
     * @return string Retourne "disabled" si les cases doivent être désactivées, sinon une chaîne vide.
     */
    private function disableCases(): string {
        $disable = '';
        
        if ($this->joueurNoirPris) {
            // Désactive les cases blanches
            $disable = "disabled";
        } elseif ($this->joueurBlancPris) {
            // Désactive les cases noires
            $disable = "disabled";
        }
        
        return $disable;
    }
    /**
     * Génère un bouton représentant une case d'avancement.
     *
     * @param int $avancer Valeur d'avancement affichée.
     * @param string $id ID HTML de l'élément.
     * @param string $disable Statut de désactivation.
     * @return string Code HTML du bouton.
     */
    private static function genererCaseAvancement(int $avancer, string $id, string $disable): string {
        return "<input type='button' class='red' value='$avancer' id='$id' $disable/>";
    }
    /**
     * Génère une case rouge.
     *
     * @param string $id ID HTML de l'élément.
     * @param string $disable Statut de désactivation.
     * @return string Code HTML de la case rouge.
     */
    private static function genererCaseRouge(string $id, string $disable): string {
        return "<input type='button' class='red' id='$id' $disable/>";
    }

    /**
     * Génère une case blanche.
     *
     * @param int $x Coordonnée x de la case.
     * @param int $y Coordonnée y de la case.
     * @param int $direction Direction de la pièce (1 pour BE, 0 pour BW).
     * @param string $id ID HTML de l'élément.
     * @param string $disable Statut de désactivation.
     * @return string Code HTML de la case blanche.
     */
    private static function genererCaseBlanche(int $x, int $y, int $direction, string $id, string $disable): string {
        $position = $direction == 1 ? "BE" : "BW";
        return "<input type='submit' class='white' name='white-$x-$y' value='$position' id='$id' $disable/>";
    }

    /**
     * Génère une case noire.
     *
     * @param int $x Coordonnée x de la case.
     * @param int $y Coordonnée y de la case.
     * @param int $direction Direction de la pièce (1 pour NS, 0 pour NN).
     * @param string $id ID HTML de l'élément.
     * @param string $disable Statut de désactivation.
     * @return string Code HTML de la case noire.
     */

     private static function genererCaseNoir(int $x, int $y, int $direction, string $id, string $disable): string {
        $position = $direction == 0 ? "NN" : "NS";
        return "<input type='submit' class='black' name='black-$x-$y' value='$position' id='$id' $disable/>";
    }
    /**
     * Génère une case vide.
     *
     * @param string $id ID HTML de l'élément.
     * @param string $disable Statut de désactivation.
     * @return string Code HTML de la case vide.
     */
    private static function genererCaseVide(string $id, string $disable): string {
        return "<input type='button' class='vide' disabled id='$id' $disable/>";
    }


    /**
     * Génère une case neutre.
     *
     * @param string $id ID HTML de l'élément.
     * @param string $disable Statut de désactivation.
     * @return string Code HTML de la case neutre.
     */
    private static function genererCaseNeutre(string $id, string $disable): string {
        return "<input type='button' class='neutre' disabled id='$id' $disable/>";
    }


    /**
     * Démarre un formulaire HTML.
     *
     * @param string $fich Action du formulaire.
     * @return string Code HTML du début du formulaire.
     */
    private static function debForm(string $fich): string {
        return "<form action='$fich' method='post' class='app'>";
    }

    /**
     * Termine un formulaire HTML.
     *
     * @return string Code HTML de la fin du formulaire.
     */
    private static function finForm(): string {
        return "</form>";
    }

    /**
     * Génère des cases rouges avec des valeurs d'avancement.
     *
     * @param array $vitesse Tableau des valeurs d'avancement.
     * @param int $id ID de départ pour les cases.
     * @param int $nbCases Nombre de cases rouges avant/après les valeurs d'avancement.
     * @param string $disable Statut de désactivation.
     * @return string Code HTML des cases rouges.
     */
    private static function casesRouges(array $vitesse, int $id, int $nbCases, string $disable): string {
        $res = "";
        $compteur = $id;
        for ($i = 0; $i < $nbCases; $i++) {
            $res .= self::genererCaseRouge("btn$compteur", $disable) . "\n";
            $compteur++;
        }

        foreach ($vitesse as $v) {
            $res .= self::genererCaseAvancement($v, "btn$compteur", $disable) . "\n";
            $compteur++;
        }

        for ($i = 0; $i < $nbCases; $i++) {
            $res .= self::genererCaseRouge("btn$compteur", $disable) . "\n";
            $compteur++;
        }

        return $res;
    }

    /**
     * Génère l'interface utilisateur complète du plateau.
     *
     * @param string $fich Action du formulaire.
     * @return string Code HTML complet de l'interface du plateau.
     */

     public static function plateauUI(string $fich): string {
        $res = "";
        $res .= PieceSquadroUI::debForm($fich). "\n";
        $monPlateau = new self();

        // Désactive les cases en fonction des prises des joueurs
        $disable = $monPlateau->disableCases();

        // Génération des cases rouges
        $res .= PieceSquadroUI::casesRouges([1, 3, 2, 3, 1], 1, 2, "disabled"). "\n";
        $res .= PieceSquadroUI::casesRouges([3, 1, 2, 1, 3], 10, 1, "disabled"). "\n";
        $res .= PieceSquadroUI::casesRouges([3, 1, 2, 1, 3], 17, 2, "disabled"). "\n";
        $res .= PieceSquadroUI::casesRouges([1, 3, 2, 3, 1], 26, 1, "disabled"). "\n";

        // Génération des cases du plateau
        for ($i = 0; $i < count($monPlateau->plate->getPlateau()); $i++) {
            for ($j = 0; $j < count($monPlateau->plate->getPlateau()[$i]); $j++) {
                switch ($monPlateau->plate->getPiece($i, $j)->getCouleur()) {
                    case -2: //case neutre
                        $res .= PieceSquadroUI::genererCaseNeutre("btn$i-$j", "disabled"). "\n";
                        break;
                    case -1: // case vide 
                        $res .= PieceSquadroUI::genererCaseVide("btn$i-$j", "disabled"). "\n";
                        break;
                    case 0: // case blanche
                        $res .= PieceSquadroUI::genererCaseBlanche($i, $j, $monPlateau->plate->getPiece($i, $j)->getDirection(), "btn$i-$j", $disable). "\n";
                        break;
                    case 1: // case noir
                        $res .= PieceSquadroUI::genererCaseNoir($i, $j, $monPlateau->plate->getPiece($i, $j)->getDirection(), "btn$i-$j", $disable) . "\n";
                        break;
                }
            }
        }

        $res .= PieceSquadroUI::finForm(). "\n";
        return $res;
    }

    /**
     * Affiche une boîte de confirmation pour un déplacement de pièce.
     *
     * @param string $fich Action du formulaire.
     * @param string $pieceId Identifiant de la pièce.
     * @return string Code HTML de la confirmation.
     */
    public static function confirmationDeplacement(string $fich, string $pieceId): string {
        $res = "<div class='confirmation'>";
        $res .= PieceSquadroUI::debForm($fich);
        $res .= "<p>Voulez-vous déplacer la pièce $pieceId ?</p>";
        $res .= "<input type='submit' value='Confirmer' name='confirm' />";
        $res .= "<input type='hidden' name='pieceId' value='$pieceId' />";
        $res .= PieceSquadroUI::finForm();
        $res .= "</div>";
        return $res;
    }
    /**
     * Affiche un message de victoire pour un joueur.
     *
     * @param string $joueur Nom du joueur gagnant.
     * @return string Code HTML du message de victoire.
     */
    public static function afficherVictoire(string $joueur): string {
        $res = "<div class='victoire'>";
        $res .= "<h2>Le joueur $joueur a gagné !</h2>";
        $res .= "</div>";
        return $res;
    }
}
?>
