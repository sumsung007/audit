<?php


namespace MyApp\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;


class TipsController extends Controller
{


    public function indexAction()
    {
        $flashData = json_decode(trim($this->cookies->get('flash')->getValue()), true);
        $this->view->message = $flashData['message'];
        $this->view->pick("tips/index");
    }


    public function show401Action()
    {
        $this->view->message = 'Error 401, No Permission';
        $this->view->pick("tips/errors");
    }


    public function show404Action()
    {
        $this->view->message = 'Error 404, Not Found';
        $this->view->pick("tips/errors");
    }


    public function exceptionAction()
    {
        $this->view->message = 'Error 400, Exception Occurs';
        $this->view->pick("tips/errors");
    }

}
