<?php
/**
 * Package  routes.php
 * Author:  joe@xxtime.com
 * Date:    2015-07-20
 * Time:    下午10:32
 * Link:    http://www.xxtime.com
 * link: http://docs.phalconphp.com/zh/latest/reference/routing.html
 */

$router = new Phalcon\Mvc\Router(false);

$router->add('/:controller/:action/:params', array(
	'namespace' => 'MyApp\Controllers',
	'controller' => 1,
	'action' => 2,
	'params' => 3,
));

$router->add('/:controller', array(
	'namespace' => 'MyApp\Controllers',
	'controller' => 1
));

$router->add('/admin/:controller/:action/:params', array(
	'namespace' => 'MyApp\Controllers\Admin',
	'controller' => 1,
	'action' => 2,
	'params' => 3,
));

$router->add('/admin/:controller', array(
	'namespace' => 'MyApp\Controllers\Admin',
	'controller' => 1
));

$router->removeExtraSlashes(true);

return $router;