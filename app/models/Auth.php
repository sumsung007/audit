<?php

namespace MyApp\Models;

use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;
use Phalcon\Security\Random;


/**
 * AuthController
 *
 * 本类仅用作管理后台调用,包含两部分
 * 1. 控制后台的RBAC权限控制
 * 2. 控制后台的SSO单点登录
 *
 * 勿将本类应用于用户前端业务
 * 前端用户数据交互可使用Users模型
 */
class Auth extends Model
{

    private $dbConnection;


    public function initialize()
    {
        $this->setConnectionService('dbData');
        $this->dbConnection = DI::getDefault()->get('dbData');
    }


    /**
     * 获取用户信息
     * @param string $username
     * @return mixed
     */
    public function getUser($username = '')
    {
        $sql = "SELECT * FROM `users` WHERE username=:username";
        $bind = array('username' => $username);
        $query = $this->dbConnection->query($sql, $bind);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetch();
        return $data;
    }


    /**
     * 用户信息 根据Ticket返回
     * @param string $ticket
     * @return mixed
     */
    public function getUserByTicket($ticket = '')
    {
        $dateTime = date('Y-m-d H:i:s', time() - 60);
        $sql = "SELECT u.* FROM `users` u, `tickets` t WHERE u.id=t.userID AND t.ticket=:ticket AND t.createdTime>'$dateTime'";
        $bind = array('ticket' => $ticket);
        // TODO :: 此处如使用$this->dbConnection时,外部程序使用file_get_contents(VerifyURL)调用时报错,直接访问VerifyURL没问题
        $query = DI::getDefault()->get('dbData')->query($sql, $bind);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetch();
        return $data;
    }


    /**
     * 插入登录日志
     * @param array $data
     */
    public function logsLogin($data = [])
    {
        $data['createdTime'] = date('Y-m-d H:i:s');
        $this->dbConnection->insertAsDict("logsLogin", $data);
    }


    /**
     * 检查登录失败次数
     * @param string $IP
     * @return bool
     */
    public function checkIP($IP = '')
    {
        $dateTime = date('Y-m-d H:i:s', time() - 600);
        $sql = "SELECT COUNT(1) count FROM `logsLogin` WHERE IP=:IP AND result=0 AND createdTime>'$dateTime'";
        $bind = array('IP' => $IP);
        $query = $this->dbConnection->query($sql, $bind);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetch();
        if ($data['count'] < 5) {
            return true;
        }
        return false;
    }


    /**
     * 生成票据Ticket
     * @param int $userID
     * @return mixed
     */
    public function  createTicket($userID = 0)
    {
        $random = new Random();
        $ticket = $random->base64Safe(64);
        $data = [
            'userID' => $userID,
            'ticket' => $ticket,
            'createdTime' => date('Y-m-d H:i:s')
        ];
        $this->dbConnection->insertAsDict("tickets", $data);
        return $ticket;
    }


    /**
     * 获取角色ID
     * @param int $userID
     * @return array
     */
    public function getRoleID($userID = 0)
    {
        $sql = "SELECT `roleID` FROM `userRole` WHERE userID=:userID";
        $bind = array('userID' => $userID);
        $query = DI::getDefault()->get('dbData')->query($sql, $bind);
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetchAll();
        if (!$data) {
            return [];
        }
        return array_column($data, 'roleID');
    }


    /**
     * 获取私有资源
     * @param int $userID
     * @param string $app
     * @return array
     */
    public function getResources($userID = 0, $app = '')
    {
        // 超级管理员
        if ($userID == 10000) {
            $sql = "SELECT res.id, res.name, res.resource, res.type, res.parent, res.icon
                FROM `resources` res
                WHERE res.status=1 AND res.app=:app
                ORDER BY res.sort DESC";
            $bind = array('app' => $app);
            $query = DI::getDefault()->get('dbData')->query($sql, $bind);
            $query->setFetchMode(Db::FETCH_ASSOC);
            return $query->fetchAll();
        }


        $roleID = $this->getRoleID($userID);
        if (!$roleID) {
            return [];
        }
        $roleID = '"' . implode('","', $roleID) . '"';
        $sql = "SELECT res.id, res.name, res.resource, res.type, res.parent, res.icon
                FROM `resources` res, `roleResource` rel
                WHERE rel.resourceID=res.id AND res.status=1 AND rel.roleID IN ($roleID) AND res.app=:app
                ORDER BY res.sort DESC";
        $bind = array('app' => $app);
        $query = DI::getDefault()->get('dbData')->query($sql, $bind);
        $query->setFetchMode(Db::FETCH_ASSOC);
        return $query->fetchAll();
    }


    /**
     * 获取私有资源acl格式
     * @param int $userID
     * @param string $app
     * @return array
     */
    public function getAclResource($userID = 0, $app = '')
    {
        $data = $this->getResources($userID, $app);
        if (!$data) {
            return [];
        }
        $result = [];
        foreach ($data as $value) {
            $resource = explode('/', trim($value['resource'], '/'));
            if (in_array($resource['0'], array('api', 'admin'))) {
                $controller = "{$resource['0']}/{$resource['1']}";
                $action = isset($resource['2']) ? $resource['2'] : 'index';
            } else {
                $controller = $resource['0'];
                $action = isset($resource['1']) ? $resource['1'] : 'index';
            }
            $result[$controller][] = $action;
        }
        return $result;
    }

}
