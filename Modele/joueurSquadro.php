<?php

declare(strict_types=1);

final class JoueurSquadro
{
    private string $nomJoueur;
    private int $id;

    public function __construct(string $nomJoueur, int $id)
    {
        $this->nomJoueur = trim($nomJoueur);
        $this->id = $id;
    }

    public function getNomJoueur(): string
    {
        return $this->nomJoueur;
    }

    public function setNomJoueur(string $nom): void
    {
        $this->nomJoueur = trim($nom);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function initials(): string
    {
        $name = preg_replace('/\s+/', ' ', trim($this->nomJoueur));
        if ($name === '') {
            return '?';
        }

        $parts = explode(' ', $name);
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }

        return $initials !== '' ? $initials : '?';
    }

    public function toJson(): string
    {
        $json = json_encode([
            'nomJoueur' => $this->nomJoueur,
            'id' => $this->id,
        ], JSON_THROW_ON_ERROR);

        return $json;
    }

    public static function fromJson(string $json): JoueurSquadro
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data) || !isset($data['nomJoueur'], $data['id'])) {
            throw new InvalidArgumentException('JSON joueur invalide.');
        }

        return new JoueurSquadro((string) $data['nomJoueur'], (int) $data['id']);
    }
}
