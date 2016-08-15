<?php


namespace MyApp\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;
use MyApp\Models\Demo;
use MyApp\Services\Services;
use Phalcon\Filter;


class DemoController extends Controller
{

    private $demoModel;
    private $filterModel;


    public function beforeExecuteRoute(Dispatcher $dispatcher)
    {
        // 在每一个找到的动作前执行
    }


    public function afterExecuteRoute(Dispatcher $dispatcher)
    {
        // 在每一个找到的动作后执行
    }


    public function initialize()
    {
        // parent::initialize();
        $this->demoModel = new Demo();


        // 过滤器
        $this->filterModel = new Filter();
        $this->filterModel->add('dataFilter', function ($value) {
            return preg_replace('/[^0-9a-zA-Z_\-,.#@*:]/', '', $value);
        });
    }


    public function indexAction()
    {
        dump('Demo Page');
    }


    // 查找 https://docs.phalconphp.com/zh/latest/reference/models.html#binding-parameters
    public function findAction()
    {
        $robots = $this->demoModel->find(
            array(
                "conditions" => "id >= :id:",
                "columns" => "id, username,password",
                "order" => "username DESC",
                "offset" => 0,
                "limit" => 10,
                "group" => "id, username",
                "bind" => array("id" => 1),
                //"cache" => array("lifetime" => 3600, "key" => "my-find-key") // 缓存结果集
            )
        );
        dump($robots->toArray());
        exit;


        // 也可以这样
        $robots = Robots::query()
            ->where("type = :type:")
            ->andWhere("year < 2000")
            ->bind(array("type" => "mechanical"))
            ->order("name")
            ->execute();


        // 单条记录
        $data = $this->demoModel->findFirst();

    }


    // 创建记录 更多形式参考save方法
    public function createAction()
    {
        // 方法一
        $this->demoModel->name = "Joe";
        $this->demoModel->age = "28";
        $this->demoModel->create();
        return $this->demoModel->id;
    }


    // 创建与更新记录
    public function saveAction()
    {
        // 方法一
        $this->demoModel->name = "Joe";
        $this->demoModel->age = "28";
        $this->demoModel->save();
        return $this->demoModel->id;


        // 方法二
        $this->demoModel->save(
            array(
                "type" => "people",
                "name" => "JoeChu",
                "year" => 1987
            )
        );


        // 方法三
        $this->demoModel->save($_POST);


        // 方法四
        $this->demoModel->save(
            $_POST,
            array(
                'name',
                'type'
            )
        );
    }


    public function demoAction()
    {
        $this->demoModel->demo();
    }


    // 过滤器
    public function filterAction()
    {
        $data = $this->request->get('id');
        $data = $this->filterModel->sanitize($data, "dataFilter");
    }


    public function serviceAction()
    {
        $response = Services::pay('paypal')->notice();
        dump($response);
    }


    //https://docs.phalconphp.com/en/latest/reference/cookies.html
    public function cookiesAction()
    {
        $this->cookies->set('foo', 'some cookies', time() + 86400);
        $this->cookies->send();

        if ($this->cookies->has('foo')) {
            $value = $this->cookies->get('foo')->getValue();
        }
        dd($value);
    }


    // link https://docs.phalconphp.com/zh/latest/reference/volt.html
    public function templateAction()
    {
        $this->view->data = time();
        $this->view->pick("demo/template");
    }

}
