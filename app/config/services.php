<?php

use Phalcon\DI\FactoryDefault,
	Phalcon\Mvc\View,
	Phalcon\Mvc\Dispatcher,
	Phalcon\Mvc\Url as UrlResolver,
	Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter,
	Phalcon\Mvc\View\Engine\Volt as VoltEngine,
	Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter,
	Phalcon\Session\Adapter\Files as SessionAdapter;

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
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set('data', function() use ($config) {
	return new DbAdapter(array(
		'host'      =>  $config->data->host,
		'username'  =>  $config->data->username,
		'password'  =>  $config->data->password,
		'dbname'    =>  $config->data->dbname,
		'charset'   =>  $config->data->charset
	));
});

$di->set('setting', function() use ($config) {
    return new DbAdapter(array(
        'host'      =>  $config->setting->host,
        'username'  =>  $config->setting->username,
        'password'  =>  $config->setting->password,
        'dbname'    =>  $config->setting->dbname,
        'charset'   =>  $config->setting->charset
    ));
});

$di->set('log', function() use ($config) {
    return new DbAdapter(array(
        'host'      =>  $config->log->host,
        'username'  =>  $config->log->username,
        'password'  =>  $config->log->password,
        'dbname'    =>  $config->log->dbname,
        'charset'   =>  $config->log->charset
    ));
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->set('modelsMetadata', function() use ($config) {
	return new MetaDataAdapter();
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
