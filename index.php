<?php

declare(strict_types=1);

require_once __DIR__ . '/Core/bootstrap.php';

if (isset($_SESSION[App::SESSION_PLAYER])) {
    App::redirect('/Vue/choixAction.php');
}

App::redirect('/Vue/login.php');
