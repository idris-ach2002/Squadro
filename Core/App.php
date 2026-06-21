<?php

declare(strict_types=1);

final class App
{
    public const SESSION_PLAYER = 'joueur';
    public const SESSION_BOARD = 'plateau';
    public const SESSION_STATE = 'etat';
    public const SESSION_TURN = 'couleur';
    public const SESSION_GAME_ID = 'partieId';
    public const SESSION_MODE = 'mode';
    public const SESSION_PLAYER_COLOR = 'playerColor';
    public const SESSION_HISTORY = 'history';
    public const SESSION_UNDO = 'undoStack';
    public const SESSION_SETTINGS = 'gameSettings';
    public const SESSION_STATS = 'gameStats';

    public static function boot(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        require_once __DIR__ . '/../.env.php';
        self::configureRuntime();
    }

    private static function configureRuntime(): void
    {
        ini_set('display_errors', getenv('APP_DEBUG') === '1' ? '1' : '0');
        error_reporting(E_ALL);

        set_exception_handler(static function (Throwable $exception): void {
            http_response_code(500);
            $debug = getenv('APP_DEBUG') === '1';
            $message = $debug ? $exception->getMessage() : 'Une erreur applicative est survenue.';
            echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Erreur Squadro</title><link rel="stylesheet" href="/assets/css/app.css"></head><body class="app-bg"><main class="shell single"><section class="panel critical"><p class="eyebrow">Erreur serveur</p><h1>Squadro ne peut pas terminer cette action.</h1><p>' . self::e($message) . '</p><a class="btn primary" href="/Vue/choixAction.php">Retour au menu</a></section></main></body></html>';
        });
    }

    public static function requirePlayer(): void
    {
        if (!isset($_SESSION[self::SESSION_PLAYER])) {
            self::redirect('/Vue/login.php');
        }
    }

    public static function redirect(string $location, int $status = 303): never
    {
        header('Location: ' . $location, true, $status);
        exit;
    }

