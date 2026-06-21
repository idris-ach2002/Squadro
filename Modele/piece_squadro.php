<?php

declare(strict_types=1);

final class PieceSquadro
{
    public const NOIR = 1;
    public const VIDE = -1;
    public const BLANC = 0;
    public const NEUTRE = -2;

    public const NORD = 0;
    public const EST = 1;
    public const SUD = 2;
    public const OUEST = 3;

    private int $couleur;
    private int $direction;

    private function __construct(int $couleur, int $direction)
    {
        $this->couleur = $couleur;
        $this->direction = $direction;
    }

    public function getCouleur(): int
    {
        return $this->couleur;
    }

    public function getDirection(): int
    {
        return $this->direction;
    }

    public function inverseDirection(): void
    {
        $this->direction = match ($this->direction) {
            self::NORD => self::SUD,
            self::SUD => self::NORD,
            self::EST => self::OUEST,
            self::OUEST => self::EST,
            default => $this->direction,
        };
    }

    public function setDirection(int $direction): void
    {
        if (!in_array($direction, [self::NORD, self::EST, self::SUD, self::OUEST, self::NEUTRE], true)) {
            throw new InvalidArgumentException('Direction de pièce invalide.');
        }
        $this->direction = $direction;
    }

    public function isEmpty(): bool
    {
        return $this->couleur === self::VIDE;
    }

    public function isNeutral(): bool
    {
        return $this->couleur === self::NEUTRE;
    }

    public function isPlayable(): bool
    {
        return !$this->isEmpty() && !$this->isNeutral();
    }

    public function colorName(): string
    {
        return match ($this->couleur) {
            self::BLANC => 'blanc',
            self::NOIR => 'noir',
            self::VIDE => 'vide',
            self::NEUTRE => 'neutre',
            default => 'inconnu',
        };
    }

    public function __toString(): string
    {
        return 'Piece[couleur=' . $this->couleur . ',direction=' . $this->direction . ']';
    }

    public static function initVide(): PieceSquadro
    {
        return new self(self::VIDE, self::NEUTRE);
    }

    public static function initNeutre(): PieceSquadro
    {
        return new self(self::NEUTRE, self::NEUTRE);
    }

    public static function initNoirNord(): PieceSquadro
    {
        return new self(self::NOIR, self::NORD);
    }

    public static function initNoirSud(): PieceSquadro
    {
        return new self(self::NOIR, self::SUD);
    }

    public static function initBlancEst(): PieceSquadro
    {
        return new self(self::BLANC, self::EST);
    }

    public static function initBlancOuest(): PieceSquadro
    {
        return new self(self::BLANC, self::OUEST);
    }

    public function toJson(): string
    {
        return json_encode([
            'couleur' => $this->couleur,
            'direction' => $this->direction,
        ], JSON_THROW_ON_ERROR);
    }

    public static function fromJson(string $json): PieceSquadro
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data) || !isset($data['couleur'], $data['direction'])) {
            throw new InvalidArgumentException('Données JSON invalides pour PieceSquadro.');
        }

        return new self((int) $data['couleur'], (int) $data['direction']);
    }
}
