<?php
require_once('piece_squadro.php');
/**
 * Classe représentant un tableau de pièces Squadro avec une implémentation de ArrayAccess et Countable.
 * Permet de gérer les pièces comme un tableau tout en validant les types et en fournissant des méthodes utiles.
 */
class ArrayPieceSquadro implements ArrayAccess, Countable {
    /**
     * @var array Liste des pièces Squadro.
     */
    private array $pieces = [];

    /**
     * Vérifie si une pièce existe à un indice donné.
     *
     * @param mixed $offset L'indice de l'élément à vérifier.
     * @return bool True si la pièce existe, false sinon.
     */
    public function offsetExists($offset): bool {
        return isset($this->pieces[$offset]);
    }

    /**
     * Récupère une pièce à un indice donné.
     *
     * @param mixed $offset L'indice de la pièce à récupérer.
     * @return PieceSquadro La pièce à l'indice donné.
     * @throws \OutOfBoundsException Si l'indice n'existe pas.
     */
    public function offsetGet($offset): PieceSquadro {
        return $this->pieces[$offset];
    }

    /**
     * Définit une pièce à un indice donné.
     *
     * @param mixed $offset L'indice où placer la pièce.
     * @param mixed $value La pièce à ajouter.
     * @return void
     * @throws InvalidArgumentException Si la valeur n'est pas une instance de PieceSquadro.
     */
    public function offsetSet($offset, $value): void {
        if (!$value instanceof PieceSquadro) {
            throw new InvalidArgumentException('La valeur doit être une instance de PieceSquadro');
        }
        if ($offset === null) {
            $this->pieces[] = $value;
        } else {
            $this->pieces[$offset] = $value;
        }
    }

    /**
     * Supprime une pièce à un indice donné.
     *
     * @param mixed $offset L'indice de la pièce à supprimer.
     * @return void
     */
    public function offsetUnset($offset): void {
        unset($this->pieces[$offset]);
    }

    /**
     * Compte le nombre de pièces dans le tableau.
     *
     * @return int Le nombre de pièces.
     */
    public function count(): int {
        return count($this->pieces);
    }

    /**
     * Ajoute une pièce à la fin du tableau.
     *
     * @param PieceSquadro $piece La pièce à ajouter.
     * @return void
     */
    public function add(PieceSquadro $piece): void {
        $this->pieces[] = $piece;
    }

    /**
     * Supprime une pièce à un indice donné.
     *
     * @param int $index L'indice de la pièce à supprimer.
     * @return void
     */
    public function remove(int $index): void {
        unset($this->pieces[$index]);
    }

    /**
     * Retourne la représentation JSON de l'objet.
     *
     * @return string La chaîne JSON représentant les pièces.
     */
    public function __toString(): string {
        return $this->toJson();
    }

    /**
     * Convertit l'objet en une chaîne JSON.
     *
     * @return string La représentation JSON des pièces.
     * @throws \RuntimeException Si une erreur survient lors de l'encodage JSON.
     */
    public function toJson(): string {
        $piecesJson = [];
        foreach ($this->pieces as $key => $piece) {
            $piecesJson[$key] = json_decode($piece->toJson(), true); // Convertir chaque pièce en tableau
        }
        return json_encode($piecesJson);
    }

    /**
     * Reconstruit un objet ArrayPieceSquadro à partir d'une chaîne JSON.
     *
     * @param string $json La chaîne JSON représentant les pièces.
     * @return ArrayPieceSquadro L'instance de ArrayPieceSquadro reconstruite.
     * @throws \InvalidArgumentException Si le JSON est invalide ou s'il y a une erreur de décodage.
     */
    public static function fromJson(string $json): ArrayPieceSquadro {
        $data = json_decode($json, true);
        if ($data === null) {
            throw new InvalidArgumentException("JSON invalide pour ArrayPieceSquadro");
        }
    
        $arrayPieceSquadro = new self();
        foreach ($data as $key => $pieceData) {
            // Ré-encode $pieceData en JSON avant de l'envoyer à PieceSquadro::fromJson
            $arrayPieceSquadro->add(PieceSquadro::fromJson(json_encode($pieceData)));
        }
        return $arrayPieceSquadro;
    }
}
