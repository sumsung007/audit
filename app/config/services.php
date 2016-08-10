<?php

use Phalcon\DI\FactoryDefault,
    Phalcon\Mvc\View,
    Phalcon\Mvc\Dispatcher,
    Phalcon\Mvc\Url as UrlResolver,
    Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter,
    Phalcon\Mvc\View\Engine\Volt as VoltEngine,
    Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter,
    Phalcon\Session\Adapter\Files as SessionAdapter,
    Phalcon\Events\Manager as EventsManager,
    Phalcon\Logger,
    Phalcon\Logger\Adapter\File as FileLogger,
    Phalcon\Cache\Frontend\Data as FrontData,
    Phalcon\Cache\Backend\File as BackFile,
    Phalcon\Cache\Backend\Redis as BackRedis;


/**
 * Events Manager
 */
$logger = new FileLogger(BASE_DIR . $config->application->logsDir . "Logs.log");
$eventsManager = new EventsManager();
$eventsManager->attach('db', function ($event, $connection) use ($config, $logger) {
    if ($event->getType() == 'beforeQuery') {
        if ($config->setting->recordSQL) {
            $logger->log($connection->getSQLStatement(), Logger::INFO);
        }
        if (preg_match('/drop|alter/i', $connection->getSQLStatement())) {
            return false;
        }
    }
    if ($event->getType() == 'afterQuery') {
    }
});


/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();

$di->set('config', function () use ($config) {
    return $config;
}, true);

$di->set('router', function () {
    return require __DIR__ . '/routes.php';
}, true);

$di->set('url', function () use ($config) {
    $url = new UrlResolver();
    $url->setBaseUri(BASE_DIR . $config->application->baseUri);
    return $url;
}, true);

$di->set('view', function () use ($config) {

    $view = new View();

    $view->setViewsDir(BASE_DIR . $config->application->viewsDir);

    $view->registerEngines(array(
        '.html' => function ($view, $di) use ($config) {

            $volt = new VoltEngine($view, $di);

            $volt->setOptions(array(
                'compiledPath' => BASE_DIR . $config->application->cacheDir,
                'compiledSeparator' => '_'
            ));

            return $volt;
        },
        '.phtml' => 'Phalcon\Mvc\View\Engine\Php' // Generate Template files uses PHP itself as the template engine
    ));

    return $view;
}, true);

$di->set('modelsMetadata', function () use ($config) {
    return new MetaDataAdapter();
}, true);

// link https://docs.phalconphp.com/zh/latest/reference/cache.html
$di->set('modelsCache', function () use ($config) {
    $frontCache = new FrontData(array("lifetime" => $config->setting->cacheTime));
    // Redis Cache
    if ($config->redis) {
        $cache = new BackRedis($frontCache, array('prefix' => $config->redis->prefix, 'host' => $config->redis->host, 'port' => $config->redis->port, 'index' => $config->redis->index));
        return $cache;
    }
    // File Cache
    $cache = new BackFile($frontCache, array('prefix' => 'cache_', 'cacheDir' => BASE_DIR . $config->application->cacheDir));
    return $cache;
}, true);

$di->set('session', function () {
    $session = new SessionAdapter();
    $session->start();
    return $session;
}, true);

$di->set('dispatcher', function () {
    $dispatcher = new Dispatcher();
    $dispatcher->setDefaultNamespace('MyApp\Controllers');
    return $dispatcher;
}, true);


/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set('data', function () use ($config, $eventsManager) {
    $connection = new DbAdapter(array(
			'host'      =>  $config->db_data->host,
			'username'  =>  $config->db_data->username,
			'password'  =>  $config->db_data->password,
			'dbname'    =>  $config->db_data->dbname,
			'charset'   =>  $config->db_data->charset
	));
    $connection->setEventsManager($eventsManager);
    return $connection;
}, true);

$di->set('setting', function () use ($config, $eventsManager) {
    $connection = new DbAdapter(array(
			'host'      =>  $config->db_setting->host,
			'username'  =>  $config->db_setting->username,
			'password'  =>  $config->db_setting->password,
			'dbname'    =>  $config->db_setting->dbname,
			'charset'   =>  $config->db_setting->charset
	));
    $connection->setEventsManager($eventsManager);
    return $connection;
}, true);

$di->set('log', function () use ($config, $eventsManager) {
    $connection= new DbAdapter(array(
			'host'      =>  $config->db_log->host,
			'username'  =>  $config->db_log->username,
			'password'  =>  $config->db_log->password,
			'dbname'    =>  $config->db_log->dbname,
			'charset'   =>  $config->db_log->charset
	));
    $connection->setEventsManager($eventsManager);
    return $connection;
}, true);
