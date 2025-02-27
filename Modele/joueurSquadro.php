<?php

class JoueurSquadro {
    private string $nomJoueur;
    private int $id;

    // Constructeur
    public function __construct(string $nomJoueur, int $id) {
        $this->nomJoueur = $nomJoueur;
        $this->id = $id;
    }

    // Getter et Setter du nom du joueur
    public function getNomJoueur(): string {
        return $this->nomJoueur;
    }

    public function setNomJoueur(string $nom): void {
        $this->nomJoueur = $nom;
    }

    // Getter et Setter de l'ID
    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    // Convertir l'objet en JSON
    public function toJson(): string {
        return json_encode([
            'nomJoueur' => $this->nomJoueur,
            'id' => $this->id
        ]);
    }

    // Créer un objet à partir du JSON
    public static function fromJson(string $json): JoueurSquadro {
        $data = json_decode($json, true);
        return new JoueurSquadro($data['nomJoueur'], $data['id']);
    }
}
