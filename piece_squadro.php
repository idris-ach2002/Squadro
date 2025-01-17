<?php
/**
 * Classe PieceSquadro
 * Représente une pièce du jeu Squadro ou une case du plateau.
 */
class PieceSquadro {
    // Constantes pour les couleurs
    public const NOIR = 1;
    public const VIDE = -1;
    public const BLANC = 0;
    public const NEUTRE = -2;

    // Constantes pour les directions
    public const NORD = 0;
    public const EST = 1;
    public const SUD = 2;
    public const OUEST = 3;

    // Variables d'instance
    protected int $couleur;
    protected int $direction;

    /**
     * Constructeur privé pour imposer l'utilisation de méthodes statiques de création.
     *
     * @param int $couleur La couleur de la pièce.
     * @param int $direction La direction de la pièce.
     */
    private function __construct(int $couleur, int $direction) {
        $this->couleur = $couleur;
        $this->direction = $direction;
    }

    /**
     * Méthode statique pour initialiser une case vide.
     *
     * @return PieceSquadro
     */
    public static function initVide(): PieceSquadro {
        return new self(self::VIDE, self::NEUTRE);
    }

    /**
     * Méthode statique pour initialiser une pièce noire allant vers le nord.
     *
     * @return PieceSquadro
     */
    public static function initNoirNord(): PieceSquadro {
        return new self(self::NOIR, self::NORD);
    }

    /**
     * Méthode statique pour initialiser une pièce blanche allant vers l'est.
     *
     * @return PieceSquadro
     */
    public static function initBlancEst(): PieceSquadro {
        return new self(self::BLANC, self::EST);
    }

    /**
     * Retourne la couleur de la pièce.
     *
     * @return int
     */
    public function getCouleur(): int {
        return $this->couleur;
    }

    /**
     * Retourne la direction de la pièce.
     *
     * @return int
     */
    public function getDirection(): int {
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


    
}
