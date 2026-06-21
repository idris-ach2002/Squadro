<?php
/**
 * Configuration database locale/Docker.
 *
 * Le projet lit les valeurs avec getenv('...'). Ce fichier fournit des valeurs
 * par défaut afin que l'application démarre sans configuration manuelle.
 * Les variables définies par Docker Compose gardent la priorité.
 */
$defaults = [
    'sgbd' => 'pgsql',
    'host' => 'db',
    'database' => 'squadro_db',
    'user' => 'squadro_user',
    'password' => 'password',
];

foreach ($defaults as $key => $value) {
    $current = getenv($key);
    if ($current === false || $current === '') {
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
