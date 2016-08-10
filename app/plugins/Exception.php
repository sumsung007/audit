<?php
/**
 * whoops exception adapter
 * Created by PhpStorm.
 * User: joe
 * Date: 14/12/7
 * Time: 12:15
 * Link: http://filp.github.io/whoops/
 */

use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

$whoops = new Whoops\Run;
$handler = new PrettyPageHandler;
$handler->setPageTitle("ERROR");
//$handler->addDataTable("Extra Info", array());
$whoops->pushHandler($handler);
//$whoops->pushHandler(new JsonResponseHandler);
// Set Whoops as the default error and exception handler used by PHP:
$whoops->register();
//throw new RuntimeException("Oh no !");
