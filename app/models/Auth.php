<?php

namespace MyApp\Models;

use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;

class Auth extends Model
{

    private $dbConnection;


    public function initialize()
    {
        $this->setConnectionService('dbData');
        $this->setSource("users");
        $this->dbConnection = DI::getDefault()->get('dbData');
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
        $query = $this->dbConnection->query($sql, $bind);
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
        $query = $this->dbConnection->query($sql, $bind);
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
