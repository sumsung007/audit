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
$logger = new FileLogger(APP_PATH . $config->application->logsDir . "Logs.log");
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

$di->set('router', function(){
	return require __DIR__ . '/routes.php';
}, true);

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->set('url', function() use ($config) {
	$url = new UrlResolver();
	$url->setBaseUri(APP_PATH . $config->application->baseUri);
	return $url;
}, true);

/**
 * Setting up the view component
 */
$di->set('view', function() use ($config) {

	$view = new View();

	$view->setViewsDir(APP_PATH . $config->application->viewsDir);

	$view->registerEngines(array(
		'.html' => function($view, $di) use ($config) {

			$volt = new VoltEngine($view, $di);

			$volt->setOptions(array(
				'compiledPath' => APP_PATH . $config->application->cacheDir,
				'compiledSeparator' => '_'
			));

			return $volt;
		},
		'.phtml' => 'Phalcon\Mvc\View\Engine\Php' // Generate Template files uses PHP itself as the template engine
	));

	return $view;
}, true);

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->set('modelsMetadata', function() use ($config) {
	return new MetaDataAdapter();
});

/**
 * link https://docs.phalconphp.com/zh/latest/reference/cache.html
 */
$di->set('modelsCache', function() use ($config) {
	$frontCache = new FrontData(array("lifetime" => 3600));
	// Redis Cache
	if ($config->redis) {
		$cache = new BackRedis($frontCache, array('prefix' => $config->redis->prefix, 'host' => $config->redis->host, 'port' => $config->redis->port, 'index' => $config->redis->index));
		return $cache;
	}
	// File Cache
	$cache = new BackFile($frontCache, array('prefix' => 'cache_', 'cacheDir' => APP_PATH . $config->application->cacheDir));
	return $cache;
});

/**
 * Start the session the first time some component request the session service
 */
$di->set('session', function() {
	$session = new SessionAdapter();
	$session->start();
	return $session;
});

$di->set('dispatcher', function(){
	$dispatcher = new Dispatcher();
	$dispatcher->setDefaultNamespace('MyApp\Controllers');
	return $dispatcher;
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set('data', function() use ($config, $eventsManager) {
    $connection = new DbAdapter(array(
			'host'      =>  $config->db_data->host,
			'username'  =>  $config->db_data->username,
			'password'  =>  $config->db_data->password,
			'dbname'    =>  $config->db_data->dbname,
			'charset'   =>  $config->db_data->charset
	));
    $connection->setEventsManager($eventsManager);
    return $connection;
});

$di->set('setting', function() use ($config, $eventsManager) {
    $connection = new DbAdapter(array(
			'host'      =>  $config->db_setting->host,
			'username'  =>  $config->db_setting->username,
			'password'  =>  $config->db_setting->password,
			'dbname'    =>  $config->db_setting->dbname,
			'charset'   =>  $config->db_setting->charset
	));
    $connection->setEventsManager($eventsManager);
    return $connection;
});

$di->set('log', function() use ($config, $eventsManager) {
    $connection= new DbAdapter(array(
			'host'      =>  $config->db_log->host,
			'username'  =>  $config->db_log->username,
			'password'  =>  $config->db_log->password,
			'dbname'    =>  $config->db_log->dbname,
			'charset'   =>  $config->db_log->charset
	));
    $connection->setEventsManager($eventsManager);
    return $connection;
});
