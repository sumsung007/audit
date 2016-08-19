<?php


namespace MyApp\Controllers;

use MyApp\Models\Utils;
use Phalcon\Mvc\Controller;


class PublicController extends Controller
{

    public function indexAction()
    {
    }


    public function loginAction()
    {
        Utils::tips('info', 'Login In Page');
    }


    public function logoutAction()
    {
        $this->session->destroy();
        Utils::tips('info', 'Logout Page');
    }


}
