<?php

class PartieSquadro {
    private const PLAYER_ONE = 0;
    private const PLAYER_TWO = 1;

    private ?int $partieId = 0;
    private array $joueurs = [];
    private int $joueurActif;
    private string $gameStatus = 'initialized';
    private PlateauSquadro $plateau;

    // Constructeur avec le premier joueur
    public function __construct(JoueurSquadro $playerOne) {
        $this->joueurs[self::PLAYER_ONE] = $playerOne;
        $this->joueurActif = self::PLAYER_ONE;
        $this->plateau = new PlateauSquadro();
    }

    // Ajouter un second joueur
    public function addJoueur(JoueurSquadro $player): void {
        if (count($this->joueurs) < 2) {
            $this->joueurs[self::PLAYER_TWO] = $player;
        }
    }

    // Récupérer le joueur actif
    public function getJoueurActif(): JoueurSquadro {
        return $this->joueurs[$this->joueurActif];
    }

    // Obtenir le nom du joueur actif
    public function getNomJoueurActif(): string {
        return $this->joueurs[$this->joueurActif]->getNomJoueur();
    }

    // Convertir l'objet en chaîne
    public function __toString(): string {
        return "Partie ID: {$this->partieId}, Joueur Actif: " . $this->getNomJoueurActif();
    }

    // Getter et Setter de l'ID de partie
    public function getPartieID(): int {
        return $this->partieId;
    }

    public function setPartieID(int $id): void {
        $this->partieId = $id;
    }


    public function getGameStatus(): string {
        return $this->gameStatus;
    }

    public function setPartieStatus(string $status): void {
        $this->gameStatus = $status;
    }

    // Récupérer tous les joueurs
    public function getJoueurs(): array {
        return $this->joueurs;
    }

    public function setPlateau (PlateauSquadro $plateau): void {
        $this->plateau = $plateau;
    }

    public function getPlateau(): PlateauSquadro {
        return $this->plateau;
    }

    // Convertir l'objet en JSON
    public function toJson(): string {
        return json_encode([
            'partieId' => $this->partieId,
            'joueurs' => array_map(fn($joueur) => json_decode($joueur->toJson(), true), $this->joueurs),
            'joueurActif' => $this->joueurActif,
            'gameStatus' => $this->gameStatus,
        ]);
    }

    // Recréer un objet PartieSquadro depuis du JSON
    public static function fromJson(string $json): PartieSquadro {
        $data = json_decode($json, true);
        $partie = new PartieSquadro(JoueurSquadro::fromJson(json_encode($data['joueurs'][self::PLAYER_ONE])));
        
        if (isset($data['joueurs'][self::PLAYER_TWO])) {
            $partie->addJoueur(JoueurSquadro::fromJson(json_encode($data['joueurs'][self::PLAYER_TWO])));
        }

        $partie->setPartieID($data['partieId']);
        $partie->joueurActif = $data['joueurActif'];
        $partie->gameStatus = $data['gameStatus'];

        return $partie;
    }
}
