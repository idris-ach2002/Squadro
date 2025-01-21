<?php
    require_once 'piece_squadro.php';
    require_once 'plateau_squadro.php';

    class ActionSquadro {
        private PlateauSquadro $plateau;

        public function __construct(PlateauSquadro $plateau) {
            $this->plateau = $plateau;
        }

        public function estJouablePiece(int $ligne, int $colonne): bool {
            $piece = $this->plateau->getPiece($ligne, $colonne);
            if ($piece->getCouleur() === PieceSquadro::VIDE) {
                return false;
            }

            $destination = $this->plateau->getCoordDestination($ligne, $colonne);
            [$destLigne, $destColonne] = $destination;

            return $destLigne >= 0 && $destLigne < 7 &&
                $destColonne >= 0 && $destColonne < 7 &&
                $this->plateau->getPiece($destLigne, $destColonne)->getCouleur() === PieceSquadro::VIDE;
        }

        public function jouePiece(int $ligne, int $colonne): bool {
            if (!$this->estJouablePiece($ligne, $colonne)) {
                return false;
            }
        
            $piece = $this->plateau->getPiece($ligne, $colonne);
            $destination = $this->plateau->getCoordDestination($ligne, $colonne);
            [$destLigne, $destColonne] = $destination;
        
            // Déplacement de la pièce
            $this->plateau->setPiece($piece, $destLigne, $destColonne);
            $this->plateau->setPiece(PieceSquadro::initVide(), $ligne, $colonne);
        
            // Gérer le retournement ou la sortie pour les pièces noires
            if ($piece->getCouleur() === PieceSquadro::NOIR) {
                if ($piece->direction === PieceSquadro::NORD && $destLigne === 6) {
                    // Fin de l'aller, inverser la direction vers le sud
                    $piece->direction = PieceSquadro::SUD;
                } elseif ($piece->direction === PieceSquadro::SUD && $destLigne === 0) {
                    // Fin du retour, retirer la pièce du plateau
                    $this->sortPiece($piece->getCouleur(), $destColonne);
                }
            }
        
            // Gérer le retournement ou la sortie pour les pièces blanches
            if ($piece->getCouleur() === PieceSquadro::BLANC) {
                if ($piece->direction === PieceSquadro::EST && $destColonne === 6) {
                    // Fin de l'aller, inverser la direction vers l'ouest
                    $piece->direction = PieceSquadro::OUEST;
                } elseif ($piece->direction === PieceSquadro::OUEST && $destColonne === 0) {
                    // Fin du retour, retirer la pièce du plateau
                    $this->sortPiece($piece->getCouleur(), $destLigne);
                }
            }
        
            // Gérer les reculs des pièces adverses
            $this->gererReculs($piece, $ligne, $colonne, $destLigne, $destColonne);
        
            return true;
        }
        
        public function reculePiece(int $ligne, int $colonne): bool {
            $piece = $this->plateau->getPiece($ligne, $colonne);
            if ($piece->getCouleur() === PieceSquadro::VIDE) {
                return false;
            }

            if ($piece->direction === PieceSquadro::NORD) {
                $this->plateau->setPiece($piece, 6, $colonne);
            } elseif ($piece->direction === PieceSquadro::SUD) {
                $this->plateau->setPiece($piece, 0, $colonne);
            } elseif ($piece->direction === PieceSquadro::EST) {
                $this->plateau->setPiece($piece, $ligne, 0);
            } elseif ($piece->direction === PieceSquadro::OUEST) {
                $this->plateau->setPiece($piece, $ligne, 6);
            }

            $piece.inverseDirection();

            return true;
        }

        public function sortPiece(int $couleur, int $rang): void {
            if ($couleur === PieceSquadro::NOIR) {
                $this->plateau->setPiece(PieceSquadro::initVide(), 6, $rang);
            } elseif ($couleur === PieceSquadro::BLANC) {
                $this->plateau->setPiece(PieceSquadro::initVide(), $rang, 0);
            }
        }

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


    // Initialisation
    echo "Initialisation du plateau et des actions...<br/>";
    $plateau = new PlateauSquadro();
    $action = new ActionSquadro($plateau);

    // Fonction de test
    function test($description, $condition) {
        if ($condition) {
            echo "[OK] $description<br/>";
        } else {
            echo "[ÉCHEC] $description<br/>";
        }
    }

    // Tests : `estJouablePiece`
    $pieceNoire = new PieceSquadro(PieceSquadro::NOIR, PieceSquadro::NORD);
    $plateau->setPiece($pieceNoire, 0, 0);
    test("Une pièce noire est jouable au début", $action->estJouablePiece(0, 0));

    $plateau->setPiece(new PieceSquadro(PieceSquadro::BLANC, PieceSquadro::EST), 1, 0);
    test("Une pièce noire n'est pas jouable si la destination est occupée", !$action->estJouablePiece(0, 0));

    $plateau->setPiece(PieceSquadro::initVide(), 1, 0);
    test("Une pièce noire devient jouable après libération de la destination", $action->estJouablePiece(0, 0));

    // Tests : `jouePiece`
    $action->jouePiece(0, 0);
    test("La pièce noire a été déplacée correctement", 
        $plateau->getPiece(1, 0)->getCouleur() === PieceSquadro::NOIR &&
        $plateau->getPiece(0, 0)->getCouleur() === PieceSquadro::VIDE
    );

    // Tests : `reculePiece`
    $action->reculePiece(1, 0);
    test("La pièce noire a été reculée à sa position initiale", 
        $plateau->getPiece(6, 0)->getCouleur() === PieceSquadro::NOIR &&
        $plateau->getPiece(1, 0)->getCouleur() === PieceSquadro::VIDE
    );

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

    // Résumé final
    echo "<br/>Tous les tests sont terminés.<br/>";

?>