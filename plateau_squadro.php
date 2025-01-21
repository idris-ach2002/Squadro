<?php
    require_once 'PieceSquadro.php';

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
        $vitesse = ($piece->getCouleur() === PieceSquadro::BLANC) ? 
                   (($piece->getDirection() === PieceSquadro::EST) ? self::BLANC_V_ALLER[$x] : self::BLANC_V_RETOUR[$x]) : 
                   (($piece->getDirection() === PieceSquadro::NORD) ? self::NOIR_V_RETOUR[$y] : self::NOIR_V_ALLER[$y]);
        $direction = $piece->getDirection();
        $destX = $x;
        $destY = $y;
    
        switch ($direction) {
            case PieceSquadro::NORD:
                $destX = max(0, $x - $vitesse);
                if ($destX === 0) {
                    $piece->inverseDirection();
                }
                break;
            case PieceSquadro::EST:
                $destY = min(6, $y + $vitesse);
                if ($destY === 6) {
                    $piece->inverseDirection();
                }
                break;
            case PieceSquadro::SUD:
                $destX = min(6, $x + $vitesse);
                if ($destX === 6) {
                    $piece->inverseDirection();
                }
                break;
            case PieceSquadro::OUEST:
                $destY = max(0, $y - $vitesse);
                if ($destY === 0) {
                    $piece->inverseDirection();
                }
                break;
            default:
                throw new \InvalidArgumentException('Direction invalide');
        }
    
        return [$destX, $destY];
    }
    
    public function getDestination(int $x, int $y): PieceSquadro {
        [$destX, $destY] = $this->getCoordDestination($x, $y);
        return $this->plateau[$destX][$destY];
    }

    // Méthode toJson
    public function toJson(): string {
        $json = json_encode([
            'plateau' => $this->plateau,
            'lignesJouables' => $this->lignesJouables,
            'colonnesJouables' => $this->colonnesJouables
        ]);

        if ($json === false) {
            throw new \RuntimeException('Erreur lors de l\'encodage JSON : ' . json_last_error_msg());
        }

        return $json;
    }

    // Méthode fromJson
    public static function fromJson(string $json): PlateauSquadro {
        $data = json_decode($json, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Erreur lors du décodage JSON : ' . json_last_error_msg());
        }

        $plateauSquadro = new self();
        $plateauSquadro->plateau = $data['plateau'];
        $plateauSquadro->lignesJouables = $data['lignesJouables'];
        $plateauSquadro->colonnesJouables = $data['colonnesJouables'];

        return $plateauSquadro;
    }

    // Méthode __toString
    public function __toString(): string {
        return $this->toJson();
    }

    // Méthodes d'accès
    public function getLignesJouables(): array {
        return $this->lignesJouables;
    }

    public function getColonnesJouables(): array {
        return $this->colonnesJouables;
    }

    public function getPlateau(): array {
        return $this->plateau;
    }

    public function getPiece(int $ligne, int $colonne): PieceSquadro {
        return $this->plateau[$ligne][$colonne];
    }

    public function setPiece(PieceSquadro $piece, int $ligne, int $colonne): void {
        $this->plateau[$ligne][$colonne] = $piece;
    }
}


    // Générer un plateau
    $plateau = new PlateauSquadro();

    // Test 1 : Vérification de l'initialisation du plateau
    echo "Test 1 : Initialisation du plateau<br/>";
    $plateauInit = $plateau->getPlateau();
    foreach ($plateauInit as $ligne) {
        foreach ($ligne as $case) {
            echo $case . " ";
        }
        echo "<br/>";
    }

    // Test 2 : Vérification des lignes et colonnes jouables
    echo "<br/>Test 2 : Lignes et colonnes jouables<br/>";
    print_r($plateau->getLignesJouables());
    print_r($plateau->getColonnesJouables());

    // Test 3 : Retrait de lignes et colonnes jouables
    echo "<br/>Test 3 : Retrait de lignes et colonnes jouables  ligne 1 colonne 5<br/>";
    $plateau->retireLigneJouable(1);
    $plateau->retireColonneJouable(5);
    print_r($plateau->getLignesJouables());
    print_r($plateau->getColonnesJouables());

    // Test 4 : Vérification de la destination
    echo "<br/>Test 4 : Destination d'une pièce<br/>";
    $destCoords = $plateau->getCoordDestination(6, 1); // Coordonnées initiales
    echo "Destination de (6, 1) : (" . $destCoords[0] . ", " . $destCoords[1] . ")\n";

    // Test 5 : Modifier une pièce et vérifier la modification
    echo "<br/>Test 5 : Modification d'une pièce<br/>";
    $newPiece = PieceSquadro::initBlancEst();
    $plateau->setPiece($newPiece, 1, 1);
    echo "<br/>Nouvelle pièce en (1, 1) : " . $plateau->getPiece(1, 1) . "<br/>";

    // Test 6 : Conversion en JSON
    echo "<br/>Test 6 : Conversion en JSON<br/>";
    $json = $plateau->toJson();
    echo $json . "<br/>";

    // Test 7 : Récupération depuis JSON
    echo "<br/>Test 7 : Récupération depuis JSON<br/>";
    $newPlateau = PlateauSquadro::fromJson($json);
    print_r($newPlateau->getLignesJouables());
    print_r($newPlateau->getColonnesJouables());

    // Test 8 : Cas limite - pièce en dehors des limites
    echo "<br/>Test 8 : Cas limite (coordonnées hors limite)<br/>";
    try {
        $plateau->getCoordDestination(7, 1); // Coordonnées hors limite
    } catch (\InvalidArgumentException $e) {
        echo "Erreur détectée : " . $e->getMessage() . "<br/>";
    }

    // Test 9 : Cas limite - JSON mal formé
    echo "<br/>Test 9 : Cas limite (JSON mal formé)<br/>";
    try {
        PlateauSquadro::fromJson('{"invalid": "data"}');
    } catch (\InvalidArgumentException $e) {
        echo "Erreur détectée : " . $e->getMessage() . "<br/>";
    }

    // Test 10 : Vérification de la méthode __toString
    echo "<br/>Test 10 : Méthode __toString<br/>";
    echo $plateau . "<br/>";

?>