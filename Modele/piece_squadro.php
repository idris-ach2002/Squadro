<?php

class PieceSquadro {
    // type de case 
    public const NOIR = 1;
    public const VIDE = -1;
    public const BLANC = 0;
    public const NEUTRE = -2;
    public const NORD = 0;
    public const EST = 1;
    public const SUD = 2;
    public const OUEST = 3;

    // variable d'instances
    protected int $couleur;
    protected int $direction;

    // Constructeur privé
    private function __construct(int $couleur, int $direction) {
        $this->couleur = $couleur;
        $this->direction = $direction;
    }

    /**
     * renvoie la couleur de la pièce .
     *
     * @return int
     */

    public function getCouleur() : int {
        return $this->couleur;
    }
          /**
     * renvoie la direction de la pièce .
     *
     * @return int
     */

    public function getDirection() : int {
        return $this->direction;
    }


      /**
     * Change la direction de la pièce (inverse la direction actuelle).
     *
     * @return void
     */
    public function inverseDirection(): void {
        switch ($this->direction) {
            case self::NORD:
                $this->setDirection(self::SUD);
                break;
            case self::SUD:
                $this->setDirection(self::NORD);
                break;
            case self::EST:
                $this->setDirection(self::OUEST);
                break;
            case self::OUEST:
                $this->setDirection(self::EST);
                break;
        }
    }

    /**
     * Modifie la direction de la pièce.
     *
     * @param int $direction La nouvelle direction.
     * @return void
     */
    public function setDirection(int $direction): void {
        $this->direction = $direction;
    }


      /**
     * Renvoie une représentation textuelle de la pièce.
     *
     * @return string
     */
    public function __toString(): string {
        return "Piece [Couleur: $this->couleur, Direction: $this->direction]";
    }

    // Méthodes d'initialisation
    public static function initVide(): PieceSquadro {
        return new self(self::VIDE, self::NEUTRE);
    }

    public static function initNeutre(): PieceSquadro {
        return new self(self::NEUTRE, self::NEUTRE);
    }

    public static function initNoirNord(): PieceSquadro {
        return new self(self::NOIR, self::NORD);
    }

    public static function initNoirSud(): PieceSquadro {
        return new self(self::NOIR, self::SUD);
    }

    public static function initBlancEst(): PieceSquadro {
        return new self(self::BLANC, self::EST);
    }

    public static function initBlancOuest(): PieceSquadro {
        return new self(self::BLANC, self::OUEST);
    }

    // Méthode toJson
    public function toJson(): string {
        $json = json_encode([
            'couleur' => $this->couleur,
            'direction' => $this->direction
        ]);

        if ($json === false) {
            throw new \RuntimeException('Erreur lors de l\'encodage JSON : ' . json_last_error_msg());
        }

        return $json;
    }

    // Méthode fromJson
    public static function fromJson(string $json): PieceSquadro {
        $data = json_decode($json, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Erreur lors du décodage JSON : ' . json_last_error_msg());
        }

        if (!isset($data['couleur']) || !isset($data['direction'])) {
            throw new \InvalidArgumentException('Données JSON invalides pour créer une PieceSquadro');
        }

        return new self($data['couleur'], $data['direction']);
    }
}




    // ____________________________Les Tests ____________________________________


    // $pieceJson = array();
    // function genererPiece() : array {
    //     $res = array();
    //     $piece1 = PieceSquadro::initBlancOuest();
    //     $piece2 = PieceSquadro::initBlancEst();
    //     $piece3 = PieceSquadro::initNoirSud();
    //     $piece4 = PieceSquadro::initNoirNord();
    //     $piece5 = PieceSquadro::initVide();
    //     $piece6 = PieceSquadro::initNeutre();
        
    //     array_push($res, $piece1,$piece2,$piece3,$piece4,$piece5,$piece6);
    //     return $res;
    // }

    // // tableau représentant toutes les combinaison des pièces
    // $pieces = genererPiece();

    // echo count($pieces) ."<br/>";


    // //affichage des pièces
    // foreach($pieces as $p) {
    //     echo($p . "<br/>");
    // }

    // //inverser la direction des pièces
    // foreach($pieces as $p) {
    //     $p->inverseDirection();
    // }

    // //affichage des pièces
    // foreach($pieces as $p) {
    //     echo($p . "<br/>");
    // }

    // echo("<br/>");

    // //tranformation des pièces au format json
    // foreach($pieces as $p) {
    //     array_push($pieceJson, $p->toJson());
    // }

    // //print_r($pieceJson);

    // //echo("<br/>");

    // //l'opération inverse
    // // foreach($pieceJson as $pJson) {
    // //    print(PieceSquadro::fromJson($pJson) ."<br/>");
    // // };
?>

