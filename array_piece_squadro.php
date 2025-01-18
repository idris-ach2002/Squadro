<?php

class ArrayPieceSquadro implements ArrayAccess, Countable {
    // Attribut privé
    private array $pieces = [];

    // Implémentation de ArrayAccess
    public function offsetExists($offset): bool {
        return isset($this->pieces[$offset]);
    }

    public function offsetGet($offset): PieceSquadro {
        return $this->pieces[$offset];
    }

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

    public function offsetUnset($offset): void {
        unset($this->pieces[$offset]);
    }

    // Implémentation de Countable
    public function count(): int {
        return count($this->pieces);
    }

    // Méthode add
    public function add(PieceSquadro $piece): void {
        $this->pieces[] = $piece;
    }

    // Méthode remove
    public function remove(int $index): void {
        unset($this->pieces[$index]);
    }

    // Méthode __toString
    public function __toString(): string {
        return $this->toJson();
    }

    // Méthode toJson
    public function toJson(): string {
        $json = json_encode($this->pieces);

        if ($json === false) {
            throw new RuntimeException('Erreur lors de l\'encodage JSON : ' . json_last_error_msg());
        }

        return $json;
    }

    // Méthode fromJson
    public static function fromJson(string $json): ArrayPieceSquadro {
        $data = json_decode($json, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Erreur lors du décodage JSON : ' . json_last_error_msg());
        }

        $arrayPieceSquadro = new self();
        foreach ($data as $pieceData) {
            $arrayPieceSquadro->add(PieceSquadro::fromJson(json_encode($pieceData)));
        }

        return $arrayPieceSquadro;
    }
}
?>