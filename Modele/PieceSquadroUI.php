<?php

declare(strict_types=1);

require_once __DIR__ . '/plateau_squadro.php';
require_once __DIR__ . '/../Core/App.php';

final class PieceSquadroUI
{
    public static function createStyle(): string
    {
        return '';
    }

    /**
     * Compatibilité avec les anciens contrôleurs : retourne uniquement le plateau.
     */
    public static function plateauUI(PlateauSquadro $plateau, string $noir = 'disabled', string $blanc = 'enabled'): string
    {
        $activeColor = $blanc === 'enabled' ? 'blanc' : 'noir';
        return self::renderBoard($plateau, $activeColor, true, null, null);
    }

    public static function debForm(string $fich): string
    {
        return '<form action="' . App::e($fich) . '" method="post" class="legacy-form">';
    }

    public static function finForm(): string
    {
        return '</form>';
    }

    public static function confirmationDeplacement(string $action, string $id, PlateauSquadro $plateau): string
    {
        return self::renderBoard($plateau, App::activeTurnLabel(), false, $_SESSION['position'] ?? null, self::destinationOf($plateau, $_SESSION['position'] ?? null))
            . '<section class="action-dock visible"><p>Confirmer le déplacement de la pièce ' . App::e($id) . ' ?</p><form action="' . App::e($action) . '" method="post" class="actions-row"><button class="btn primary" name="choix" value="PRESEED">Sceller</button><button class="btn ghost" name="choix" value="ABORT">Retenir</button></form></section>';
    }

    public static function afficher_erreur(string $action, string $id, PlateauSquadro $plateau): string
    {
        return '<section class="alert danger">Déplacement impossible pour ' . App::e($id) . '.</section>'
            . self::renderBoard($plateau, App::activeTurnLabel(), true, null, null)
            . '<form action="' . App::e($action) . '" method="post"><button class="btn primary" name="erreur" value="1">Reprendre</button></form>';
    }

    public static function afficherVictoire(string $couleur, string $action): string
    {
        return '<section class="victory-card"><p class="eyebrow">Gloire à Sparte</p><h1>' . App::e(ucfirst($couleur)) . ' domine l’arène</h1><p>Quatre champions ont terminé leur marche. La victoire est gravée.</p><form action="' . App::e($action) . '" method="post"><button class="btn primary" name="rejouer" value="1">Nouvelle bataille</button></form></section>';
    }

    /**
     * @param array{mode:string, player:?JoueurSquadro, playerColor:?string, activeColor:string, allowMoves:bool, state:string, selected:?array<int,int>, destination:?array<int,int>, game:?PartieSquadro, flashes:array<int,array{type:string,message:string}>} $context
     */
    public static function renderGamePage(PlateauSquadro $plateau, array $context): string
    {
        $title = 'Squadro Arena';
        $activeColor = $context['activeColor'];
        $player = $context['player'];
        $mode = $context['mode'];
        $game = $context['game'];
        $state = $context['state'];
        $selected = $context['selected'];
        $destination = $context['destination'];
        $allowMoves = $context['allowMoves'];
        $metrics = self::metrics($plateau);
        $history = App::history();

        $bodyClass = $state === 'Victoire' ? 'app-bg greek-theme win-mode' : 'app-bg greek-theme';

        $html = '<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . App::e($title) . '</title><link rel="stylesheet" href="/assets/css/app.css"><link rel="stylesheet" href="/assets/css/greek-theme.css"><script defer src="/assets/js/app.js"></script><script defer src="/assets/js/sparta-effects.js"></script></head><body class="' . $bodyClass . '">';
        $html .= '<main class="game-shell">';
        $html .= self::renderTopbar($player, $mode, $activeColor, $context['playerColor'], $game);
        $html .= self::renderFlashes($context['flashes']);
        $html .= '<section class="game-layout">';
        $html .= self::renderLeftPanel($metrics, $activeColor, $mode, $game);
        $html .= '<section class="board-stage">';
        $html .= self::renderBoardHeader($state, $activeColor, $allowMoves, $selected, $destination);
        $html .= self::renderBoard($plateau, $activeColor, $allowMoves && $state === 'choixPiece', $selected, $destination);
        $html .= self::renderActionDock($state, $selected, $destination);
        $html .= '</section>';
        $html .= self::renderRightPanel($history, $metrics, $game);
        $html .= '</section>';

        if ($state === 'Victoire') {
            $html .= '<section class="victory-overlay"><div class="victory-card"><p class="eyebrow">Gloire à Sparte</p><h1>' . App::e(ucfirst($activeColor)) . ' domine l’arène</h1><p>Le camp ' . App::e($activeColor) . ' a ramené quatre étendards hors du plateau. La victoire est gravée.</p><form action="traiteActionSquadro.php" method="post" class="actions-row centered"><button class="btn primary" name="rejouer" value="1">Nouvelle bataille</button><a class="btn ghost" href="/Vue/choixAction.php">Agora</a></form></div></section>';
        }

        $html .= '</main></body></html>';
        return $html;
    }

