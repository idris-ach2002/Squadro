<?php
// Charger l'autoloader de Composer pour utiliser Dotenv
require_once __DIR__ . '/../vendor/autoload.php'; // Si le dossier vendor est à la racine

// Charger le fichier .env avec php dotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../'); // Charger .env situé à la racine
$dotenv->load();

// Vérification si les variables d'environnement sont bien définies
if (empty($_ENV['sgbd']) || empty($_ENV['host']) || empty($_ENV['database']) || empty($_ENV['user']) || empty($_ENV['password'])) {
    throw new Exception("Les variables d'environnement de la base de données ne sont pas définies correctement.");
}

// Pour déboguer et voir si les variables sont bien chargées
print("sgbd: " . $_ENV['sgbd'] . "\n");
print("host: " . $_ENV['host'] . "\n");
print("database: " . $_ENV['database'] . "\n");
print("user: " . $_ENV['user'] . "\n");
print("password: " . $_ENV['password'] . "\n");
