<?php
require_once __DIR__ . '/../.env.php';

$required = ['sgbd', 'host', 'database', 'user', 'password'];
foreach ($required as $key) {
    $value = getenv($key);
    if ($value === false || $value === '') {
        throw new Exception("La variable d'environnement '$key' de la base de données n'est pas définie.");
    }
}
