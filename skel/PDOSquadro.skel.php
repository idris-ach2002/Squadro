<?php

declare(strict_types=1);

require_once __DIR__ . '/../Modele/joueurSquadro.php';
require_once __DIR__ . '/../Modele/plateau_squadro.php';
require_once __DIR__ . '/../Modele/partieSquadro.php';

final class PDOSquadro
{
    private static ?PDO $pdo = null;

    private static PDOStatement $createPlayerSquadro;
    private static PDOStatement $selectPlayerByName;
    private static PDOStatement $selectPlayerById;
    private static PDOStatement $createPartieSquadro;
    private static PDOStatement $savePartieSquadro;
    private static PDOStatement $addPlayerToPartieSquadro;
    private static PDOStatement $selectPartieSquadroById;
    private static PDOStatement $selectAllPartieSquadro;
    private static PDOStatement $selectAllPartieSquadroByPlayerNameNonTerminees;
    private static PDOStatement $selectAllPartieSquadroEnAttente;
    private static PDOStatement $selectDashboardStats;

    public static function initPDO(string $sgbd, string $host, string $db, string $user, string $password): void
    {
        if (self::$pdo instanceof PDO) {
            return;
        }

        if ($sgbd !== 'pgsql') {
            throw new InvalidArgumentException("Type de SGBD non supporté : $sgbd. Le projet Docker utilise PostgreSQL.");
        }

        self::$pdo = new PDO(
            'pgsql:host=' . $host . ';dbname=' . $db,
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        self::initTable(__DIR__ . '/../SQL/squadro.sql');
        self::initPrepare();
    }

    private static function pdo(): PDO
    {
        if (!self::$pdo instanceof PDO) {
            throw new RuntimeException('PDO non initialisé. App::initDatabase() doit être appelé avant toute requête.');
        }

        return self::$pdo;
    }

    private static function initTable(string $filePath): void
    {
        $sql = file_get_contents($filePath);
        if ($sql === false) {
            throw new RuntimeException('Impossible de lire le fichier SQL : ' . $filePath);
        }
        self::pdo()->exec($sql);
    }

    private static function initPrepare(): void
    {
        $pdo = self::pdo();

        self::$createPlayerSquadro = $pdo->prepare(
            'INSERT INTO joueursquadro (joueurNom) VALUES (:name)
             ON CONFLICT (joueurNom) DO UPDATE SET joueurNom = EXCLUDED.joueurNom
             RETURNING id, joueurNom'
        );
        self::$selectPlayerByName = $pdo->prepare('SELECT id, joueurNom FROM joueursquadro WHERE joueurNom = :name');
        self::$selectPlayerById = $pdo->prepare('SELECT id, joueurNom FROM joueursquadro WHERE id = :id');

        self::$createPartieSquadro = $pdo->prepare(
            "INSERT INTO partiesquadro (playerOne, gameStatus, json, currentTurn, moveCount, createdAt, updatedAt)
             VALUES ((SELECT id FROM joueursquadro WHERE joueurNom = :playerName), :gameStatus, :json, :currentTurn, 0, NOW(), NOW())
             RETURNING partieId"
        );

        self::$savePartieSquadro = $pdo->prepare(
            'UPDATE partiesquadro
             SET gameStatus = :gameStatus,
                 json = :json,
                 currentTurn = :currentTurn,
                 winner = :winner,
                 lastMove = :lastMove,
                 moveCount = :moveCount,
                 updatedAt = NOW()
             WHERE partieId = :partieId'
        );

        self::$addPlayerToPartieSquadro = $pdo->prepare(
            "UPDATE partiesquadro
             SET playerTwo = (SELECT id FROM joueursquadro WHERE joueurNom = :playerName),
                 gameStatus = 'active',
                 updatedAt = NOW()
             WHERE partieId = :gameId
               AND playerOne <> (SELECT id FROM joueursquadro WHERE joueurNom = :playerName)"
        );

        self::$selectPartieSquadroById = $pdo->prepare('SELECT * FROM partiesquadro WHERE partieId = :gameId');
        self::$selectAllPartieSquadro = $pdo->prepare('SELECT * FROM partiesquadro ORDER BY updatedAt DESC, partieId DESC');
        self::$selectAllPartieSquadroByPlayerNameNonTerminees = $pdo->prepare(
            "SELECT p.*
             FROM partiesquadro p
             WHERE p.gameStatus != 'finished'
               AND (p.playerOne = (SELECT id FROM joueursquadro WHERE joueurNom = :playerName)
                    OR p.playerTwo = (SELECT id FROM joueursquadro WHERE joueurNom = :playerName))
             ORDER BY p.updatedAt DESC, p.partieId DESC"
        );
        self::$selectAllPartieSquadroEnAttente = $pdo->prepare(
            "SELECT * FROM partiesquadro
             WHERE gameStatus = 'waitingForPlayer'
               AND playerOne != :playerId
             ORDER BY createdAt DESC, partieId DESC"
        );
        self::$selectDashboardStats = $pdo->prepare(
            "SELECT
                COUNT(*) FILTER (WHERE gameStatus = 'waitingForPlayer') AS waiting,
                COUNT(*) FILTER (WHERE gameStatus = 'active') AS active,
                COUNT(*) FILTER (WHERE gameStatus = 'finished') AS finished
             FROM partiesquadro"
        );
    }

    public static function createPlayer(string $name): JoueurSquadro
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Le nom du joueur ne peut pas être vide.');
        }

