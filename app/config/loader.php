<?php


use Phalcon\Loader;
use Phalcon\Config\Adapter\Ini;


$config = new Ini(APP_DIR . "/config/config.ini");
$loader = new Loader();


// We're a registering a set of directories taken from the configuration file
$loader->registerNamespaces(array(
    'MyApp\Controllers\Api'   => BASE_DIR . $config->application->controllersDir . 'api/',
    'MyApp\Controllers\Admin' => BASE_DIR . $config->application->controllersDir . 'admin/',
    'MyApp\Controllers'       => BASE_DIR . $config->application->controllersDir,
    'MyApp\Models'            => BASE_DIR . $config->application->modelsDir,
    'MyApp\Services'          => BASE_DIR . $config->application->servicesDir,
    'MyApp\Plugins'           => BASE_DIR . $config->application->pluginsDir,
    'MyApp\Libraries'         => BASE_DIR . $config->application->librariesDir,
))->register();


// load function
include_once BASE_DIR . $config->application->pluginsDir . 'Common.php';


// load composer
if (!file_exists(BASE_DIR . '/vendor/autoload.php')) {
    die('The project needs Composer, please check vendor directory');
}
include_once BASE_DIR . '/vendor/autoload.php';
if ($config->setting->sandbox == true) {
    include BASE_DIR . $config->application->pluginsDir . 'Exception.php';
}


// sandbox
switch ($config->setting->sandbox) {
    case true:
        error_reporting(E_ALL); //error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
        break;
    default:
        header_remove('X-Powered-By');
        error_reporting(0);
}