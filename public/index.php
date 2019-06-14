<?php

declare(strict_types=1);

require dirname(__DIR__).'/config/bootstrap.php';

$kernel = new App\Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->run();
