<?php

require_once __DIR__ . '/../Modele/joueurSquadro.php';
require_once __DIR__ . '/../Modele/plateau_squadro.php';
require_once __DIR__ . '/../Modele/partieSquadro.php';

class PDOSquadro
{
    private static PDO $pdo;

    public static function initPDO(string $sgbd, string $host, string $db, string $user, string $password): void
    {
        switch ($sgbd) {
            case 'pgsql':
                self::$pdo = new PDO('pgsql:host=' . $host . ';dbname=' . $db, $user, $password);
                break;
            default:
                exit("Type de sgbd non correct : $sgbd fourni, 'pgsql' attendu");
        }

        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::initTable(__DIR__ . '/../SQL/squadro.sql');
        self::initPrepare();
    }

    private static function initTable(string $filePath): void
    {
        $sql = file_get_contents($filePath);
        self::$pdo->exec($sql);
    }

    private static function initPrepare(): void
    {
        self::$createPlayerSquadro = self::$pdo->prepare("INSERT INTO joueursquadro (joueurNom) VALUES (:name)");
        self::$selectPlayerByName = self::$pdo->prepare("SELECT id, joueurNom FROM joueursquadro WHERE joueurNom = :name");

        self::$createPartieSquadro = self::$pdo->prepare("INSERT INTO partiesquadro (playerOne, gameStatus, json) VALUES ((SELECT id FROM joueursquadro WHERE joueurNom = :playerName), :gameStatus, :json)");
        self::$savePartieSquadro = self::$pdo->prepare("UPDATE partiesquadro SET gameStatus = :gameStatus, json = :json WHERE partieId = :partieId");
        self::$addPlayerToPartieSquadro = self::$pdo->prepare("UPDATE partiesquadro SET playerTwo = (SELECT id FROM joueursquadro WHERE joueurNom = :playerName), json = :json WHERE partieId = :gameId");
        self::$selectPartieSquadroById = self::$pdo->prepare("SELECT * FROM partiesquadro WHERE partieId = :gameId");
        self::$selectAllPartieSquadro = self::$pdo->prepare("SELECT * FROM partiesquadro");
        self::$selectAllPartieSquadroByPlayerNameNonTerminees = self::$pdo->prepare("SELECT * FROM partiesquadro WHERE gameStatus != 'finished' AND (playerOne = (SELECT id FROM joueursquadro WHERE joueurNom = :playerName) OR playerTwo = (SELECT id FROM joueursquadro WHERE joueurNom = :playerName))");
        self::$selectAllPartieSquadroEnAttente = self::$pdo->prepare("SELECT * FROM partiesquadro WHERE gameStatus = 'waitingForPlayer' AND playerOne != :playerId");
    }

    private static PDOStatement $createPlayerSquadro;
    private static PDOStatement $selectPlayerByName;

    public static function createPlayer(string $name): JoueurSquadro
    {
        self::$createPlayerSquadro->execute(['name' => $name]);
        return self::getPlayerByName($name);
    }

    public static function getPlayerId(string $name): ?int
    {
        self::$selectPlayerByName->execute(['name' => $name]);
        $row = self::$selectPlayerByName->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    }

    public static function getPlayerByName(string $name): ?JoueurSquadro
    {
        self::$selectPlayerByName->execute(['name' => $name]);
        $row = self::$selectPlayerByName->fetch(PDO::FETCH_ASSOC);
        return $row ? new JoueurSquadro($row['joueurnom'], (int)$row['id']) : null;
    }


