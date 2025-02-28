<?php

session_start();

$_SESSION["etat"] = "login";

header('Location: Controlleur/traiteActionSquadro.php');
header('HTTP/1.1 303 See Other');

