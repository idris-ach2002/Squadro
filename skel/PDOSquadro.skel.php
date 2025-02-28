<?php
namespace Squadro;


use PDO;
use PDOException;
use PDOStatement;



class PDOSquadro
{
    private static PDO $pdo;

    public static function initPDO(string $sgbd, string $host, string $db, string $user, string $password): void
    {
        switch ($sgbd) {
/*            case 'mysql':
                TODO si nécessaire
                break;
                */
            case 'pgsql':
                self::$pdo = new PDO('pgsql:host=' . $host . ' dbname=' . $db . ' user=' . $user . ' password=' . $password);
                break;
            default:
                exit ("Type de sgbd non correct : $sgbd fourni, 'pgsql' attendu");
        }

        // pour récupérer aussi les exceptions provenant de PDOStatement
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Initialiser les tables
        self::initTable('../SQL/squadro.sql');

        self::initPrepare();
    }


    private static function initTable (string $filePath) : void
    {
        $sql = file_get_contents($filePath);
        self::$pdo->exec($sql);
    }


    private static function initPrepare () : void
    {
        self::$createPlayerSquadro = self::$pdo->prepare("INSERT INTO joueursquadro (joueurNom) VALUES (:name)");
        self::$selectPlayerByName = self::$pdo->prepare("SELECT id, joueurNom FROM joueursquadro WHERE joueurNom = :name");

        self::$createPartieSquadro = self::$pdo->prepare("INSERT INTO partiesquadro (playerOne, json) VALUES ((SELECT id FROM joueursquadro WHERE joueurNom = :playerName), :json)");
        self::$savePartieSquadro = self::$pdo->prepare("UPDATE partiesquadro SET gameStatus = :gameStatus, json = :json WHERE partieId = :partieId");
        self::$addPlayerToPartieSquadro = self::$pdo->prepare("UPDATE partiesquadro SET playerTwo = (SELECT id FROM joueursquadro WHERE joueurNom = :playerName), json = :json WHERE partieId = :gameId");
        self::$selectPartieSquadroById = self::$pdo->prepare("SELECT * FROM partiesquadro WHERE partieId = :gameId");
        self::$selectAllPartieSquadro = self::$pdo->prepare("SELECT * FROM partiesquadro");
        self::$selectAllPartieSquadroByPlayerName = self::$pdo->prepare("SELECT * FROM partiesquadro WHERE playerOne = (SELECT id FROM joueursquadro WHERE joueurNom = :playerName) OR playerTwo = (SELECT id FROM joueursquadro WHERE joueurNom = :playerName)");
    }

    /* requêtes Préparées pour l'entitePlayerSquadro */
    private static PDOStatement $createPlayerSquadro;
    private static PDOStatement $selectPlayerByName;

    /******** Gestion des requêtes relatives à JoueurSquadro *************/
    public static function createPlayer(string $name): void //JoueurSquadro
    {
        self::$createPlayerSquadro->execute(['name' => $name]);
        //return new JoueurSquadro($name, 0);
    }

    public static function selectPlayerByName(string $name): ?JoueurSquadro
    {
        self::$selectPlayerByName->execute(['name' => $name]);
        $row = self::$selectPlayerByName->fetch(PDO::FETCH_ASSOC);
        print_r($row);
        //return $row ? new JoueurSquadro($row['joueurNom'], 0) : null;
	return null;
    }

    /* requêtes préparées pour l'entite PartieSquadro */
    private static PDOStatement $createPartieSquadro; // INSERT INTO XXXXX VALUES (?,?,?)
    private static PDOStatement $savePartieSquadro;
    private static PDOStatement $addPlayerToPartieSquadro;
    private static PDOStatement $selectPartieSquadroById;
    private static PDOStatement $selectAllPartieSquadro;
    private static PDOStatement $selectAllPartieSquadroByPlayerName;

    /******** Gestion des requêtes relatives à PartieSquadro *************/

    /**
     * initialisation et execution de $createPartieSquadro la requête préparée pour enregistrer une nouvelle partie
     */
    public static function createPartieSquadro(string $playerName, string $json): void
    {
        self::$createPartieSquadro->execute(['playerName' => $playerName, 'json' => $json]);
    }



    /**
     * initialisation et execution de $savePartieSquadro la requête préparée pour changer
     * l'état de la partie et sa représentation json
     */
    public static function savePartieSquadro(string $gameStatus, string $json, int $partieId): void
    {
        self::$savePartieSquadro->execute(['gameStatus' => $gameStatus, 'json' => $json, 'partieId' => $partieId]);
    }



    /**
     * initialisation et execution de $addPlayerToPartieSquadro la requête préparée pour intégrer le second joueur
     */
    public static function addPlayerToPartieSquadro(string $playerName, string $json, int $gameId): void
    {
        self::$addPlayerToPartieSquadro->execute(['playerName' => $playerName, 'json' => $json, 'gameId' => $gameId]);
    }


    /**
     * initialisation et execution de $selectPartieSquadroById la requête préparée pour récupérer
     * une instance de PartieSquadro en fonction de son identifiant
     */
    public static function getPartieSquadroById(int $gameId): void //?PartieSquadro
    {
        self::$selectPartieSquadroById->execute(['gameId' => $gameId]);
        $row = self::$selectPartieSquadroById->fetch(PDO::FETCH_ASSOC);

        print_r($row);

        //return $row ? new PartieSquadro($row['partieId'], $row['playerOne'], $row['playerTwo'], $row['gameStatus'], $row['json']) : null;
    }


    /**
     * initialisation et execution de $selectAllPartieSquadro la requête préparée pour récupérer toutes
     * les instances de PartieSquadro
     */
    public static function getAllPartieSquadro(): array
    {
        self::$selectAllPartieSquadro->execute();
        return self::$selectAllPartieSquadro->fetchAll(PDO::FETCH_ASSOC);
    }

    

    /**
     * initialisation et execution de $selectAllPartieSquadroByPlayerName la requête préparée pour récupérer les instances
     * de PartieSquadro accessibles au joueur $playerName
     * ne pas oublier les parties "à un seul joueur"
     */
    public static function getAllPartieSquadroByPlayerName(string $playerName): array
    {
        self::$selectAllPartieSquadroByPlayerName->execute(['playerName' => $playerName]);
        return self::$selectAllPartieSquadroByPlayerName->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * initialisation et execution de la requête préparée pour récupérer
     * l'identifiant de la dernière partie ouverte par $playername
     */
    public static function getLastGameIdForPlayer(string $playerName): int
    {
        $stmt = self::$pdo->prepare("SELECT partieId FROM partiesquadro WHERE playerOne = (SELECT id FROM joueursquadro WHERE joueurNom = :playerName) ORDER BY partieId DESC LIMIT 1");
        $stmt->execute(['playerName' => $playerName]);
        return $stmt->fetchColumn() ?: 0;
    }


}


function test () : void
{
    // ----------------------------------------- TESTS
    PDOSquadro::initPDO('pgsql', 'localhost', 'squadro_db', 'squadro_user', 'password');
    PDOSquadro::selectPlayerByName('yjk');

    PDOSquadro::createPartieSquadro('yjk', 'json');
    PDOSquadro::savePartieSquadro('initialized', 'json', 1);
    PDOSquadro::addPlayerToPartieSquadro('ToTo', 'json', 1);
    PDOSquadro::getPartieSquadroById(1);
    print_r(PDOSquadro::getAllPartieSquadro());
    print_r(PDOSquadro::getAllPartieSquadroByPlayerName('yjk'));
    print(PDOSquadro::getLastGameIdForPlayer('yjk'));
    // ----------------------------------------- TESTS
}


// test();