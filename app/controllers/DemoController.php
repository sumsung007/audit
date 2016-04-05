<?php

namespace MyApp\Controllers;

use Phalcon\Mvc\Controller;
use MyApp\Services\Services;

class DemoController extends Controller
{

    public function indexAction()
    {
    }

    public function serviceAction()
    {
        $response = Services::pay('paypal')->notice();
        dump($response);
    }

}
