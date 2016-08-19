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


            // 安全检查 TODO::验证码
            if (!$this->authModel->checkIP($ipAddress)) {
                Utils::tips('notice', 'Login Failed Too Much Time');
            }


            // 查询用户
            $user = $this->authModel->getUser($username);
            if (!$user) {
                Utils::tips('notice', 'User Is Not Exist');
            }

            // 验证密码
            $verifyResult = password_verify($password, $user['password']);

            // 登录日志
            $log = array(
                'userID' => $user['id'],
                'IP' => $ipAddress,
                'location' => $this->utilsModel->getLocation($ipAddress),
                'userAgent' => $userAgent,
                'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
                'result' => $verifyResult ? 1 : 0,
            );
            $this->authModel->logsLogin($log);

            if (!$verifyResult) {
                Utils::tips('notice', 'Password Error');
            }


            // TODO::设置SESSION


        }
    }


    public function logoutAction()
    {
        $this->session->destroy();
    }


    public function verifyAction()
    {
    }


    public function resourcesAction()
    {
    }

}
