<?php

namespace MyApp\Controllers\Api;

use MyApp\Models\Auth;
use MyApp\Models\Utils;

class SsoController extends ControllerBase
{

    private $authModel;
    private $utilsModel;


    public function initialize()
    {
        $this->authModel = new Auth();
        $this->utilsModel = new Utils();
    }


    public function indexAction()
    {
        $this->loginAction();
    }


    public function loginAction()
    {
        //$password = password_hash($password, PASSWORD_DEFAULT);
        if ($_POST) {
            $username = trim($this->request->getPost('username', 'email'));
            $password = trim($this->request->getPost('password', 'string'));
            $captcha = $this->request->getPost('captcha', 'alphanum');
            $ipAddress = $this->request->getClientAddress();
            $userAgent = $this->request->getUserAgent();
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';


            // 安全检查 TODO::验证码
            if (!$this->authModel->checkIP($ipAddress)) {
                Utils::tips('warning', 'Login Failed Too Much Time');
            }


            // 查询用户
            $user = $this->authModel->getUser($username);
            if (!$user) {
                Utils::tips('warning', 'User Is Not Exist');
            }


            // 验证密码
            $verifyResult = password_verify($password, $user['password']);

            // 登录日志
            $log = array(
                'userID' => $user['id'],
                'IP' => $ipAddress,
                'location' => $this->utilsModel->getLocation($ipAddress),
                'userAgent' => $userAgent,
                'referer' => $referer,
                'result' => $verifyResult ? 1 : 0,
            );
            $this->authModel->logsLogin($log);

            if (!$verifyResult) {
                Utils::tips('warning', 'Password Error');
            }

            if ($user['status'] != 1) {
                Utils::tips('warning', 'The User Is Limited');
            }


            // 设置SESSION
            $this->session->set('userID', $user['id']);
            $this->session->set('username', $user['username']);
            $this->session->set('name', $user['name']);


            // 生成Ticket
            $ticket = $this->authModel->createTicket($user['id']);


            // 回调地址
            $redirect = urldecode(substr($referer, strpos($referer, 'redirect=') + 9));
            if (strpos($redirect, '?')) {
                $redirect .= '&ticket=' . $ticket;
            } else {
                $redirect .= '?ticket=' . $ticket;
            }
            header("Location:" . $redirect);
            exit();
        }


        $this->view->pick('sso/index');
    }


    public function logoutAction()
    {
        $this->session->destroy();
    }


    public function verifyAction()
    {
        $ticket = $this->request->get('ticket', 'string');
        $user = $this->authModel->getUserByTicket($ticket);
        if (!$user) {
            Utils::outputJSON(array('code' => 1, 'message' => 'failed'));
        }
        $user = array(
            'userID' => $user['id'],
            'username' => $user['username'],
            'name' => $user['name']
        );
        Utils::outputJSON(array('code' => 0, 'message' => 'success', 'data' => $user));
    }


    public function resourcesAction()
    {
        $app = $this->request->get('app', 'int');
        $ticket = $this->request->get('ticket', 'string');
        $user = $this->authModel->getUserByTicket($ticket);
        if (!$user) {
            Utils::outputJSON(array('code' => 1, 'message' => 'failed'));
        }

        $result['aclAll'] = $this->authModel->getAclResource(10000, $app);
        $result['aclAllow'] = $this->authModel->getAclResource($user['id'], $app);
        $result['menuTree'] = $this->utilsModel->list2tree($this->authModel->getResources($user['id'], $app));
        Utils::outputJSON(array('code' => 0, 'message' => 'success', 'data' => $result));
    }

}
