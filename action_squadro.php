<?php

class ActionSquadro {
    private PlateauSquadro $plateau;

    public function __construct(PlateauSquadro $plateau) {
        $this->plateau = $plateau;
    }

    // Méthode estJouablePiece
    public function estJouablePiece(int $ligne, int $colonne): bool {
        $piece = $this->plateau->getPiece($ligne, $colonne);
        $destination = $this->plateau->getCoordDestination($ligne, $colonne);
        [$destLigne, $destColonne] = $destination;

        // Vérifie si la destination est dans les limites du plateau et si la case est vide
        return $destLigne >= 0 && $destLigne < 7 && $destColonne >= 0 && $destColonne < 7 && $this->plateau->getPiece($destLigne, $destColonne)->getCouleur() === PieceSquadro::VIDE;
    }

    // Méthode jouePiece
    public function jouePiece(int $ligne, int $colonne): bool {
        if (!$this->estJouablePiece($ligne, $colonne)) {
            return false;
        }

        $piece = $this->plateau->getPiece($ligne, $colonne);
        $destination = $this->plateau->getCoordDestination($ligne, $colonne);
        [$destLigne, $destColonne] = $destination;

        // Déplace la pièce
        $this->plateau->setPiece($piece, $destLigne, $destColonne);
        $this->plateau->setPiece(PieceSquadro::initVide(), $ligne, $colonne);

        // Gère le retournement ou la sortie de la pièce
        if ($piece->getCouleur() === PieceSquadro::NOIR && $destLigne === 0) {
            $piece->direction = PieceSquadro::SUD;
        } elseif ($piece->getCouleur() === PieceSquadro::BLANC && $destColonne === 0) {
            $piece->direction = PieceSquadro::EST;
        } elseif ($piece->getCouleur() === PieceSquadro::NOIR && $destLigne === 6) {
            $this->sortPiece($piece->getCouleur(), $destColonne);
        } elseif ($piece->getCouleur() === PieceSquadro::BLANC && $destColonne === 6) {
            $this->sortPiece($piece->getCouleur(), $destLigne);
        }

        // Gère les reculs des pièces adverses
        $this->gererReculs($piece, $ligne, $colonne, $destLigne, $destColonne);

        return true;
    }

    // Méthode reculePiece
    public function reculePiece(int $ligne, int $colonne): bool {
        $piece = $this->plateau->getPiece($ligne, $colonne);
        if ($piece->getCouleur() === PieceSquadro::VIDE || $piece->getCouleur() === PieceSquadro::NEUTRE) {
            return false;
        }

        if ($piece->direction === PieceSquadro::NORD || $piece->direction === PieceSquadro::SUD) {
            $piece->direction = ($piece->direction === PieceSquadro::NORD) ? PieceSquadro::SUD : PieceSquadro::NORD;
            $this->plateau->setPiece($piece, 6, $colonne);
        } else {
            $piece->direction = ($piece->direction === PieceSquadro::EST) ? PieceSquadro::OUEST : PieceSquadro::EST;
            $this->plateau->setPiece($piece, $ligne, 6);
        }

        return true;
    }

    // Méthode sortPiece
    public function sortPiece(int $couleur, int $rang): void {
        if ($couleur === PieceSquadro::NOIR) {
            $this->plateau->setPiece(PieceSquadro::initVide(), 6, $rang);
        } elseif ($couleur === PieceSquadro::BLANC) {
            $this->plateau->setPiece(PieceSquadro::initVide(), $rang, 6);
        }
    }

    // Méthode remporteVictoire
    public function remporteVictoire(int $couleur): bool {
        if ($couleur === PieceSquadro::NOIR) {
            foreach ($this->plateau->getPlateau()[6] as $piece) {
                if ($piece->getCouleur() === PieceSquadro::NOIR) {
                    return false;
                }
            }
        } elseif ($couleur === PieceSquadro::BLANC) {
            foreach ($this->plateau->getPlateau() as $ligne) {
                if ($ligne[6]->getCouleur() === PieceSquadro::BLANC) {
                    return false;
                }
            }
        }
        return true;
    }

    // Méthode privée pour gérer les reculs des pièces adverses
    private function gererReculs(PieceSquadro $piece, int $ligne, int $colonne, int $destLigne, int $destColonne): void {
        // Implémentation de la logique pour gérer les reculs des pièces adverses
        // Cette méthode doit être complétée selon les règles spécifiques du jeu
        $direction = $piece->direction;
        $vitesse = ($piece->couleur === PieceSquadro::BLANC) ? PlateauSquadro::BLANC_V_ALLER[$ligne] : PlateauSquadro::NOIR_V_ALLER[$colonne];

        for ($i = 1; $i <= $vitesse; $i++) {
            $currentLigne = $ligne;
            $currentColonne = $colonne;

            switch ($direction) {
                case PieceSquadro::NORD:
                    $currentLigne -= $i;
                    break;
                case PieceSquadro::EST:
                    $currentColonne += $i;
                    break;
                case PieceSquadro::SUD:
                    $currentLigne += $i;
                    break;
                case PieceSquadro::OUEST:
                    $currentColonne -= $i;
                    break;
            }

            if ($currentLigne >= 0 && $currentLigne < 7 && $currentColonne >= 0 && $currentColonne < 7) {
                $adversaire = $this->plateau->getPiece($currentLigne, $currentColonne);
                if ($adversaire->getCouleur() !== $piece->getCouleur() && $adversaire->getCouleur() !== PieceSquadro::VIDE && $adversaire->getCouleur() !== PieceSquadro::NEUTRE) {
                    $this->reculePiece($currentLigne, $currentColonne);
                }
            }
        }
    }
}
?>