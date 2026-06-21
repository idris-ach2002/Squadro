<?php

declare(strict_types=1);

require_once __DIR__ . '/piece_squadro.php';
require_once __DIR__ . '/plateau_squadro.php';

final class ActionSquadro
{
    private PlateauSquadro $plateau;

    public function __construct(PlateauSquadro $plateau)
    {
        $this->plateau = $plateau;
    }

    public function estJouablePiece(int $ligne, int $colonne): bool
    {
        try {
            return $this->plateau->getPiece($ligne, $colonne)->isPlayable();
        } catch (Throwable) {
            return false;
        }
    }

    public function jouePiece(int $x, int $y): bool
    {
        if (!$this->estJouablePiece($x, $y)) {
            return false;
        }

        [$destX, $destY] = $this->plateau->getCoordDestination($x, $y);
        if (!$this->plateau->isInside($destX, $destY)) {
            return false;
        }

        $this->reculerPiecesAdverses($x, $y, $destX, $destY);

        $piece = $this->plateau->getPiece($x, $y);
        $this->plateau->setPiece($piece, $destX, $destY);
        $this->plateau->setPiece(PieceSquadro::initVide(), $x, $y);

        $this->handleHalfTurn($piece, $destX, $destY);
        $this->handleFinishedPiece($piece, $x, $y, $destX, $destY);

        return true;
    }

    private function reculerPiecesAdverses(int $x, int $y, int $destX, int $destY): void
    {
        $piece = $this->plateau->getPiece($x, $y);
        if (!$piece->isPlayable()) {
            return;
        }

        [$stepX, $stepY] = $this->movementStep($piece);
        $curX = $x + $stepX;
        $curY = $y + $stepY;

        while ($this->plateau->isInside($curX, $curY)) {
            $candidate = $this->plateau->getPiece($curX, $curY);
            if ($candidate->isPlayable() && $candidate->getCouleur() !== $piece->getCouleur()) {
                [$startX, $startY] = $this->startingPositionFor($candidate, $curX, $curY);
                $this->plateau->setPiece($candidate, $startX, $startY);
                $this->plateau->setPiece(PieceSquadro::initVide(), $curX, $curY);
            }

            if ($curX === $destX && $curY === $destY) {
                break;
            }
            $curX += $stepX;
            $curY += $stepY;
        }
    }

    /** @return array{0:int,1:int} */
    private function movementStep(PieceSquadro $piece): array
    {
        return match ($piece->getDirection()) {
            PieceSquadro::EST => [0, 1],
            PieceSquadro::OUEST => [0, -1],
            PieceSquadro::NORD => [-1, 0],
            PieceSquadro::SUD => [1, 0],
            default => [0, 0],
        };
    }

    /** @return array{0:int,1:int} */
    private function startingPositionFor(PieceSquadro $piece, int $x, int $y): array
    {
        if ($piece->getCouleur() === PieceSquadro::BLANC) {
            return [$x, $piece->getDirection() === PieceSquadro::EST ? 0 : 6];
        }

        return [$piece->getDirection() === PieceSquadro::SUD ? 0 : 6, $y];
    }

    private function handleHalfTurn(PieceSquadro $piece, int $destX, int $destY): void
    {
        if (($piece->getCouleur() === PieceSquadro::BLANC && $destY === 6)
            || ($piece->getCouleur() === PieceSquadro::NOIR && $destX === 0)
        ) {
            $piece->inverseDirection();
        }
    }

    private function handleFinishedPiece(PieceSquadro $piece, int $originX, int $originY, int $destX, int $destY): void
    {
        if ($piece->getCouleur() === PieceSquadro::BLANC && $destY === 0 && $piece->getDirection() === PieceSquadro::OUEST) {
            $this->sortPiece(PieceSquadro::BLANC, $originX);
        }

        if ($piece->getCouleur() === PieceSquadro::NOIR && $destX === 6 && $piece->getDirection() === PieceSquadro::SUD) {
            $this->sortPiece(PieceSquadro::NOIR, $originY);
        }
    }

    public function sortPiece(int $couleur, int $rang): void
    {
        if ($couleur === PieceSquadro::BLANC) {
            $this->plateau->retireLigneJouable($rang);
            $this->plateau->setPiece(PieceSquadro::initVide(), $rang, 0);
        } elseif ($couleur === PieceSquadro::NOIR) {
            $this->plateau->retireColonneJouable($rang);
            $this->plateau->setPiece(PieceSquadro::initVide(), 6, $rang);
        }
    }

    public function remporteVictoire(int $couleur): bool
    {
        $piecesRestantes = $couleur === PieceSquadro::BLANC
            ? $this->plateau->getLignesJouables()
            : $this->plateau->getColonnesJouables();

        return count($piecesRestantes) <= 1;
    }
}
