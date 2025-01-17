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
}
?>