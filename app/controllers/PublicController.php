<?php


namespace MyApp\Controllers;

use Phalcon\Mvc\Controller;


class PublicController extends Controller
{

    public function indexAction()
    {
    }


    public function loginAction()
    {
        dd('Login In Page');
    }


    public function logoutAction()
    {
        $this->session->destroy();
        dd('Logout Page');
    }


}
