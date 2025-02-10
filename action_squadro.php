<?php
require_once 'piece_squadro.php';
require_once 'plateau_squadro.php';

/**
 * Classe ActionSquadro
 * 
 * Gère les actions du jeu Squadro, notamment les déplacements des pièces et les conditions de victoire.
 */
class ActionSquadro
{
    /** @var PlateauSquadro Plateau sur lequel les actions sont effectuées */
    private PlateauSquadro $plateau;

    /**
     * Constructeur de la classe ActionSquadro.
     * 
     * @param PlateauSquadro $plateau Instance du plateau de jeu.
     */
    public function __construct(PlateauSquadro $plateau)
    {
        $this->plateau = $plateau;
    }

    /**
     * Vérifie si une pièce peut être jouée à une position donnée.
     * 
     * @param int $ligne Ligne de la pièce.
     * @param int $colonne Colonne de la pièce.
     * @return bool True si la pièce peut être jouée, sinon False.
     */
    public function estJouablePiece(int $ligne, int $colonne): bool
    {
        $piece = $this->plateau->getPiece($ligne, $colonne);
        if ($piece === null || $piece->getCouleur() === PieceSquadro::VIDE || $piece->getCouleur() === PieceSquadro::NEUTRE) {
            return false;
        }

        return true;
    }


    /**
     * Reculer les pièces adverses survolées vers leur case de départ.
     * 
     * @param int $x Ligne actuelle de la pièce en déplacement.
     * @param int $y Colonne actuelle de la pièce en déplacement.
     * @param int $destX Ligne de destination.
     * @param int $destY Colonne de destination.
     */
    public function reculerPiece(int $x, int $y, int $destX, int $destY): void
    {
        // Récupérer la pièce en mouvement
        $piece = $this->plateau->getPiece($x, $y);
        if ($piece === null || $piece->getCouleur() === PieceSquadro::VIDE) {
            return;
        }

        // Déterminer le pas de déplacement en fonction de la couleur et de la direction de la pièce
        $stepX = 0;
        $stepY = 0;

        if ($piece->getCouleur() === PieceSquadro::BLANC) {
            // Les pièces blanches se déplacent horizontalement
            $stepY = ($piece->getDirection() === PieceSquadro::EST) ? 1 : -1;
        } elseif ($piece->getCouleur() === PieceSquadro::NOIR) {
            // Les pièces noires se déplacent verticalement
            $stepX = ($piece->getDirection() === PieceSquadro::SUD) ? 1 : -1;
        }

        // Parcourir les cases entre la position actuelle et la destination
        $curX = $x + $stepX;
        $curY = $y + $stepY;

        while (($stepX !== 0 && $curX !== $destX + $stepX) || ($stepY !== 0 && $curY !== $destY + $stepY)) {
            $adversaryPiece = $this->plateau->getPiece($curX, $curY);
            if ($adversaryPiece !== null && $adversaryPiece->getCouleur() !== PieceSquadro::VIDE && $adversaryPiece->getCouleur() !== $piece->getCouleur()) {
                // Déterminer la position de départ de la pièce adverse en fonction de sa direction
                $startX = $curX;
                $startY = $curY;

                if ($adversaryPiece->getCouleur() === PieceSquadro::BLANC) {
                    $startY = ($adversaryPiece->getDirection() === PieceSquadro::EST) ? 0 : 6;
                } elseif ($adversaryPiece->getCouleur() === PieceSquadro::NOIR) {
                    $startX = ($adversaryPiece->getDirection() === PieceSquadro::SUD) ? 0 : 6;
                }

                // Placer la pièce adverse à sa position de départ
                $this->plateau->setPiece($adversaryPiece, $startX, $startY);
                // Remplacer l'ancienne position par une pièce vide
                $this->plateau->setPiece(PieceSquadro::initVide(), $curX, $curY);
            }
            $curX += $stepX;
            $curY += $stepY;
        }
    }