    public static function e(string|int|null $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function flash(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    /** @return array<int,array{type:string,message:string}> */
    public static function consumeFlash(): array
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return is_array($flash) ? $flash : [];
    }

    public static function currentPlayer(): ?JoueurSquadro
    {
        $json = $_SESSION[self::SESSION_PLAYER] ?? null;
        if (!is_string($json) || $json === '') {
            return null;
        }
        try {
            return JoueurSquadro::fromJson($json);
        } catch (Throwable) {
            unset($_SESSION[self::SESSION_PLAYER]);
            return null;
        }
    }

    public static function initDatabase(): void
    {
        require_once __DIR__ . '/../env/db.php';
        require_once __DIR__ . '/../skel/PDOSquadro.skel.php';

        PDOSquadro::initPDO(
            (string) getenv('sgbd'),
            (string) getenv('host'),
            (string) getenv('database'),
            (string) getenv('user'),
            (string) getenv('password')
        );
    }

    public static function resetRuntimeGame(bool $keepIdentity = true): void
    {
        $player = $_SESSION[self::SESSION_PLAYER] ?? null;
        session_regenerate_id(true);

        if (!$keepIdentity) {
            $_SESSION = [];
            return;
        }

        $_SESSION = [];
        if ($player !== null) {
            $_SESSION[self::SESSION_PLAYER] = $player;
        }
    }

    public static function ensureBoard(): PlateauSquadro
    {
        if (!isset($_SESSION[self::SESSION_BOARD]) || !($_SESSION[self::SESSION_BOARD] instanceof PlateauSquadro)) {
            $_SESSION[self::SESSION_BOARD] = new PlateauSquadro();
        }

        return $_SESSION[self::SESSION_BOARD];
    }

    public static function pushUndo(PlateauSquadro $plateau, string $turn): void
    {
        $stack = $_SESSION[self::SESSION_UNDO] ?? [];
        if (!is_array($stack)) {
            $stack = [];
        }

        $stack[] = [
            'plateau' => $plateau->toJson(),
            'turn' => $turn,
            'at' => date(DATE_ATOM),
        ];

        $_SESSION[self::SESSION_UNDO] = array_slice($stack, -5);
    }

    public static function popUndo(): ?array
    {
        $stack = $_SESSION[self::SESSION_UNDO] ?? [];
        if (!is_array($stack) || $stack === []) {
            return null;
        }

        $state = array_pop($stack);
        $_SESSION[self::SESSION_UNDO] = $stack;
        return is_array($state) ? $state : null;
    }

    public static function addHistory(string $message): void
    {
        $history = $_SESSION[self::SESSION_HISTORY] ?? [];
        if (!is_array($history)) {
            $history = [];
        }

        array_unshift($history, [
            'message' => $message,
            'at' => date('H:i:s'),
        ]);

        $_SESSION[self::SESSION_HISTORY] = array_slice($history, 0, 24);
    }

    /** @return array<int,array{message:string,at:string}> */
    public static function history(): array
    {
        $history = $_SESSION[self::SESSION_HISTORY] ?? [];
        return is_array($history) ? $history : [];
    }


    /** @return array{moveFlow:string,assist:bool,bot:bool,botColor:string,showCoordinates:bool,cinematic:bool} */
    public static function settings(): array
    {
        $defaults = [
            'moveFlow' => 'instant',
            'assist' => true,
            'bot' => false,
            'botColor' => 'noir',
            'showCoordinates' => true,
            'cinematic' => true,
        ];

        $settings = $_SESSION[self::SESSION_SETTINGS] ?? [];
        if (!is_array($settings)) {
            $settings = [];
        }

        $merged = array_merge($defaults, $settings);
        $merged['moveFlow'] = $merged['moveFlow'] === 'confirm' ? 'confirm' : 'instant';
        $merged['botColor'] = $merged['botColor'] === 'blanc' ? 'blanc' : 'noir';
        $merged['assist'] = (bool) $merged['assist'];
        $merged['bot'] = (bool) $merged['bot'];
        $merged['showCoordinates'] = (bool) $merged['showCoordinates'];
        $merged['cinematic'] = (bool) $merged['cinematic'];

        $_SESSION[self::SESSION_SETTINGS] = $merged;
        return $merged;
    }

    /** @param array<string,mixed> $input */
    public static function updateSettings(array $input): void
    {
        $settings = self::settings();
        $settings['moveFlow'] = ($input['moveFlow'] ?? 'instant') === 'confirm' ? 'confirm' : 'instant';
        $settings['assist'] = isset($input['assist']);
        $settings['showCoordinates'] = isset($input['showCoordinates']);
        $settings['cinematic'] = isset($input['cinematic']);

        if (isset($input['bot'])) {
            $settings['bot'] = true;
            $settings['botColor'] = ($input['botColor'] ?? 'noir') === 'blanc' ? 'blanc' : 'noir';
        } else {
            $settings['bot'] = false;
        }

        $_SESSION[self::SESSION_SETTINGS] = $settings;
    }

    public static function enableBot(string $botColor = 'noir'): void
    {
        $settings = self::settings();
        $settings['bot'] = true;
        $settings['botColor'] = $botColor === 'blanc' ? 'blanc' : 'noir';
        $settings['moveFlow'] = 'instant';
        $_SESSION[self::SESSION_SETTINGS] = $settings;
    }

    public static function disableBot(): void
    {
        $settings = self::settings();
        $settings['bot'] = false;
        $_SESSION[self::SESSION_SETTINGS] = $settings;
    }

    /** @return array{moves:int,whiteMoves:int,blackMoves:int,captures:int,whiteCaptures:int,blackCaptures:int,finishes:int,oracleMoves:int,longestMove:int,startedAt:int} */
    public static function stats(): array
    {
        $defaults = [
            'moves' => 0,
            'whiteMoves' => 0,
            'blackMoves' => 0,
            'captures' => 0,
            'whiteCaptures' => 0,
            'blackCaptures' => 0,
            'finishes' => 0,
            'oracleMoves' => 0,
            'longestMove' => 0,
            'startedAt' => time(),
        ];

        $stats = $_SESSION[self::SESSION_STATS] ?? [];
        if (!is_array($stats)) {
            $stats = [];
        }

        $merged = array_merge($defaults, $stats);
        $_SESSION[self::SESSION_STATS] = $merged;
        return $merged;
    }

    public static function resetStats(): void
    {
        $_SESSION[self::SESSION_STATS] = [
            'moves' => 0,
            'whiteMoves' => 0,
            'blackMoves' => 0,
            'captures' => 0,
            'whiteCaptures' => 0,
            'blackCaptures' => 0,
            'finishes' => 0,
            'oracleMoves' => 0,
            'longestMove' => 0,
            'startedAt' => time(),
        ];
    }

    /** @param array<string,mixed> $move */
    public static function recordMove(array $move, bool $oracle = false): void
    {
        $stats = self::stats();
        $color = ($move['color'] ?? 'blanc') === 'noir' ? 'black' : 'white';
        $captures = (int) ($move['captures'] ?? 0);
        $distance = (int) ($move['distance'] ?? 0);

        $stats['moves']++;
        $stats[$color . 'Moves']++;
        $stats['captures'] += $captures;
        $stats[$color . 'Captures'] += $captures;
        $stats['longestMove'] = max((int) $stats['longestMove'], $distance);

        if (!empty($move['finish'])) {
            $stats['finishes']++;
        }
        if ($oracle) {
            $stats['oracleMoves']++;
        }

        $_SESSION[self::SESSION_STATS] = $stats;
    }

    public static function activeTurnLabel(): string
    {
        return ($_SESSION[self::SESSION_TURN] ?? 'blanc') === 'noir' ? 'noir' : 'blanc';
    }

    public static function oppositeColor(string $color): string
    {
        return $color === 'blanc' ? 'noir' : 'blanc';
    }
}
