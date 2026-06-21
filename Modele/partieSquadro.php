<?php

declare(strict_types=1);

require_once __DIR__ . '/joueurSquadro.php';
require_once __DIR__ . '/plateau_squadro.php';

final class PartieSquadro
{
    private const PLAYER_ONE = 0;
    private const PLAYER_TWO = 1;

    private int $partieId = 0;
    /** @var array<int,JoueurSquadro> */
    private array $joueurs = [];
    private int $joueurActif = self::PLAYER_ONE;
    private string $gameStatus = 'initialized';
    private PlateauSquadro $plateau;
    private string $currentTurn = 'blanc';
    private ?string $winner = null;
    private ?string $lastMove = null;
    private int $moveCount = 0;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    public function __construct(JoueurSquadro $playerOne)
    {
        $this->joueurs[self::PLAYER_ONE] = $playerOne;
        $this->plateau = new PlateauSquadro();
    }

    public function addJoueur(JoueurSquadro $player): void
    {
        if (!$this->hasSecondPlayer()) {
            $this->joueurs[self::PLAYER_TWO] = $player;
        }
    }

    public function hasSecondPlayer(): bool
    {
        return isset($this->joueurs[self::PLAYER_TWO]);
    }

    public function getJoueurActif(): JoueurSquadro
    {
        return $this->joueurs[$this->joueurActif];
    }

    public function getNomJoueurActif(): string
    {
        return $this->getJoueurActif()->getNomJoueur();
    }

    public function __toString(): string
    {
        return "Partie #{$this->partieId} — tour {$this->currentTurn}";
    }

    public function getPartieID(): int
    {
        return $this->partieId;
    }

    public function setPartieID(int $id): void
    {
        $this->partieId = $id;
    }

    public function getGameStatus(): string
    {
        return $this->gameStatus;
    }

    public function setPartieStatus(string $status): void
    {
        $this->gameStatus = $status;
    }

    /** @return array<int,JoueurSquadro> */
    public function getJoueurs(): array
    {
        return $this->joueurs;
    }

    public function getPlayerOne(): JoueurSquadro
    {
        return $this->joueurs[self::PLAYER_ONE];
    }

    public function getPlayerTwo(): ?JoueurSquadro
    {
        return $this->joueurs[self::PLAYER_TWO] ?? null;
    }

    public function setPlateau(PlateauSquadro $plateau): void
    {
        $this->plateau = $plateau;
    }

    public function getPlateau(): PlateauSquadro
    {
        return $this->plateau;
    }

    public function getCurrentTurn(): string
    {
        return $this->currentTurn === 'noir' ? 'noir' : 'blanc';
    }

    public function setCurrentTurn(string $currentTurn): void
    {
        $this->currentTurn = $currentTurn === 'noir' ? 'noir' : 'blanc';
    }

    public function getWinner(): ?string
    {
        return $this->winner;
    }

    public function setWinner(?string $winner): void
    {
        $this->winner = $winner;
    }

    public function getLastMove(): ?string
    {
        return $this->lastMove;
    }

    public function setLastMove(?string $lastMove): void
    {
        $this->lastMove = $lastMove;
    }

    public function getMoveCount(): int
    {
        return $this->moveCount;
    }

    public function setMoveCount(int $moveCount): void
    {
        $this->moveCount = max(0, $moveCount);
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function toJson(): string
    {
        return json_encode([
            'partieId' => $this->partieId,
            'joueurs' => array_map(static fn(JoueurSquadro $joueur): array => json_decode($joueur->toJson(), true, 512, JSON_THROW_ON_ERROR), $this->joueurs),
            'joueurActif' => $this->joueurActif,
            'gameStatus' => $this->gameStatus,
            'plateau' => json_decode($this->plateau->toJson(), true, 512, JSON_THROW_ON_ERROR),
            'currentTurn' => $this->currentTurn,
            'winner' => $this->winner,
            'lastMove' => $this->lastMove,
            'moveCount' => $this->moveCount,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ], JSON_THROW_ON_ERROR);
    }

    public static function fromJson(string $json): PartieSquadro
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data) || !isset($data['joueurs'][self::PLAYER_ONE])) {
            throw new InvalidArgumentException('JSON partie invalide.');
        }

        $partie = new PartieSquadro(JoueurSquadro::fromJson(json_encode($data['joueurs'][self::PLAYER_ONE], JSON_THROW_ON_ERROR)));

        if (isset($data['joueurs'][self::PLAYER_TWO])) {
            $partie->addJoueur(JoueurSquadro::fromJson(json_encode($data['joueurs'][self::PLAYER_TWO], JSON_THROW_ON_ERROR)));
        }

        $partie->setPartieID((int) ($data['partieId'] ?? 0));
        $partie->joueurActif = (int) ($data['joueurActif'] ?? self::PLAYER_ONE);
        $partie->setPartieStatus((string) ($data['gameStatus'] ?? 'initialized'));
        if (isset($data['plateau'])) {
            $partie->setPlateau(PlateauSquadro::fromJson(json_encode($data['plateau'], JSON_THROW_ON_ERROR)));
        }
        $partie->setCurrentTurn((string) ($data['currentTurn'] ?? 'blanc'));
        $partie->setWinner(isset($data['winner']) ? (string) $data['winner'] : null);
        $partie->setLastMove(isset($data['lastMove']) ? (string) $data['lastMove'] : null);
        $partie->setMoveCount((int) ($data['moveCount'] ?? 0));
        $partie->setCreatedAt(isset($data['createdAt']) ? (string) $data['createdAt'] : null);
        $partie->setUpdatedAt(isset($data['updatedAt']) ? (string) $data['updatedAt'] : null);

        return $partie;
    }
}
