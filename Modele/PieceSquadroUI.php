<?php

declare(strict_types=1);

require_once __DIR__ . '/plateau_squadro.php';
require_once __DIR__ . '/partieSquadro.php';
require_once __DIR__ . '/SquadroAnalyzer.php';
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
            . '<section class="action-dock visible"><p>Confirmer le déplacement de la pièce ' . App::e($id) . ' ?</p><form action="' . App::e($action) . '" method="post" class="actions-row"><button class="btn primary" name="choix" value="PRESEED">Confirmer</button><button class="btn ghost" name="choix" value="ABORT">Annuler</button></form></section>';
    }

    public static function afficher_erreur(string $action, string $id, PlateauSquadro $plateau): string
    {
        return '<section class="alert danger">Déplacement impossible pour ' . App::e($id) . '.</section>'
            . self::renderBoard($plateau, App::activeTurnLabel(), true, null, null)
            . '<form action="' . App::e($action) . '" method="post"><button class="btn primary" name="erreur" value="1">Reprendre</button></form>';
    }

    public static function afficherVictoire(string $couleur, string $action): string
    {
        return '<section class="victory-card"><p class="eyebrow">Victoire</p><h1>' . App::e(ucfirst($couleur)) . ' remporte la partie</h1><p>Quatre pièces ont terminé leur aller-retour. La partie est gagnée.</p><form action="' . App::e($action) . '" method="post"><button class="btn primary" name="rejouer" value="1">Nouvelle partie</button></form></section>';
    }

    /**
     * @param array{mode:string, player:?JoueurSquadro, playerColor:?string, activeColor:string, allowMoves:bool, state:string, selected:?array<int,int>, destination:?array<int,int>, game:?PartieSquadro, flashes:array<int,array{type:string,message:string}>} $context
     */
    public static function renderGamePage(PlateauSquadro $plateau, array $context): string
    {
        $title = 'Squadro — Arène Spartiate';
        $activeColor = $context['activeColor'];
        $player = $context['player'];
        $mode = $context['mode'];
        $game = $context['game'];
        $state = $context['state'];
        $selected = $context['selected'];
        $destination = $context['destination'];
        $allowMoves = $context['allowMoves'];
        $settings = App::settings();
        $metrics = SquadroAnalyzer::metrics($plateau);
        $history = App::history();
        $stats = App::stats();
        $moves = SquadroAnalyzer::legalMoves($plateau, $activeColor);
        $bestMove = $moves[0] ?? null;
        $threats = SquadroAnalyzer::threatsAgainst($plateau, $activeColor);

        $bodyClass = 'app-bg greek-game ' . ($state === 'Victoire' ? 'win-mode' : '');
        if (!empty($settings['cinematic'])) {
            $bodyClass .= ' cinematic-mode';
        }

        $html = '<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . App::e($title) . '</title><link rel="stylesheet" href="/assets/css/app.css"><link rel="stylesheet" href="/assets/css/greek-game.css"><script defer src="/assets/js/app.js"></script></head><body class="' . App::e($bodyClass) . '">';
        $html .= '<main class="game-shell" id="top">';
        $html .= self::renderTopbar($player, $mode, $activeColor, $context['playerColor'], $game, $settings);
        $html .= self::renderFlashes($context['flashes']);
        $html .= self::renderWarRoom($activeColor, $mode, $settings, $bestMove, $threats, $stats);
        $html .= '<section class="game-layout">';
        $html .= self::renderLeftPanel($metrics, $activeColor, $mode, $game, $settings, $bestMove, $threats, $moves);
        $html .= '<section class="board-stage" id="board">';
        $html .= self::renderBoardHeader($state, $activeColor, $allowMoves, $selected, $destination, $settings);
        $html .= self::renderBoard($plateau, $activeColor, $allowMoves && $state === 'choixPiece', $selected, $destination);
        $html .= self::renderActionDock($state, $selected, $destination);
        $html .= '</section>';
        $html .= self::renderRightPanel($history, $metrics, $stats, $settings, $mode, $game);
        $html .= '</section>';
        $html .= self::renderQuickCommandBar($state, $activeColor, $allowMoves, $settings, $bestMove);

        if ($state === 'Victoire') {
            $html .= '<section class="victory-overlay"><div class="victory-card"><p class="eyebrow">Fin de partie</p><h1>' . App::e(ucfirst($activeColor)) . ' gagne la manche</h1><p>Le camp ' . App::e($activeColor) . ' a sorti quatre pièces et verrouille la victoire.</p><form action="traiteActionSquadro.php" method="post" class="actions-row centered"><button class="btn primary" name="rejouer" value="1">Rejouer</button><a class="btn ghost" href="/Vue/choixAction.php">Menu</a></form></div></section>';
        }

        $html .= '</main></body></html>';
        return $html;
    }

    /** @param array<string,mixed> $settings */
    private static function renderTopbar(?JoueurSquadro $player, string $mode, string $activeColor, ?string $playerColor, ?PartieSquadro $game, array $settings): string
    {
        $gameLabel = $game instanceof PartieSquadro ? '#' . $game->getPartieID() : ($mode === 'bot' ? 'Oracle' : 'Local');
        $modeLabel = match ($mode) {
            'online' => $playerColor ? 'Vous jouez ' . $playerColor : 'Table en ligne',
            'bot' => 'Contre l’Oracle',
            default => 'Duel local',
        };

        return '<header class="topbar"><a class="brand" href="/Vue/choixAction.php"><span class="brand-mark">Λ</span><span><strong>Squadro</strong><small>Arène spartiate</small></span></a><nav class="topbar-nav"><span class="pill">Partie ' . App::e($gameLabel) . '</span><span class="pill ' . App::e($activeColor) . '">Tour ' . App::e($activeColor) . '</span><span class="pill muted">' . App::e($modeLabel) . '</span><span class="pill muted">' . ($settings['moveFlow'] === 'instant' ? 'Coup direct' : 'Validation fixe') . '</span></nav><div class="player-chip"><span class="avatar">' . App::e($player?->initials() ?? '?') . '</span><span>' . App::e($player?->getNomJoueur() ?? 'Invité') . '</span></div><form action="traiteActionSquadro.php" method="post" class="top-actions"><button class="icon-btn" name="oracle" value="1" title="Jouer le meilleur coup calculé">Ω</button><button class="icon-btn" name="export" value="1" title="Exporter la partie">⇩</button><button class="icon-btn" name="sync" value="1" title="Synchroniser">↻</button><button class="icon-btn" name="menu" value="1" title="Menu">☰</button></form></header>';
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

    /** @param array<string,mixed> $settings @param ?array<string,mixed> $bestMove @param array<int,array<string,mixed>> $threats @param array<string,int> $stats */
    private static function renderWarRoom(string $activeColor, string $mode, array $settings, ?array $bestMove, array $threats, array $stats): string
    {
        $best = $bestMove ? $bestMove['label'] . ' · ' . $bestMove['summary'] : 'Aucun coup légal';
        $threatLabel = count($threats) === 0 ? 'Aucune menace directe' : count($threats) . ' menace' . (count($threats) > 1 ? 's' : '') . ' détectée' . (count($threats) > 1 ? 's' : '');
        $tempo = max(0, time() - (int) ($stats['startedAt'] ?? time()));
        $minutes = intdiv($tempo, 60);
        $seconds = $tempo % 60;

        return '<section class="war-room"><div><p class="eyebrow">Salle de guerre</p><h1>Tour ' . App::e($activeColor) . '</h1><p>' . App::e($settings['moveFlow'] === 'instant' ? 'Clique une pièce jouable : le coup est appliqué immédiatement.' : 'Clique une pièce : la validation reste toujours visible en bas de l’écran.') . '</p></div><div class="war-metrics"><article><strong>' . App::e((string) $stats['moves']) . '</strong><span>Coups</span></article><article><strong>' . App::e((string) $stats['captures']) . '</strong><span>Reculs infligés</span></article><article><strong>' . App::e($minutes . ':' . str_pad((string) $seconds, 2, '0', STR_PAD_LEFT)) . '</strong><span>Temps</span></article></div><div class="oracle-strip"><span>Oracle</span><p>' . App::e($best) . '</p><small>' . App::e($threatLabel) . '</small></div></section>';
    }

    /** @param array<string,mixed> $settings */
    private static function renderBoardHeader(string $state, string $activeColor, bool $allowMoves, ?array $selected, ?array $destination, array $settings): string
    {
        $headline = match ($state) {
            'ConfirmationPiece' => 'Validation du déplacement',
            'erreur' => 'Action refusée',
            'Victoire' => 'Partie terminée',
            default => $allowMoves ? 'Choisis une pièce ' . $activeColor : 'En attente du joueur ' . $activeColor,
        };

        $sub = $settings['moveFlow'] === 'instant'
            ? 'Coup direct activé : plus besoin de descendre dans la page pour confirmer. Survole une pièce pour voir sa destination.'
            : 'Mode sécurisé : la confirmation est fixée en bas de l’écran, accessible sans scroll.';

        if ($selected && $destination) {
            $sub = 'Pièce sélectionnée : [' . $selected[0] . ',' . $selected[1] . '] → destination [' . $destination[0] . ',' . $destination[1] . '].';
        }

        return '<div class="board-header"><div><p class="eyebrow">Plateau 7×7</p><h1>' . App::e($headline) . '</h1><p>' . App::e($sub) . '</p></div><form action="traiteActionSquadro.php" method="post" class="actions-row"><button class="btn subtle" name="undo" value="1">Annuler</button><button class="btn subtle" name="oracle" value="1">Oracle</button><button class="btn subtle" name="rejouer" value="1">Reset</button></form></div>';
    }

    /** @param array<string,mixed> $settings @param ?array<string,mixed> $bestMove @param array<int,array<string,mixed>> $threats @param array<int,array<string,mixed>> $moves */
    private static function renderLeftPanel(array $metrics, string $activeColor, string $mode, ?PartieSquadro $game, array $settings, ?array $bestMove, array $threats, array $moves): string
    {
        $status = $game?->getGameStatus() ?? $mode;
        $moveCount = $game?->getMoveCount() ?? (int) App::stats()['moves'];
        $html = '<aside class="side-panel"><section class="panel"><p class="eyebrow">Objectif</p><h2>Sortir 4 pièces</h2><p>Chaque pièce traverse le plateau, revient dans l’autre sens, puis sort. La première couleur qui sort quatre pièces gagne.</p><div class="stat-grid"><div><strong>' . App::e($metrics['whiteDone']) . '/4</strong><span>Blanc</span></div><div><strong>' . App::e($metrics['blackDone']) . '/4</strong><span>Noir</span></div><div><strong>' . App::e($moveCount) . '</strong><span>Coups</span></div><div><strong>' . App::e($status) . '</strong><span>Statut</span></div></div></section>';

        if (!empty($settings['assist'])) {
            $html .= self::renderAdvisorPanel($activeColor, $bestMove, $threats, $moves);
        }

        $html .= '<section class="panel"><p class="eyebrow">Raccourcis</p><ul class="rules"><li><strong>1–5</strong> : jouer une pièce disponible.</li><li><strong>O</strong> : coup Oracle.</li><li><strong>U</strong> : annuler.</li><li><strong>F</strong> : plein écran plateau.</li><li><strong>M</strong> : retour menu.</li></ul></section></aside>';
        return $html;
    }

    /** @param ?array<string,mixed> $bestMove @param array<int,array<string,mixed>> $threats @param array<int,array<string,mixed>> $moves */
    private static function renderAdvisorPanel(string $activeColor, ?array $bestMove, array $threats, array $moves): string
    {
        $html = '<section class="panel advisor"><p class="eyebrow">Oracle tactique</p>';
        if ($bestMove) {
            $html .= '<h2>Coup conseillé</h2><p><strong>' . App::e($bestMove['label']) . '</strong><br><span>' . App::e($bestMove['summary']) . ' · score ' . App::e($bestMove['score']) . '</span></p><form action="traiteActionSquadro.php" method="post"><button class="btn primary full" name="move" value="' . App::e($bestMove['token']) . '">Jouer ce coup</button></form>';
        } else {
            $html .= '<h2>Aucun coup légal</h2><p>L’Oracle ne trouve pas d’action jouable pour ' . App::e($activeColor) . '.</p>';
        }

        $html .= '<div class="threat-box ' . (count($threats) > 0 ? 'danger' : 'safe') . '"><strong>' . count($threats) . '</strong><span>menace' . (count($threats) > 1 ? 's' : '') . ' directe' . (count($threats) > 1 ? 's' : '') . '</span></div>';
        $html .= '<div class="move-stack">';
        foreach (array_slice($moves, 0, 5) as $index => $move) {
            $html .= '<form action="traiteActionSquadro.php" method="post" class="move-chip"><button name="move" value="' . App::e($move['token']) . '"><span>#' . ($index + 1) . '</span><strong>' . App::e($move['label']) . '</strong><small>' . App::e($move['summary']) . '</small></button></form>';
        }
        $html .= '</div></section>';
        return $html;
    }

    /** @param array<string,int> $stats @param array<string,mixed> $settings */
    private static function renderRightPanel(array $history, array $metrics, array $stats, array $settings, string $mode, ?PartieSquadro $game): string
    {
        $lastMove = $game?->getLastMove();
        $html = '<aside class="side-panel"><section class="panel"><p class="eyebrow">Progression</p>';
        $html .= self::progressRow('Blanc', $metrics['whiteDone'], 'white');
        $html .= self::progressRow('Noir', $metrics['blackDone'], 'black');
        $html .= '</section>';
        $html .= self::renderStatsPanel($stats);
        $html .= self::renderSettingsPanel($settings, $mode);
        $html .= '<section class="panel history"><p class="eyebrow">Historique de bataille</p>';
        if ($lastMove) {
            $html .= '<div class="history-item pinned"><span>DB</span><p>' . App::e($lastMove) . '</p></div>';
        }
        if ($history === []) {
            $html .= '<p class="muted-text">Aucun coup enregistré dans cette session.</p>';
        } else {
            foreach ($history as $item) {
                $html .= '<div class="history-item"><span>' . App::e($item['at'] ?? '') . '</span><p>' . App::e($item['message'] ?? '') . '</p></div>';
            }
        }
        return $html . '</section></aside>';
    }

    /** @param array<string,int> $stats */
    private static function renderStatsPanel(array $stats): string
    {
        return '<section class="panel"><p class="eyebrow">Statistiques</p><div class="stat-grid compact"><div><strong>' . App::e((string) $stats['whiteMoves']) . '</strong><span>Coups blancs</span></div><div><strong>' . App::e((string) $stats['blackMoves']) . '</strong><span>Coups noirs</span></div><div><strong>' . App::e((string) $stats['finishes']) . '</strong><span>Sorties</span></div><div><strong>' . App::e((string) $stats['longestMove']) . '</strong><span>Plus long</span></div><div><strong>' . App::e((string) $stats['oracleMoves']) . '</strong><span>Oracle</span></div><div><strong>' . App::e((string) $stats['captures']) . '</strong><span>Reculs</span></div></div></section>';
    }

    /** @param array<string,mixed> $settings */
    private static function renderSettingsPanel(array $settings, string $mode): string
    {
        $instantSelected = $settings['moveFlow'] === 'instant' ? ' selected' : '';
        $confirmSelected = $settings['moveFlow'] === 'confirm' ? ' selected' : '';
        $assistChecked = !empty($settings['assist']) ? ' checked' : '';
        $coordChecked = !empty($settings['showCoordinates']) ? ' checked' : '';
        $cinematicChecked = !empty($settings['cinematic']) ? ' checked' : '';
        $botChecked = !empty($settings['bot']) && $mode !== 'online' ? ' checked' : '';
        $botDisabled = $mode === 'online' ? ' disabled' : '';

        return '<section class="panel settings-panel"><p class="eyebrow">Commandement</p><form action="traiteActionSquadro.php" method="post" class="settings-form"><input type="hidden" name="action" value="settings"><label><span>Déplacement</span><select name="moveFlow"><option value="instant"' . $instantSelected . '>Instantané</option><option value="confirm"' . $confirmSelected . '>Confirmation fixe</option></select></label><label class="check"><input type="checkbox" name="assist" value="1"' . $assistChecked . '> Assistance Oracle</label><label class="check"><input type="checkbox" name="showCoordinates" value="1"' . $coordChecked . '> Coordonnées</label><label class="check"><input type="checkbox" name="cinematic" value="1"' . $cinematicChecked . '> Effets arène</label><label class="check"><input type="checkbox" name="bot" value="1"' . $botChecked . $botDisabled . '> Oracle automatique</label><input type="hidden" name="botColor" value="noir"><button class="btn subtle full" type="submit">Appliquer</button></form></section>';
    }

    private static function progressRow(string $label, int $done, string $theme): string
    {
        $done = min(4, max(0, $done));
        $items = '';
        for ($i = 1; $i <= 4; $i++) {
            $items .= '<span class="progress-dot ' . ($i <= $done ? 'done' : '') . '"></span>';
        }
        return '<div class="progress-row ' . App::e($theme) . '"><div><strong>' . App::e($label) . '</strong><small>' . $done . '/4 sorties</small></div><div class="progress-dots">' . $items . '</div></div>';
    }

    private static function renderActionDock(string $state, ?array $selected, ?array $destination): string
    {
        if ($state !== 'ConfirmationPiece' || !$selected) {
            return '';
        }

        $destinationLabel = $destination ? '[' . $destination[0] . ',' . $destination[1] . ']' : 'inconnue';
        return '<section class="action-dock visible"><div><strong>Valider le coup ?</strong><p>[' . App::e($selected[0]) . ',' . App::e($selected[1]) . '] ira vers ' . App::e($destinationLabel) . '.</p></div><form action="traiteActionSquadro.php" method="post" class="actions-row"><button class="btn primary" name="choix" value="PRESEED">Confirmer</button><button class="btn ghost" name="choix" value="ABORT">Annuler</button></form></section>';
    }

    /** @param array<string,mixed> $settings @param ?array<string,mixed> $bestMove */
    private static function renderQuickCommandBar(string $state, string $activeColor, bool $allowMoves, array $settings, ?array $bestMove): string
    {
        if ($state === 'Victoire') {
            return '';
        }
        $flow = $settings['moveFlow'] === 'instant' ? 'Coup direct' : 'Validation fixe';
        $best = $bestMove ? '<button class="btn primary" name="move" value="' . App::e($bestMove['token']) . '">Meilleur coup</button>' : '<button class="btn primary" disabled>Meilleur coup</button>';
        return '<section class="quick-command-bar"><div><strong>' . App::e(ucfirst($activeColor)) . '</strong><span>' . App::e($allowMoves ? $flow : 'Attente adversaire') . '</span></div><form action="traiteActionSquadro.php" method="post" class="actions-row">' . $best . '<button class="btn ghost" name="oracle" value="1">Oracle</button><button class="btn ghost" name="undo" value="1">Annuler</button></form></section>';
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
        $settings = App::settings();
        $classes = ['cell', 'board-cell'];
        if ($x >= 1 && $x <= 5 && $y >= 1 && $y <= 5) {
            $classes[] = 'battlefield';
        } else {
            $classes[] = 'track';
        }
        if (!empty($settings['showCoordinates'])) {
            $classes[] = 'with-coords';
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
            $content = '<span class="temple-mark">◆</span>';
        } elseif ($piece->getCouleur() === PieceSquadro::BLANC || $piece->getCouleur() === PieceSquadro::NOIR) {
            $content = self::pieceButton($plateau, $piece, $x, $y, $activeColor, $allowMoves);
        }

        if (!empty($settings['showCoordinates'])) {
            $content .= '<span class="coord-label">' . $x . ',' . $y . '</span>';
        }

        return '<div class="' . implode(' ', $classes) . '" data-cell="' . $x . '-' . $y . '">' . $content . '</div>';
    }

    private static function pieceButton(PlateauSquadro $plateau, PieceSquadro $piece, int $x, int $y, string $activeColor, bool $allowMoves): string
    {
        $color = $piece->getCouleur() === PieceSquadro::BLANC ? 'blanc' : 'noir';
        $direction = self::directionLabel($piece->getDirection());
        $settings = App::settings();
        $move = SquadroAnalyzer::analyzeMove($plateau, $color, $x, $y);
        $destination = $move['destination'] ?? self::destinationOf($plateau, [$x, $y]);
        $destAttr = $destination ? ' data-destination="' . App::e($destination[0] . '-' . $destination[1]) . '"' : '';
        $enabled = $allowMoves && $color === $activeColor && $move !== null;
        $disabled = $enabled ? '' : ' disabled';
        $title = ucfirst($color) . ' ' . $direction . ' — position ' . $x . ',' . $y;
        $score = $move ? ' data-score="' . App::e($move['score']) . '" data-summary="' . App::e($move['summary']) . '"' : '';

        if ($settings['moveFlow'] === 'instant') {
            $name = 'move';
            $value = $color . ':' . $x . ':' . $y;
        } else {
            $name = $color;
            $value = 'btn' . $x . '-' . $y;
        }

        return '<button type="submit" class="piece-button piece-' . App::e($color) . ' dir-' . App::e(strtolower($direction)) . '" name="' . App::e($name) . '" value="' . App::e($value) . '" data-origin="' . $x . '-' . $y . '"' . $destAttr . $score . $disabled . ' title="' . App::e($title) . '"><span class="piece-icon"></span><span class="piece-meta">' . App::e($direction) . '</span></button>';
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
}