        self::$createPlayerSquadro->execute(['name' => $name]);
        $row = self::$createPlayerSquadro->fetch();
        if (!$row) {
            throw new RuntimeException('Création du joueur impossible.');
        }

        return self::mapPlayer($row);
    }

    public static function getPlayerId(string $name): ?int
    {
        $player = self::getPlayerByName($name);
        return $player?->getId();
    }

    public static function getPlayerByName(string $name): ?JoueurSquadro
    {
        self::$selectPlayerByName->execute(['name' => trim($name)]);
        $row = self::$selectPlayerByName->fetch();
        return $row ? self::mapPlayer($row) : null;
    }

    public static function getPlayerById(int $id): ?JoueurSquadro
    {
        self::$selectPlayerById->execute(['id' => $id]);
        $row = self::$selectPlayerById->fetch();
        return $row ? self::mapPlayer($row) : null;
    }

    public static function creerPartieSquadro(string $playerJsonOrName, string $json, string $status = 'waitingForPlayer'): int
    {
        $playerName = self::normalizePlayerName($playerJsonOrName);
        self::$createPartieSquadro->execute([
            'playerName' => $playerName,
            'gameStatus' => $status,
            'json' => $json,
            'currentTurn' => 'blanc',
        ]);

        $id = self::$createPartieSquadro->fetchColumn();
        if ($id === false) {
            throw new RuntimeException('Impossible de créer la partie.');
        }

        return (int) $id;
    }

    public static function savePartieSquadro(
        string $gameStatus,
        string $json,
        int $partieId,
        string $currentTurn = 'blanc',
        ?string $winner = null,
        ?string $lastMove = null,
        ?int $moveCount = null
    ): void {
        $current = self::getPartieSquadroById($partieId);
        $moveCount ??= $current ? $current->getMoveCount() : 0;

        self::$savePartieSquadro->execute([
            'gameStatus' => $gameStatus,
            'json' => $json,
            'currentTurn' => $currentTurn,
            'winner' => $winner,
            'lastMove' => $lastMove,
            'moveCount' => $moveCount,
            'partieId' => $partieId,
        ]);
    }

    public static function addPlayerToPartieSquadro(string $playerJsonOrName, int $gameId): void
    {
        self::$addPlayerToPartieSquadro->execute([
            'playerName' => self::normalizePlayerName($playerJsonOrName),
            'gameId' => $gameId,
        ]);
    }

    public static function getPartieSquadroById(int $gameId): ?PartieSquadro
    {
        self::$selectPartieSquadroById->execute(['gameId' => $gameId]);
        $row = self::$selectPartieSquadroById->fetch();
        return $row ? self::mapGame($row) : null;
    }

    /** @return array<int,array<string,mixed>> */
    public static function getAllPartieSquadro(): array
    {
        self::$selectAllPartieSquadro->execute();
        return self::$selectAllPartieSquadro->fetchAll();
    }

    /** @return array<int,string> */
    public static function getAllPartieSquadroByPlayerNameNonTerminees(string $playerJsonOrName): array
    {
        self::$selectAllPartieSquadroByPlayerNameNonTerminees->execute([
            'playerName' => self::normalizePlayerName($playerJsonOrName),
        ]);

        return array_map(
            static fn(array $row): string => self::mapGame($row)->toJson(),
            self::$selectAllPartieSquadroByPlayerNameNonTerminees->fetchAll()
        );
    }

    /** @return array<int,string> */
    public static function getAllPartieSquadroEnAttente(string $playerJsonOrName): array
    {
        $playerId = self::normalizePlayer($playerJsonOrName)->getId();
        self::$selectAllPartieSquadroEnAttente->execute(['playerId' => $playerId]);

        return array_map(
            static fn(array $row): string => self::mapGame($row)->toJson(),
            self::$selectAllPartieSquadroEnAttente->fetchAll()
        );
    }

    public static function getLastGameIdForPlayer(string $playerJsonOrName): int
    {
        $stmt = self::pdo()->prepare(
            'SELECT partieId FROM partiesquadro
             WHERE playerOne = (SELECT id FROM joueursquadro WHERE joueurNom = :playerName)
             ORDER BY partieId DESC LIMIT 1'
        );
        $stmt->execute(['playerName' => self::normalizePlayerName($playerJsonOrName)]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    /** @return array{waiting:int,active:int,finished:int} */
    public static function getDashboardStats(): array
    {
        self::$selectDashboardStats->execute();
        $row = self::$selectDashboardStats->fetch() ?: [];
        return [
            'waiting' => (int) ($row['waiting'] ?? 0),
            'active' => (int) ($row['active'] ?? 0),
            'finished' => (int) ($row['finished'] ?? 0),
        ];
    }

    private static function mapPlayer(array $row): JoueurSquadro
    {
        return new JoueurSquadro((string) ($row['joueurnom'] ?? $row['joueurNom']), (int) $row['id']);
    }

    private static function mapGame(array $row): PartieSquadro
    {
        $playerOne = self::getPlayerById((int) $row['playerone']);
        if (!$playerOne instanceof JoueurSquadro) {
            throw new RuntimeException('Partie corrompue : playerOne introuvable.');
        }

        $partie = new PartieSquadro($playerOne);

        if (!empty($row['playertwo'])) {
            $playerTwo = self::getPlayerById((int) $row['playertwo']);
            if ($playerTwo instanceof JoueurSquadro) {
                $partie->addJoueur($playerTwo);
            }
        }

        $partie->setPartieID((int) $row['partieid']);
        $partie->setPartieStatus((string) $row['gamestatus']);
        $partie->setPlateau(PlateauSquadro::fromJson((string) $row['json']));
        $partie->setCurrentTurn((string) ($row['currentturn'] ?? 'blanc'));
        $partie->setWinner($row['winner'] !== null ? (string) $row['winner'] : null);
        $partie->setLastMove($row['lastmove'] !== null ? (string) $row['lastmove'] : null);
        $partie->setMoveCount((int) ($row['movecount'] ?? 0));
        $partie->setCreatedAt(isset($row['createdat']) ? (string) $row['createdat'] : null);
        $partie->setUpdatedAt(isset($row['updatedat']) ? (string) $row['updatedat'] : null);

        return $partie;
    }

    private static function normalizePlayerName(string $playerJsonOrName): string
    {
        return self::normalizePlayer($playerJsonOrName)->getNomJoueur();
    }

    private static function normalizePlayer(string $playerJsonOrName): JoueurSquadro
    {
        $decoded = json_decode($playerJsonOrName, true);
        if (is_array($decoded) && isset($decoded['nomJoueur'], $decoded['id'])) {
            return JoueurSquadro::fromJson($playerJsonOrName);
        }

        $player = self::getPlayerByName($playerJsonOrName);
        if (!$player instanceof JoueurSquadro) {
            throw new RuntimeException('Joueur introuvable : ' . $playerJsonOrName);
        }

        return $player;
    }
}
