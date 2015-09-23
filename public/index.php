<?php

define('APP_PATH', realpath(__DIR__ . '/../'));

use Phalcon\Mvc\Application;

/**
 * Read auto-loader
 */
include __DIR__ . "/../app/config/loader.php";

/**
 * Read services
 */
include __DIR__ . "/../app/config/services.php";

/**
 * Handle the request
 */
$application = new Application($di);

echo $application->handle()->getContent();
