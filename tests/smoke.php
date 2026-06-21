<?php

declare(strict_types=1);

require_once __DIR__ . '/../Modele/action_squadro.php';

$plateau = new PlateauSquadro();
$action = new ActionSquadro($plateau);

assert($action->estJouablePiece(1, 0) === true);
assert($action->estJouablePiece(0, 0) === false);
assert($action->jouePiece(1, 0) === true);
assert($plateau->getPiece(1, 1)->getCouleur() === PieceSquadro::BLANC);

$json = $plateau->toJson();
$copy = PlateauSquadro::fromJson($json);
assert($copy->getPiece(1, 1)->getCouleur() === PieceSquadro::BLANC);

echo "Smoke tests OK\n";
