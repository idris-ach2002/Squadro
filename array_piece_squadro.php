<?php
require_once('piece_squadro.php');
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
        $piecesJson = [];
        foreach ($this->pieces as $key => $piece) {
            $piecesJson[$key] = json_decode($piece->toJson(), true); // Convertir chaque pièce en tableau
        }
        return json_encode($piecesJson);
    }
    

    // Méthode fromJson
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

    // Initialisation des tests
    echo "=== Tests pour ArrayPieceSquadro ===\n";

    // 1. Création d'une instance de ArrayPieceSquadro
    $tableauPiece = new ArrayPieceSquadro();
    echo "Instance créée.\n";

    // 2. Ajout de pièces dans le tableau
    echo "Ajout de pièces dans ArrayPieceSquadro...\n";
    $tableauPiece->add(PieceSquadro::initBlancOuest());
    $tableauPiece->add(PieceSquadro::initNoirNord());
    $tableauPiece->add(PieceSquadro::initVide());
    echo "Nombre de pièces après ajout : " . count($tableauPiece) . "\n";

    // 3. Vérification de l'accès aux éléments
    echo "Accès aux éléments :\n";
    for ($i = 0; $i < count($tableauPiece); $i++) {
        echo $tableauPiece[$i] . "\n";
    }

    // 4. Modification d'une pièce
    echo "Modification d'une pièce (indice 1)...\n";
    $tableauPiece[1] = PieceSquadro::initBlancEst();
    echo "Nouvelle valeur : " . $tableauPiece[1] . "\n";

    // 5. Suppression d'une pièce
    echo "Suppression d'une pièce (indice 0)...\n";
    unset($tableauPiece[0]);
    echo "Nombre de pièces après suppression : " . count($tableauPiece) . "\n";

    // 6. Conversion en JSON
    echo "Conversion du tableau en JSON :\n";
    $json = $tableauPiece->toJson();
    echo $json . "\n";

    // 7. Création depuis un JSON
    echo "Création d'une nouvelle instance depuis le JSON :\n";
    $newArrayPieceSquadro = ArrayPieceSquadro::fromJson($json);
    echo "Nombre de pièces dans la nouvelle instance : " . count($newArrayPieceSquadro) . "\n";

    // Vérification des éléments recréés
    foreach ($newArrayPieceSquadro as $piece) {
        echo $piece . "\n";
    }

    // 8. Test de l'exception pour valeur non valide
    echo "Test d'ajout d'une valeur non valide...\n";
    try {
        $tableauPiece->add("Valeur non valide");
    } catch (InvalidArgumentException $e) {
        echo "Exception attrapée : " . $e->getMessage() . "\n";
    }

    // 9. Test de l'accès à un index non défini
    echo "Test d'accès à un index non défini...\n";
    if (!isset($tableauPiece[10])) {
        echo "Index 10 n'existe pas.\n";
    }

    echo "=== Fin des tests ===\n";
?>