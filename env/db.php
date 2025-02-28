<?php
// Charger l'autoloader de Composer pour utiliser Dotenv
require_once __DIR__ . '/../.env.php'; // Charger le fichier .env.php


// Vérification si les variables d'environnement sont bien définies
if (empty(getenv('sgbd')) || empty(getenv('host')) || empty(getenv('database')) || empty(getenv('user')) || empty(getenv('password'))) {
    throw new Exception("Les variables d'environnement de la base de données ne sont pas définies correctement.");
}

// Pour déboguer et voir si les variables sont bien chargées
print("sgbd: " . getenv('sgbd') . "\n");
print("host: " . getenv('host') . "\n");
print("database: " . getenv('database') . "\n");
print("user: " . getenv('user') . "\n");
print("password: " . getenv('password') . "\n");