    private static function renderTopbar(?JoueurSquadro $player, string $mode, string $activeColor, ?string $playerColor, ?PartieSquadro $game): string
    {
        $gameLabel = $game instanceof PartieSquadro ? '#' . $game->getPartieID() : 'Duel';
        $colorBadge = $playerColor ? '<span class="pill muted">Ton camp : ' . App::e($playerColor) . '</span>' : '<span class="pill muted">Duel d’entraînement</span>';

        return '<header class="topbar"><a class="brand" href="/Vue/choixAction.php"><span class="brand-mark">Λ</span><span><strong>Squadro</strong><small>Arène spartiate</small></span></a><nav class="topbar-nav"><span class="pill">Arène ' . App::e($gameLabel) . '</span><span class="pill ' . App::e($activeColor) . '">Initiative ' . App::e($activeColor) . '</span>' . $colorBadge . '</nav><div class="player-chip"><span class="avatar">' . App::e($player?->initials() ?? '?') . '</span><span>' . App::e($player?->getNomJoueur() ?? 'Invité') . '</span></div><form action="traiteActionSquadro.php" method="post" class="top-actions"><button class="icon-btn" name="sync" value="1" title="Rafraîchir l’arène">↻</button><button class="icon-btn" name="menu" value="1" title="Agora">☰</button></form></header>';
    }

    private static function renderFlashes(array $flashes): string
    {
        if ($flashes === []) {
            return '';
        }

        $html = '<section class="flash-stack">';
        foreach ($flashes as $flash) {
            $type = $flash['type'] ?? 'info';
            $message = $flash['message'] ?? '';
            $html .= '<div class="alert ' . App::e($type) . '">' . App::e($message) . '</div>';
        }
        return $html . '</section>';
    }

    private static function renderBoardHeader(string $state, string $activeColor, bool $allowMoves, ?array $selected, ?array $destination): string
    {
        $headline = match ($state) {
            'ConfirmationPiece' => 'Valide ton ordre',
            'erreur' => 'Ordre refusé',
            'Victoire' => 'Victoire gravée',
            default => $allowMoves ? 'Choisis ton champion ' . $activeColor : 'En attente du camp ' . $activeColor,
        };

        $sub = 'Survole un champion pour lire sa trajectoire. Tout adversaire croisé est repoussé jusqu’à son point de départ.';
        if ($selected && $destination) {
            $sub = 'Champion choisi : [' . $selected[0] . ',' . $selected[1] . '] → destination [' . $destination[0] . ',' . $destination[1] . '].';
        }

        return '<div class="board-header"><div><p class="eyebrow">Champ de bataille</p><h1>' . App::e($headline) . '</h1><p>' . App::e($sub) . '</p></div><form action="traiteActionSquadro.php" method="post" class="actions-row"><button class="btn subtle" name="undo" value="1">Replier</button><button class="btn subtle" name="rejouer" value="1">Réinitialiser</button></form></div>';
    }

    private static function renderLeftPanel(array $metrics, string $activeColor, string $mode, ?PartieSquadro $game): string
    {
        $status = $game?->getGameStatus() ?? 'local';
        $moveCount = $game?->getMoveCount() ?? count(App::history());
        return '<aside class="side-panel"><section class="panel"><p class="eyebrow">Mission</p><h2>Sortir 4 étendards</h2><p>Chaque champion traverse l’arène, revient sous la pression ennemie, puis sort du champ. Le premier camp qui sort quatre champions impose sa loi.</p><div class="stat-grid"><div><strong>' . App::e($metrics['whiteDone']) . '/4</strong><span>Blanc</span></div><div><strong>' . App::e($metrics['blackDone']) . '/4</strong><span>Noir</span></div><div><strong>' . App::e($moveCount) . '</strong><span>Ordres</span></div><div><strong>' . App::e($status) . '</strong><span>État</span></div></div></section><section class="panel"><p class="eyebrow">Code de l’arène</p><ul class="rules"><li>Les blancs percent les lignes horizontales.</li><li>Les noirs écrasent les colonnes verticales.</li><li>Les plaques de bronze indiquent la puissance de marche.</li><li>Un champion croisé est repoussé à son départ.</li></ul></section></aside>';
    }

