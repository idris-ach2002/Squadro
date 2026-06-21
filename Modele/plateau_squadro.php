<?php

declare(strict_types=1);

require_once __DIR__ . '/piece_squadro.php';

final class PlateauSquadro
{
    public const SIZE = 7;

    public const BLANC_V_ALLER = [0, 1, 3, 2, 3, 1, 0];
    public const NOIR_V_RETOUR = [0, 1, 3, 2, 3, 1, 0];
    public const BLANC_V_RETOUR = [0, 3, 1, 2, 1, 3, 0];
    public const NOIR_V_ALLER = [0, 3, 1, 2, 1, 3, 0];

    /** @var array<int,array<int,PieceSquadro>> */
    private array $plateau;
    /** @var array<int,int> */
    private array $lignesJouables = [1, 2, 3, 4, 5];
    /** @var array<int,int> */
    private array $colonnesJouables = [1, 2, 3, 4, 5];

    public function __construct()
    {
        $this->initPlateau();
    }

    private function initPlateau(): void
    {
        $this->plateau = [];
        for ($x = 0; $x < self::SIZE; $x++) {
            $this->plateau[$x] = [];
            for ($y = 0; $y < self::SIZE; $y++) {
                $this->plateau[$x][$y] = PieceSquadro::initVide();
            }
        }

        $this->plateau[0][0] = PieceSquadro::initNeutre();
        $this->plateau[0][6] = PieceSquadro::initNeutre();
        $this->plateau[6][0] = PieceSquadro::initNeutre();
        $this->plateau[6][6] = PieceSquadro::initNeutre();

        for ($i = 1; $i <= 5; $i++) {
            $this->plateau[$i][0] = PieceSquadro::initBlancEst();
            $this->plateau[6][$i] = PieceSquadro::initNoirNord();
        }
    }

    public function retireLigneJouable(int $index): void
    {
        $this->lignesJouables = array_values(array_filter(
            $this->lignesJouables,
            static fn(int $ligne): bool => $ligne !== $index
        ));
    }

    public function retireColonneJouable(int $index): void
    {
        $this->colonnesJouables = array_values(array_filter(
            $this->colonnesJouables,
            static fn(int $colonne): bool => $colonne !== $index
        ));
    }

    /** @return array{0:int,1:int} */
    public function getCoordDestination(int $x, int $y): array
    {
        $this->assertInside($x, $y);
        $piece = $this->getPiece($x, $y);

        if (!$piece->isPlayable()) {
            throw new InvalidArgumentException("Aucune pièce jouable en ($x,$y).");
        }

        if ($piece->getCouleur() === PieceSquadro::BLANC) {
            $speed = $piece->getDirection() === PieceSquadro::EST
                ? self::BLANC_V_ALLER[$x]
                : self::BLANC_V_RETOUR[$x];
            return [$x, $piece->getDirection() === PieceSquadro::EST ? $y + $speed : $y - $speed];
        }

        if ($piece->getCouleur() === PieceSquadro::NOIR) {
            $speed = $piece->getDirection() === PieceSquadro::NORD
                ? self::NOIR_V_ALLER[$y]
                : self::NOIR_V_RETOUR[$y];
            return [$piece->getDirection() === PieceSquadro::NORD ? $x - $speed : $x + $speed, $y];
        }

        throw new InvalidArgumentException('Couleur de pièce inconnue.');
    }

    public function getDestination(int $x, int $y): ?PieceSquadro
    {
        [$newX, $newY] = $this->getCoordDestination($x, $y);
        return $this->isInside($newX, $newY) ? $this->plateau[$newX][$newY] : null;
    }

    public function toJson(): string
    {
        return json_encode([
            'plateau' => array_map(
                static fn(array $ligne): array => array_map(
                    static fn(PieceSquadro $piece): array => json_decode($piece->toJson(), true, 512, JSON_THROW_ON_ERROR),
                    $ligne
                ),
                $this->plateau
            ),
            'lignesJouables' => $this->lignesJouables,
            'colonnesJouables' => $this->colonnesJouables,
        ], JSON_THROW_ON_ERROR);
    }

    public static function fromJson(string $json): PlateauSquadro
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data) || !isset($data['plateau'], $data['lignesJouables'], $data['colonnesJouables'])) {
            throw new InvalidArgumentException('Données JSON invalides pour PlateauSquadro.');
        }

        $plateauSquadro = new self();
        $plateauSquadro->plateau = array_map(
            static fn(array $ligne): array => array_map(
                static fn(array $piece): PieceSquadro => PieceSquadro::fromJson(json_encode($piece, JSON_THROW_ON_ERROR)),
                $ligne
            ),
            $data['plateau']
        );
        $plateauSquadro->lignesJouables = array_values(array_map('intval', $data['lignesJouables']));
        $plateauSquadro->colonnesJouables = array_values(array_map('intval', $data['colonnesJouables']));

        return $plateauSquadro;
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    /** @return array<int,int> */
    public function getLignesJouables(): array
    {
        return $this->lignesJouables;
    }

    /** @return array<int,int> */
    public function getColonnesJouables(): array
    {
        return $this->colonnesJouables;
    }

    /** @return array<int,array<int,PieceSquadro>> */
    public function getPlateau(): array
    {
        return $this->plateau;
    }

    public function getPiece(int $ligne, int $colonne): PieceSquadro
    {
        $this->assertInside($ligne, $colonne);
        return $this->plateau[$ligne][$colonne];
    }

    public function setPiece(PieceSquadro $piece, int $ligne, int $colonne): void
    {
        $this->assertInside($ligne, $colonne);
        $this->plateau[$ligne][$colonne] = $piece;
    }

    public function isInside(int $ligne, int $colonne): bool
    {
        return $ligne >= 0 && $ligne < self::SIZE && $colonne >= 0 && $colonne < self::SIZE;
    }

    private function assertInside(int $ligne, int $colonne): void
    {
        if (!$this->isInside($ligne, $colonne)) {
            throw new OutOfBoundsException("Coordonnées hors plateau : ($ligne,$colonne).");
        }
    }
}
