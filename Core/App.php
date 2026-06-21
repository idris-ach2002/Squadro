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

        $_SESSION[self::SESSION_HISTORY] = array_slice($history, 0, 12);
    }

    /** @return array<int,array{message:string,at:string}> */
    public static function history(): array
    {
        $history = $_SESSION[self::SESSION_HISTORY] ?? [];
        return is_array($history) ? $history : [];
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