    /**
     * Déplace une pièce sur le plateau si elle est jouable.
     * 
     * @param int $x Ligne actuelle de la pièce.
     * @param int $y Colonne actuelle de la pièce.
     * @return bool True si la pièce a été déplacée, sinon False.
     */
    public function jouePiece(int $x, int $y): bool
    {
        if (!$this->estJouablePiece($x, $y)) {
            return false;
        }

        [$destX, $destY] = $this->plateau->getCoordDestination($x, $y);
        $this->reculerPiece($x, $y, $destX, $destY);

        $piece = $this->plateau->getPiece($x, $y);
        $this->plateau->setPiece($piece, $destX, $destY);
        $this->plateau->setPiece(PieceSquadro::initVide(), $x, $y);

        if (($piece->getCouleur() === PieceSquadro::BLANC && $destY === 6) ||
            ($piece->getCouleur() === PieceSquadro::NOIR && $destX === 0)
        ) {
            $piece->inverseDirection();
        }

        if ($piece->getCouleur() === PieceSquadro::BLANC && $destY === 0 && $piece->getDirection() === PieceSquadro::OUEST) {
            $this->sortPiece($piece->getCouleur(), $x);
        } 

        if($piece->getCouleur() === PieceSquadro::NOIR && $destX === 6 && $piece->getDirection() === PieceSquadro::SUD){
            $this->sortPiece($piece->getCouleur(), $y);
        }

        return true;
    }


    /**
     * Retire une pièce du plateau lorsqu'elle atteint sa position finale.
     * 
     * @param int $couleur Couleur de la pièce (NOIR ou BLANC).
     * @param int $rang Rang de la pièce sur le plateau.
     */
    public function sortPiece(int $couleur, int $rang): void
    {
        if ($couleur === PieceSquadro::BLANC) {
            $this->plateau->retireLigneJouable($rang);
            $this->plateau->setPiece(PieceSquadro::initVide(), $rang, 0);
        } elseif ($couleur === PieceSquadro::NOIR) {
            $this->plateau->retireColonneJouable($rang);
            $this->plateau->setPiece(PieceSquadro::initVide(), 6, $rang);
        }
    }

    /**
     * Vérifie si une couleur a remporté la victoire.
     * 
     * @param int $couleur Couleur de la pièce (NOIR ou BLANC).
     * @return bool True si la victoire est remportée, sinon False.
     */
    public function remporteVictoire(int $couleur): bool
    {
        $piecesRestantes = ($couleur === PieceSquadro::BLANC) ? $this->plateau->getLignesJouables() : $this->plateau->getColonnesJouables();
        return count($piecesRestantes) <= 1;
    }
}





// Fonction de test
function test($description, $condition)
{
    if ($condition) {
        echo "[OK] $description<br/>";
    } else {
        echo "[ÉCHEC] $description<br/>";
    }
}



function testUnitaireAction()
{
    // Initialisation
    echo "Initialisation du plateau et des actions...<br/>";
    $plateau = new PlateauSquadro();
    print_r($plateau->getPlateau()[1]);
    $action = new ActionSquadro($plateau);

    // Tests : `estJouablePiece`

    test("Une pièce neutre jouable au début 0-0", $action->estJouablePiece(0, 0));
    test("Une pièce blanche est jouable au début 1-0", $action->estJouablePiece(1, 0));

    $piece = PieceSquadro::initBlancEst();
    $piece->inverseDirection();

    $plateau->setPiece($piece, 1, 0);
    test("Piece vient de terminer son parcour", $action->estJouablePiece(0, 0));

    $piece->inverseDirection();

    $plateau->setPiece($piece, 1, 0);

    // Tests : `jouePiece`
    $action->jouePiece(1, 0);
    test(
        "La pièce blanche a été déplacée correctement",
        $plateau->getPiece(1, 0)->getCouleur() === PieceSquadro::VIDE &&
            $plateau->getPiece(1, 1)->getCouleur() === PieceSquadro::BLANC
    );

    print_r($plateau->getPlateau()[1]);

    // Tests : `reculePiece`
    /*$action->reculerPiece(1, 1);
        test("La pièce blanche a été reculée à sa position initiale", 
            $plateau->getPiece(1, 0)->getCouleur() === PieceSquadro::BLANC &&
            $plateau->getPiece(1, 1)->getCouleur() === PieceSquadro::VIDE
        );
        /*
        // Tests : `sortPiece`
        $action->sortPiece(PieceSquadro::NOIR, 0);
        test("La pièce noire a été retirée du plateau", 
            $plateau->getPiece(6, 0)->getCouleur() === PieceSquadro::VIDE
        );

        // Tests : `remporteVictoire`
        $plateau->setPiece(new PieceSquadro(PieceSquadro::BLANC, PieceSquadro::EST), 0, 6);
        test("Une victoire blanche est détectée lorsque toutes les pièces ont terminé leur parcours",
            $action->remporteVictoire(PieceSquadro::BLANC)
        );
        */

    print_r($plateau->getPlateau()[1]);

    // Résumé final
    echo "<br/>Tous les tests sont terminés.<br/>";
}

//testUnitaireAction();

