<?php

$loader = new \Phalcon\Loader();


/**
 * Read the configuration
 */
$config = __DIR__ . "/config.example.ini";
$config_pro = __DIR__ . "/config.ini";
if (file_exists($config_pro)) {
    $config = $config_pro;
}
$config = new Phalcon\Config\Adapter\Ini($config);

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerNamespaces(array(
	'MyApp\Controllers' => APP_PATH . $config->application->controllersDir,
	'MyApp\Models'      => APP_PATH . $config->application->modelsDir,
	'MyApp\Services'    => APP_PATH . $config->application->servicesDir,
	'MyApp\Plugins'     => APP_PATH . $config->application->pluginsDir,
	'MyApp\Libraries'   => APP_PATH . $config->application->librariesDir,
))->register();

// load function
include_once APP_PATH . $config->application->pluginsDir . 'Common.php';

// load composer
$vendor = APP_PATH . '/vendor/autoload.php';
if (file_exists($vendor)) {
    include_once $vendor;
    if ($config->setting->appDebug == true) {
        include APP_PATH . $config->application->pluginsDir . 'Exception.php';
    }
}

// set error_reporting
switch ($config->setting->appDebug) {
    case true:
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING); #error_reporting(E_ALL);
        break;
    default:
        error_reporting(0);
}
