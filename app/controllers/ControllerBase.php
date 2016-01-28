<?php

namespace MyApp\Controllers;

use Phalcon\Mvc\Controller;

class ControllerBase extends Controller
{

    public $_config;
    public $_app;

    public function initialize()
    {
        global $config;
        $this->_config = $config;
        $this->_app = $this->dispatcher->getParam("app");


        // 设置时区
        ini_set("date.timezone", $this->_config->env->timezone);


        // 日志记录
        if ($config->development->record_request) {
            write_log(urldecode(http_build_query($_REQUEST)), date("Ymd") . '.log');
        }
    }

}
