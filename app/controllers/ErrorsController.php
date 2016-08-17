<?php


namespace MyApp\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;


class ErrorsController extends Controller
{


    public function indexAction()
    {
        dd('Error Home Page');
    }


    public function show401Action()
    {
        dd('Error 401, No Permission');
    }


    public function show404Action()
    {
        dd('Error 404, Not Found');
    }


    public function exceptionAction()
    {
        dd('Error 400, There Is A Exception');
    }

}
