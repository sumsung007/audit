<?php

namespace MyApp\Controllers;

use Phalcon\Mvc\Controller;
use MyApp\Models\Demo;
use MyApp\Services\Services;

class DemoController extends Controller
{

    private $demoModel;

    public function initialize()
    {
        // parent::initialize();
        $this->demoModel = new Demo();
    }

    public function indexAction()
    {
        $this->demoModel->demo();
    }

    public function serviceAction()
    {
        $response = Services::pay('paypal')->notice();
        dump($response);
    }

}
