<?php

declare(strict_types=1);

require_once __DIR__ . '/plateau_squadro.php';
require_once __DIR__ . '/action_squadro.php';

/**
 * Service de lecture tactique du plateau.
 *
 * Le moteur de règles reste dans ActionSquadro/PlateauSquadro. Cette classe ne
 * fait que produire des coups légaux, des scores et des explications UI.
 */
final class SquadroAnalyzer
{
    /** @return array<int,array<string,mixed>> */
    public static function legalMoves(PlateauSquadro $plateau, string $color): array
    {
        $targetColor = self::colorToInt($color);
        $moves = [];

        for ($x = 0; $x < PlateauSquadro::SIZE; $x++) {
            for ($y = 0; $y < PlateauSquadro::SIZE; $y++) {
                $piece = $plateau->getPiece($x, $y);
                if (!$piece->isPlayable() || $piece->getCouleur() !== $targetColor) {
                    continue;
                }

                $candidate = self::analyzeMove($plateau, $color, $x, $y);
                if ($candidate !== null) {
                    $moves[] = $candidate;
                }
            }
        }

        usort(
            $moves,
            static fn(array $a, array $b): int => ($b['score'] <=> $a['score']) ?: ($b['distance'] <=> $a['distance'])
        );

        return $moves;
    }

    public static function bestMove(PlateauSquadro $plateau, string $color): ?array
    {
        $moves = self::legalMoves($plateau, $color);
        return $moves[0] ?? null;
    }

    public static function analyzeMove(PlateauSquadro $plateau, string $color, int $x, int $y): ?array
    {
        try {
            $piece = $plateau->getPiece($x, $y);
            if (!$piece->isPlayable() || $piece->getCouleur() !== self::colorToInt($color)) {
                return null;
            }

            [$destX, $destY] = $plateau->getCoordDestination($x, $y);
            if (!$plateau->isInside($destX, $destY)) {
                return null;
            }

            $path = self::path($plateau, $x, $y, $destX, $destY, $piece);
            $captured = [];
            foreach ($path as [$px, $py]) {
                $candidate = $plateau->getPiece($px, $py);
                if ($candidate->isPlayable() && $candidate->getCouleur() !== $piece->getCouleur()) {
                    $captured[] = [$px, $py];
                }
            }

            $distance = abs($destX - $x) + abs($destY - $y);
            $finish = self::isFinishingMove($piece, $destX, $destY);
            $turnaround = self::isTurnaroundMove($piece, $destX, $destY);
            $returning = in_array($piece->getDirection(), [PieceSquadro::OUEST, PieceSquadro::SUD], true);
            $score = self::score($distance, count($captured), $finish, $turnaround, $returning);

            $effects = [];
            if ($finish) {
                $effects[] = 'sortie';
            }
            if ($turnaround) {
                $effects[] = 'demi-tour';
            }
            if ($captured !== []) {
                $effects[] = count($captured) . ' recul' . (count($captured) > 1 ? 's' : '');
            }
            if ($effects === []) {
                $effects[] = $distance . ' case' . ($distance > 1 ? 's' : '');
            }

            return [
                'color' => $color,
                'origin' => [$x, $y],
                'destination' => [$destX, $destY],
                'distance' => $distance,
                'captures' => count($captured),
                'capturedPositions' => $captured,
                'finish' => $finish,
                'turnaround' => $turnaround,
                'returning' => $returning,
                'score' => $score,
                'token' => $color . ':' . $x . ':' . $y,
                'label' => ucfirst($color) . ' [' . $x . ',' . $y . '] → [' . $destX . ',' . $destY . ']',
                'effects' => $effects,
                'summary' => implode(' · ', $effects),
            ];
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Liste des pièces de $color actuellement exposées à un recul adverse.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function threatsAgainst(PlateauSquadro $plateau, string $color): array
    {
        $opponent = $color === 'blanc' ? 'noir' : 'blanc';
        $threats = [];

        foreach (self::legalMoves($plateau, $opponent) as $move) {
            foreach ($move['capturedPositions'] as $position) {
                [$x, $y] = $position;
                $piece = $plateau->getPiece((int) $x, (int) $y);
                if ($piece->isPlayable() && $piece->getCouleur() === self::colorToInt($color)) {
                    $threats[] = [
                        'target' => [$x, $y],
                        'by' => $move['origin'],
                        'move' => $move,
                    ];
                }
            }
        }

        return $threats;
    }

    /** @return array{whiteDone:int,blackDone:int,whiteRemaining:int,blackRemaining:int} */
    public static function metrics(PlateauSquadro $plateau): array
    {
        $whiteRemaining = count($plateau->getLignesJouables());
        $blackRemaining = count($plateau->getColonnesJouables());

        return [
            'whiteDone' => min(4, max(0, 5 - $whiteRemaining)),
            'blackDone' => min(4, max(0, 5 - $blackRemaining)),
            'whiteRemaining' => $whiteRemaining,
            'blackRemaining' => $blackRemaining,
        ];
    }

    private static function score(int $distance, int $captures, bool $finish, bool $turnaround, bool $returning): int
    {
        $score = $distance * 5 + $captures * 34;
        if ($finish) {
            $score += 140;
        }
        if ($turnaround) {
            $score += 32;
        }
        if ($returning) {
            $score += 10;
        }
        return $score;
    }

    /** @return array<int,array{0:int,1:int}> */
    private static function path(PlateauSquadro $plateau, int $x, int $y, int $destX, int $destY, PieceSquadro $piece): array
    {
        [$stepX, $stepY] = match ($piece->getDirection()) {
            PieceSquadro::EST => [0, 1],
            PieceSquadro::OUEST => [0, -1],
            PieceSquadro::NORD => [-1, 0],
            PieceSquadro::SUD => [1, 0],
            default => [0, 0],
        };

        $path = [];
        $curX = $x + $stepX;
        $curY = $y + $stepY;
        while ($plateau->isInside($curX, $curY)) {
            $path[] = [$curX, $curY];
            if ($curX === $destX && $curY === $destY) {
                break;
            }
            $curX += $stepX;
            $curY += $stepY;
        }

        return $path;
    }

    private static function isFinishingMove(PieceSquadro $piece, int $destX, int $destY): bool
    {
        return ($piece->getCouleur() === PieceSquadro::BLANC && $piece->getDirection() === PieceSquadro::OUEST && $destY === 0)
            || ($piece->getCouleur() === PieceSquadro::NOIR && $piece->getDirection() === PieceSquadro::SUD && $destX === 6);
    }

    private static function isTurnaroundMove(PieceSquadro $piece, int $destX, int $destY): bool
    {
        return ($piece->getCouleur() === PieceSquadro::BLANC && $piece->getDirection() === PieceSquadro::EST && $destY === 6)
            || ($piece->getCouleur() === PieceSquadro::NOIR && $piece->getDirection() === PieceSquadro::NORD && $destX === 0);
    }

    private static function colorToInt(string $color): int
    {
        return $color === 'noir' ? PieceSquadro::NOIR : PieceSquadro::BLANC;
    }
}
