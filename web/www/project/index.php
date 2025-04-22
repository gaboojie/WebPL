<?php

// Project URL: cs4640.cs.virginia.edu/tbp8gx/project/index.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

spl_autoload_register(function ($classname) {
    include "/students/tbp8gx/students/tbp8gx/private/project/$classname.php";
});

$controller = new ProjectController($_GET);
$controller->run();