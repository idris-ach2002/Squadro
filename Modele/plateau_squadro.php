<?php
    require_once 'piece_squadro.php';
/**
 * Classe représentant le plateau de jeu Squadro.
 */
class PlateauSquadro {

    // Constantes de vitesse
    /** @var array<int> Vitesse des pièces blanches en direction aller */
    public const BLANC_V_ALLER = [0, 1, 3, 2, 3, 1, 0];
    /** @var array<int> Vitesse des pièces noires en direction retour */
    public const NOIR_V_RETOUR = [0, 1, 3, 2, 3, 1, 0];
    /** @var array<int> Vitesse des pièces blanches en direction retour */
    public const BLANC_V_RETOUR = [0, 3, 1, 2, 1, 3, 0];
    /** @var array<int> Vitesse des pièces noires en direction aller */
    public const NOIR_V_ALLER = [0, 3, 1, 2, 1, 3, 0];

    // Attributs privés
    /** @var array<array<PieceSquadro|null>> Plateau de jeu contenant les pièces */
    private array $plateau;
    /** @var array<int> Lignes jouables */
    private array $lignesJouables = [1, 2, 3, 4, 5];
    /** @var array<int> Colonnes jouables */
    private array $colonnesJouables = [1, 2, 3, 4, 5];

    /**
     * Constructeur par défaut : initialise le plateau.
     */
    public function __construct() {
        $this->initPlateau();
    }

    /**
     * Initialise le plateau de jeu.
     */
    private function initPlateau(): void {
        $this->plateau = array_fill(0, 7, array_fill(0, 7, null));
        $this->initCasesVides();
        $this->initCasesNeutres();
        $this->initCasesNoires();
        $this->initCasesBlanches();
    }

    /**
     * Initialise les cases vides du plateau.
     */
    private function initCasesVides(): void {
        for ($i = 1; $i <= 5; $i++) {
            $this->plateau[0][$i] = PieceSquadro::initVide();
            $this->plateau[$i][6] = PieceSquadro::initVide();
            for ($j = 1; $j <= 5; $j++) {
                $this->plateau[$i][$j] = PieceSquadro::initVide();
            }
        }
    }

    /**
     * Initialise les cases neutres du plateau.
     */
    private function initCasesNeutres(): void {
        $this->plateau[0][0] = PieceSquadro::initNeutre();
        $this->plateau[0][6] = PieceSquadro::initNeutre();
        $this->plateau[6][0] = PieceSquadro::initNeutre();
        $this->plateau[6][6] = PieceSquadro::initNeutre();
    }

    /**
     * Initialise les pièces noires sur le plateau.
     */
    private function initCasesNoires(): void {
        for ($i = 1; $i <= 5; $i++) {
            $this->plateau[6][$i] = PieceSquadro::initNoirNord();
        }
    }

    /**
     * Initialise les pièces blanches sur le plateau.
     */
    private function initCasesBlanches(): void {
        for ($i = 1; $i <= 5; $i++) {
            $this->plateau[$i][0] = PieceSquadro::initBlancEst();
        }
    }

    /**
     * Retire une ligne des lignes jouables.
     * 
     * @param int $index Indice de la ligne à retirer.
     */
    public function retireLigneJouable(int $index): void {
        $key = array_search($index, $this->lignesJouables, true);
        if ($key !== false) {
            unset($this->lignesJouables[$key]);
            $this->lignesJouables = array_values($this->lignesJouables); // Optionnel
        }
    }
    

    /**
     * Retire une colonne des colonnes jouables.
     * 
     * @param int $index Indice de la colonne à retirer.
     */
    public function retireColonneJouable(int $index): void {
        $key = array_search($index, $this->colonnesJouables, true);
        if ($key !== false) {
            unset($this->colonnesJouables[$key]);
            $this->colonnesJouables = array_values($this->colonnesJouables); // Optionnel
        }
    }



    /**
     * Calcule la destination d'une pièce.
     * 
     * @param int $x Ligne actuelle de la pièce.
     * @param int $y Colonne actuelle de la pièce.
     * @return array<int, int> Coordonnées [ligne, colonne] de destination.
     * @throws \InvalidArgumentException Si la direction est invalide.
     */
    public function getCoordDestination(int $x, int $y): array {
        $piece = $this->plateau[$x][$y];
        if ($piece === null) {
            throw new \InvalidArgumentException("Aucune pièce trouvée en ($x, $y)");
        }

        $direction = $piece->getDirection();
        $couleur = $piece->getCouleur();
        $vitesse = 0;

        if ($couleur === PieceSquadro::BLANC) {
            $vitesse = ($direction === PieceSquadro::EST) ? self::BLANC_V_ALLER[$x] : self::BLANC_V_RETOUR[$x];
            $newY = ($direction === PieceSquadro::EST) ? $y + $vitesse : $y - $vitesse;
            return [$x, $newY];
        } elseif ($couleur === PieceSquadro::NOIR) {
            $vitesse = ($direction === PieceSquadro::NORD) ? self::NOIR_V_ALLER[$y] : self::NOIR_V_RETOUR[$y];
            $newX = ($direction === PieceSquadro::NORD) ? $x - $vitesse : $x + $vitesse;
            return [$newX, $y];
        }

        throw new \InvalidArgumentException("Direction inconnue");
    }

