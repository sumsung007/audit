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
        // 管理后台的登录处理
        $ticket = $this->request->get('ticket', 'string');
        if (!$ticket) {
            $callback = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $LoginURL = $this->config->sso->BaseURL . '?redirect=' . urlencode($callback);
            header('Location:' . $LoginURL);
            exit();
        }

        // 验证ticket
        $verifyURL = $this->config->sso->BaseURL . '/verify?ticket=' . $ticket;
        $result = file_get_contents($verifyURL);
        $result = json_decode($result, true);

        if ($result['code'] != 0) {
            Utils::tips('warning', 'Login Failed');
        }


        // TODO::拿Ticket换取资源
        // $resourceURL = $this->config->sso->BaseURL . '/resources?app=' . $this->config->sso->APPID . '&ticket=' . $ticket;
        // $resources = file_get_contents($resourceURL); // TODO :: 程序里报错,需要单独拿出来
        // TODO::接权限控制


        // 设置SESSION
        $this->session->set('userID', $result['data']['userID']);
        $this->session->set('username', $result['data']['username']);
        $this->session->set('name', $result['data']['name']);


        header('Location:/');
        exit();
    }


    public function logoutAction()
    {
        $this->session->destroy();
        Utils::tips('info', 'Logout Page');
    }


}