    private static function renderRightPanel(array $history, array $metrics, ?PartieSquadro $game): string
    {
        $lastMove = $game?->getLastMove();
        $html = '<aside class="side-panel"><section class="panel"><p class="eyebrow">Étendards sortis</p>';
        $html .= self::progressRow('Blanc', $metrics['whiteDone'], 'white');
        $html .= self::progressRow('Noir', $metrics['blackDone'], 'black');
        $html .= '</section><section class="panel history"><p class="eyebrow">Chronique du duel</p>';
        if ($lastMove) {
            $html .= '<div class="history-item pinned"><span>Dernier</span><p>' . App::e($lastMove) . '</p></div>';
        }
        if ($history === []) {
            $html .= '<p class="muted-text">Aucun ordre gravé pour l’instant.</p>';
        } else {
            foreach ($history as $item) {
                $html .= '<div class="history-item"><span>' . App::e($item['at'] ?? '') . '</span><p>' . App::e($item['message'] ?? '') . '</p></div>';
            }
        }
        return $html . '</section></aside>';
    }

    private static function progressRow(string $label, int $done, string $theme): string
    {
        $done = min(4, max(0, $done));
        $items = '';
        for ($i = 1; $i <= 4; $i++) {
            $items .= '<span class="progress-dot ' . ($i <= $done ? 'done' : '') . '"></span>';
        }
        return '<div class="progress-row ' . App::e($theme) . '"><div><strong>' . App::e($label) . '</strong><small>' . $done . '/4 hors de l’arène</small></div><div class="progress-dots">' . $items . '</div></div>';
    }

    private static function renderActionDock(string $state, ?array $selected, ?array $destination): string
    {
        if ($state !== 'ConfirmationPiece' || !$selected) {
            return '';
        }

        $destinationLabel = $destination ? '[' . $destination[0] . ',' . $destination[1] . ']' : 'inconnue';
        return '<section class="action-dock visible"><div><strong>Sceller cet ordre ?</strong><p>[' . App::e($selected[0]) . ',' . App::e($selected[1]) . '] marchera vers ' . App::e($destinationLabel) . '.</p></div><form action="traiteActionSquadro.php" method="post" class="actions-row"><button class="btn primary" name="choix" value="PRESEED">Sceller</button><button class="btn ghost" name="choix" value="ABORT">Retenir</button></form></section>';
    }

    private static function renderBoard(PlateauSquadro $plateau, string $activeColor, bool $allowMoves, ?array $selected, ?array $destination): string
    {
        $html = '<form action="traiteActionSquadro.php" method="post" class="squadro-board" aria-label="Plateau Squadro">';

        for ($gridRow = 0; $gridRow <= 8; $gridRow++) {
            for ($gridCol = 0; $gridCol <= 8; $gridCol++) {
                $html .= self::renderGridCell($plateau, $gridRow, $gridCol, $activeColor, $allowMoves, $selected, $destination);
            }
        }

        return $html . '</form>';
    }

    private static function renderGridCell(PlateauSquadro $plateau, int $gridRow, int $gridCol, string $activeColor, bool $allowMoves, ?array $selected, ?array $destination): string
    {
        if (self::isOuterCorner($gridRow, $gridCol)) {
            return '<div class="cell outer-corner portal" aria-hidden="true"></div>';
        }

        if ($gridRow === 0 && $gridCol >= 2 && $gridCol <= 6) {
            return self::speedCell(PlateauSquadro::NOIR_V_RETOUR[$gridCol - 1], 'top');
        }
        if ($gridRow === 8 && $gridCol >= 2 && $gridCol <= 6) {
            return self::speedCell(PlateauSquadro::NOIR_V_ALLER[$gridCol - 1], 'bottom');
        }
        if ($gridCol === 0 && $gridRow >= 2 && $gridRow <= 6) {
            return self::speedCell(PlateauSquadro::BLANC_V_ALLER[$gridRow - 1], 'left');
        }
        if ($gridCol === 8 && $gridRow >= 2 && $gridRow <= 6) {
            return self::speedCell(PlateauSquadro::BLANC_V_RETOUR[$gridRow - 1], 'right');
        }

        if ($gridRow >= 1 && $gridRow <= 7 && $gridCol >= 1 && $gridCol <= 7) {
            $x = $gridRow - 1;
            $y = $gridCol - 1;
            return self::boardCell($plateau, $x, $y, $activeColor, $allowMoves, $selected, $destination);
        }

        return '<div class="cell rail-space" aria-hidden="true"></div>';
    }