    public static function getPlayerById(int $id) : ?JoueurSquadro
    {
        $stmt = self::$pdo->prepare("SELECT * FROM joueursquadro WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new JoueurSquadro($row['joueurnom'], (int)$row['id']) : null;
    }

    private static PDOStatement $createPartieSquadro;
    private static PDOStatement $savePartieSquadro;
    private static PDOStatement $addPlayerToPartieSquadro;
    private static PDOStatement $selectPartieSquadroById;
    private static PDOStatement $selectAllPartieSquadro;
    private static PDOStatement $selectAllPartieSquadroByPlayerNameNonTerminees;
    private static PDOStatement $selectAllPartieSquadroEnAttente;

    public static function creerPartieSquadro(string $playerName, string $json): void
    {
        if (!isset(self::$createPartieSquadro)) {
            self::initPrepare();
        }

        
        $joueur = JoueurSquadro::fromJson($playerName);
        $playerName = $joueur->getNomJoueur();

        self::$createPartieSquadro->execute([
            'playerName' => $playerName,
            'gameStatus' => 'waitingForPlayer',
            'json' => $json
        ]);
    }

    public static function savePartieSquadro(string $gameStatus, string $json, int $partieId): void
    {
        self::$savePartieSquadro->execute([
            'gameStatus' => $gameStatus,
            'json' => $json,
            'partieId' => $partieId
        ]);
    }

    public static function addPlayerToPartieSquadro(string $playerName, string $json, int $gameId): void
    {
        if (json_decode($playerName, true) !== null) {
            $playerName = JoueurSquadro::fromJson($playerName)->getNomJoueur();
        }

        self::$addPlayerToPartieSquadro->execute([
            'playerName' => $playerName,
            'json' => $json,
            'gameId' => $gameId
        ]);
    }

    public static function getPartieSquadroById(int $gameId): ?PartieSquadro
    {
        self::$selectPartieSquadroById->execute(['gameId' => $gameId]);
        $row = self::$selectPartieSquadroById->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        $partie = new PartieSquadro(
            self::getPlayerById((int)$row['playerone'])
        );


        if (isset($row['playertwo'])) {
            $partie->addJoueur(self::getPlayerById((int)$row['playertwo']));
        }

        $partie->setPartieID((int)$row['partieid']);
        $partie->setPartieStatus($row['gamestatus']);
        $partie->setPlateau(PlateauSquadro::fromJson($row['json']));


        return $partie;
    }

    public static function getAllPartieSquadro(): array
    {
        self::$selectAllPartieSquadro->execute();
        return self::$selectAllPartieSquadro->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllPartieSquadroByPlayerNameNonTerminees(string $playerName): ?array
    {
        if (!isset(self::$selectAllPartieSquadroByPlayerNameNonTerminees)) {
            self::initPrepare();
        }


        $playerName = JoueurSquadro::fromJson($playerName)->getNomJoueur();
        self::$selectAllPartieSquadroByPlayerNameNonTerminees->execute(['playerName' => $playerName]);
        
        $res = self::$selectAllPartieSquadroByPlayerNameNonTerminees->fetchAll(PDO::FETCH_ASSOC);


        $array_tab = array();


        foreach ($res as $row) {
            array_push($array_tab, self::getPartieSquadroById($row["partieid"])->toJson());
        }

        return $array_tab;
    }


    public static function getAllPartieSquadroEnAttente (string $player) : ?array
    {
        if (!isset(self::$selectAllPartieSquadroEnAttente)) {
            self::initPrepare();
        }


        $playerId = JoueurSquadro::fromJson($player)->getId();
        self::$selectAllPartieSquadroEnAttente->execute(['playerId' => $playerId]);
        
        $res = self::$selectAllPartieSquadroEnAttente->fetchAll(PDO::FETCH_ASSOC);


        $array_tab = array();


        foreach ($res as $row) {
            array_push($array_tab, self::getPartieSquadroById($row["partieid"])->toJson());
        }

        return $array_tab;
    }




    public static function getLastGameIdForPlayer(string $playerName): int
    {
        $stmt = self::$pdo->prepare("SELECT partieId FROM partiesquadro WHERE playerOne = (SELECT id FROM joueursquadro WHERE joueurNom = :playerName) ORDER BY partieId DESC LIMIT 1");
        $stmt->execute(['playerName' => $playerName]);
        return (int)$stmt->fetchColumn() ?: 0;
    }
}

function test(): void
{
    PDOSquadro::initPDO('pgsql', 'localhost', 'squadro_db', 'squadro_user', 'password');
    $player = PDOSquadro::createPlayer('yjk');
    var_dump($player);

    PDOSquadro::createPartieSquadro('yjk', '{"state":"waiting"}');
    $gameId = PDOSquadro::getLastGameIdForPlayer('yjk');
    PDOSquadro::savePartieSquadro('initialized', '{"state":"ready"}', $gameId);
    PDOSquadro::addPlayerToPartieSquadro('ToTo', '{"state":"started"}', $gameId);
    $game = PDOSquadro::getPartieSquadroById($gameId);
    var_dump($game);

    print_r(PDOSquadro::getAllPartieSquadro());
    print_r(PDOSquadro::getAllPartieSquadroByPlayerName('yjk'));
}

// test();