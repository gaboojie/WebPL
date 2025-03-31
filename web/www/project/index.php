<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

spl_autoload_register(function ($classname) {
    include "/opt/src/project/$classname.php";
});

$controller = new ProjectController($_GET);
$controller->run();