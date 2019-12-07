<?php

declare(strict_types=1);

require dirname(__DIR__).'/config/bootstrap.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    \Symfony\Component\ErrorHandler\Debug::enable();
}

$kernel = new \App\Kernel($_SERVER['APP_ENV'], $_SERVER['APP_DEBUG']);
$kernel->run();
