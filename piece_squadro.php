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
?>