    /**
     * Retourne la pièce à la destination calculée pour une pièce donnée.
     *
     * @param int $x La ligne actuelle de la pièce.
     * @param int $y La colonne actuelle de la pièce.
     * @return PieceSquadro La pièce située à la destination calculée.
     * @throws \InvalidArgumentException Si les coordonnées sont invalides.
     */
    public function getDestination(int $x, int $y): ?PieceSquadro {
        [$newX, $newY] = $this->getCoordDestination($x, $y);

        if ($newX < 0 || $newX >= 7 || $newY < 0 || $newY >= 7) {
            throw new \InvalidArgumentException("Coordonnées hors limites : ($newX, $newY)");
        }

        return $this->plateau[$newX][$newY] ?? null;
    }
    
    

    /**
     * Convertit l'état actuel du plateau en une chaîne JSON.
     *
     * @return string La représentation JSON du plateau, des lignes jouables et des colonnes jouables.
     * @throws \RuntimeException Si une erreur survient lors de l'encodage JSON.
     */
    public function toJson(): string {
        $plateau = array_map(
            fn(array $ligne) => array_map(
                fn(PieceSquadro $piece) => json_decode($piece->toJson(), true),
                $ligne
            ),
            $this->plateau
        );

        $json = json_encode([
            'plateau' => $plateau,
            'lignesJouables' => $this->lignesJouables,
            'colonnesJouables' => $this->colonnesJouables
        ]);

        if ($json === false) {
            throw new \RuntimeException('Erreur lors de l\'encodage JSON : ' . json_last_error_msg());
        }

        return $json;
    }

    /**
     * Reconstruit un objet PlateauSquadro à partir d'une chaîne JSON.
     *
     * @param string $json La représentation JSON du plateau.
     * @return PlateauSquadro Une instance de PlateauSquadro recréée depuis le JSON.
     * @throws \InvalidArgumentException Si une erreur survient lors du décodage JSON.
     */
    public static function fromJson(string $json): PlateauSquadro {
        $data = json_decode($json, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Erreur lors du décodage JSON : ' . json_last_error_msg());
        }

        if (!isset($data['plateau'], $data['lignesJouables'], $data['colonnesJouables'])) {
            throw new \InvalidArgumentException('Données JSON invalides pour recréer un PlateauSquadro');
        }

        $plateauSquadro = new self();
        $plateauSquadro->plateau = array_map(
            fn(array $ligne) => array_map(
                fn(array $piece) => PieceSquadro::fromJson(json_encode($piece)),
                $ligne
            ),
            $data['plateau']
        );
        $plateauSquadro->lignesJouables = array_values($data['lignesJouables']);
        $plateauSquadro->colonnesJouables = array_values($data['colonnesJouables']);

        return $plateauSquadro;
    }

    /**
     * Retourne la représentation JSON du plateau en tant que chaîne.
     *
     * @return string La représentation JSON du plateau.
     */
    public function __toString(): string {
        return $this->toJson();
    }

    /**
     * Retourne les lignes actuellement jouables.
     *
     * @return array Les indices des lignes jouables.
     */
    public function getLignesJouables(): array {
        return $this->lignesJouables;
    }

    /**
     * Retourne les colonnes actuellement jouables.
     *
     * @return array Les indices des colonnes jouables.
     */
    public function getColonnesJouables(): array {
        return $this->colonnesJouables;
    }

    /**
     * Retourne le plateau de jeu.
     *
     * @return array La matrice représentant le plateau de jeu.
     */
    public function getPlateau(): array {
        return $this->plateau;
    }

    /**
     * Retourne une pièce spécifique du plateau.
     *
     * @param int $ligne L'indice de la ligne de la pièce.
     * @param int $colonne L'indice de la colonne de la pièce.
     * @return PieceSquadro La pièce à la position donnée.
     * @throws \OutOfBoundsException Si les indices sont hors du plateau.
     */
    public function getPiece(int $ligne, int $colonne): PieceSquadro {
        return $this->plateau[$ligne][$colonne];
    }

    /**
     * Définit une pièce à une position spécifique sur le plateau.
     *
     * @param PieceSquadro $piece La pièce à placer.
     * @param int $ligne L'indice de la ligne où placer la pièce.
     * @param int $colonne L'indice de la colonne où placer la pièce.
     * @throws \OutOfBoundsException Si les indices sont hors du plateau.
     */
    public function setPiece(PieceSquadro $piece, int $ligne, int $colonne): void {
        $this->plateau[$ligne][$colonne] = $piece;
    }

}

    function testUnitairePlateau() {
                // Générer un plateau
        $plateau = new PlateauSquadro();
        echo $plateau;

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

    }

    //testUnitairePlateau();
