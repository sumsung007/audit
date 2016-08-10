<?php

namespace MyApp\Controllers;

use Phalcon\Mvc\Controller;

class ControllerBase extends Controller
{

    public $_app;

    public function initialize()
    {
        $this->_app = $this->dispatcher->getParam("app");


        // 设置时区
        ini_set("date.timezone", $this->config->setting->timezone);


        // 日志记录
        if ($this->config->setting->recordRequest) {
            $_url = $_REQUEST['_url'];
            unset($_REQUEST['_url']);
            $log = empty($_REQUEST) ? $_url : ($_url . '?' . urldecode(http_build_query($_REQUEST)));
            write_log($log, date("Ymd") . '.log');
        }
    }

}