    private static function boardCell(PlateauSquadro $plateau, int $x, int $y, string $activeColor, bool $allowMoves, ?array $selected, ?array $destination): string
    {
        $piece = $plateau->getPiece($x, $y);
        $classes = ['cell', 'board-cell'];
        if ($x >= 1 && $x <= 5 && $y >= 1 && $y <= 5) {
            $classes[] = 'battlefield';
        } else {
            $classes[] = 'track';
        }
        if ($selected && $selected[0] === $x && $selected[1] === $y) {
            $classes[] = 'selected-cell';
        }
        if ($destination && $destination[0] === $x && $destination[1] === $y) {
            $classes[] = 'destination-cell';
        }

        $content = '';
        if ($piece->getCouleur() === PieceSquadro::NEUTRE) {
            $classes[] = 'temple-cell';
            $content = '<span class="temple-mark">Λ</span>';
        } elseif ($piece->getCouleur() === PieceSquadro::BLANC || $piece->getCouleur() === PieceSquadro::NOIR) {
            $content = self::pieceButton($plateau, $piece, $x, $y, $activeColor, $allowMoves);
        }

        return '<div class="' . implode(' ', $classes) . '" data-cell="' . $x . '-' . $y . '">' . $content . '</div>';
    }

    private static function pieceButton(PlateauSquadro $plateau, PieceSquadro $piece, int $x, int $y, string $activeColor, bool $allowMoves): string
    {
        $color = $piece->getCouleur() === PieceSquadro::BLANC ? 'blanc' : 'noir';
        $direction = self::directionLabel($piece->getDirection());
        $name = $color;
        $value = 'btn' . $x . '-' . $y;
        $enabled = $allowMoves && $color === $activeColor;
        $destination = self::destinationOf($plateau, [$x, $y]);
        $destAttr = $destination ? ' data-destination="' . App::e($destination[0] . '-' . $destination[1]) . '"' : '';
        $disabled = $enabled ? '' : ' disabled';
        $title = ucfirst($color) . ' ' . $direction . ' — position ' . $x . ',' . $y;

        return '<button type="submit" class="piece-button piece-' . App::e($color) . ' dir-' . App::e(strtolower($direction)) . '" name="' . App::e($name) . '" value="' . App::e($value) . '"' . $destAttr . $disabled . ' title="' . App::e($title) . '"><span class="piece-icon"></span><span class="piece-meta">' . App::e($direction) . '</span></button>';
    }

    private static function speedCell(int $value, string $position): string
    {
        return '<div class="cell speed speed-' . App::e($position) . '"><span>' . App::e($value) . '</span></div>';
    }

    private static function isOuterCorner(int $row, int $col): bool
    {
        return ($row === 0 || $row === 8) && ($col === 0 || $col === 8);
    }

    private static function directionLabel(int $direction): string
    {
        return match ($direction) {
            PieceSquadro::NORD => 'N',
            PieceSquadro::SUD => 'S',
            PieceSquadro::EST => 'E',
            PieceSquadro::OUEST => 'O',
            default => '?',
        };
    }

    /** @param ?array<int,int> $position */
    public static function destinationOf(PlateauSquadro $plateau, ?array $position): ?array
    {
        if (!$position || count($position) < 2) {
            return null;
        }
        try {
            [$x, $y] = $plateau->getCoordDestination((int) $position[0], (int) $position[1]);
            if ($x < 0 || $x > 6 || $y < 0 || $y > 6) {
                return null;
            }
            return [$x, $y];
        } catch (Throwable) {
            return null;
        }
    }

    /** @return array{whiteDone:int,blackDone:int,whiteRemaining:int,blackRemaining:int} */
    private static function metrics(PlateauSquadro $plateau): array
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
}
