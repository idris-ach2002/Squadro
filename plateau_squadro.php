<?php

class PlateauSquadro {
    // Constantes
    public const BLANC_V_ALLER = [0, 1, 3, 2, 3, 1, 0];
    public const NOIR_V_RETOUR = [0, 1, 3, 2, 3, 1, 0];
    public const BLANC_V_RETOUR = [0, 3, 1, 2, 1, 3, 0];
    public const NOIR_V_ALLER = [0, 3, 1, 2, 1, 3, 0];

    // Attributs privés
    private array $plateau;
    private array $lignesJouables = [1, 2, 3, 4, 5];
    private array $colonnesJouables = [1, 2, 3, 4, 5];

    // Constructeur par défaut
    public function __construct() {
        $this->initPlateau();
    }

    // Méthodes privées d'initialisation
    private function initPlateau(): void {
        $this->plateau = array_fill(0, 7, array_fill(0, 7, null));
        $this->initCasesVides();
        $this->initCasesNeutres();
        $this->initCasesNoires();
        $this->initCasesBlanches();
    }

    private function initCasesVides(): void {
        for ($i = 1; $i <= 5; $i++) {
            for ($j = 1; $j <= 5; $j++) {
                $this->plateau[$i][$j] = PieceSquadro::initVide();
            }
        }
    }

    private function initCasesNeutres(): void {
        $this->plateau[0][0] = PieceSquadro::initNeutre();
        $this->plateau[0][6] = PieceSquadro::initNeutre();
        $this->plateau[6][0] = PieceSquadro::initNeutre();
        $this->plateau[6][6] = PieceSquadro::initNeutre();
    }

    private function initCasesNoires(): void {
        for ($i = 1; $i <= 5; $i++) {
            $this->plateau[6][$i] = PieceSquadro::initNoirNord();
            $this->plateau[0][$i] = PieceSquadro::initNoirSud();
        }
    }

    private function initCasesBlanches(): void {
        for ($i = 1; $i <= 5; $i++) {
            $this->plateau[$i][6] = PieceSquadro::initBlancOuest();
            $this->plateau[$i][0] = PieceSquadro::initBlancEst();
        }
    }

    // Méthodes publiques
    public function retireLigneJouable(int $index): void {
        $this->lignesJouables = array_values(array_diff($this->lignesJouables, [$index]));
    }

    public function retireColonneJouable(int $index): void {
        $this->colonnesJouables = array_values(array_diff($this->colonnesJouables, [$index]));
    }

    public function getCoordDestination(int $x, int $y): array {
        $piece = $this->plateau[$x][$y];
        $vitesse = ($piece->couleur === PieceSquadro::BLANC) ? self::BLANC_V_ALLER[$x] : self::NOIR_V_ALLER[$y];
        $direction = $piece->direction;

        switch ($direction) {
            case PieceSquadro::NORD:
                return [$x - $vitesse, $y];
            case PieceSquadro::EST:
                return [$x, $y + $vitesse];
            case PieceSquadro::SUD:
                return [$x + $vitesse, $y];
            case PieceSquadro::OUEST:
                return [$x, $y - $vitesse];
            default:
                throw new \InvalidArgumentException('Direction invalide');
        }
    }

    public function getDestination(int $x, int $y): PieceSquadro {
        [$destX, $destY] = $this->getCoordDestination($x, $y);
        return $this->plateau[$destX][$destY];
    }
}
?>