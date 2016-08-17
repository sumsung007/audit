<?php

namespace MyApp\Plugins;

use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Acl\Adapter\Memory as AclList;
use Phalcon\Events\Event;
use MyApp\Models\Auth;

// https://docs.phalconphp.com/zh/latest/reference/dispatching.html
// https://docs.phalconphp.com/zh/latest/api/Phalcon_Mvc_Dispatcher.html
class SecurityPlugin extends Plugin
{


    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        return $this->checkPermission($event, $dispatcher);
    }


    public function beforeException(Event $event, Dispatcher $dispatcher)
    {
        global $config;
        if (!$config->setting->appDebug) {
            $dispatcher->forward([
                'namespace' => 'MyApp\Controllers',
                'controller' => 'errors',
                'action' => 'show404'
            ]);
            return false;
        }
    }


    private function getAcl()
    {
        if (isset($this->persistent->acl)) {
            return $this->persistent->acl;
        }


        // ACL
        $acl = new AclList();
        $acl->setDefaultAction(Acl::DENY);


        // 用户ID
        $userID = $this->session->get('userID');


        // 定义角色
        $roleList = array(
            new Role('Guests', 'Guest users'),
            new Role('Users', 'Member users'),
            new Role('Admins', 'Guest users')
        );


        // 资源
        $publicResources = array(
            'index' => array('index'),
            'public' => array('login'),
            'public' => array('logout'),
            'about' => array('index'),
            'contact' => array('index'),
            'errors' => array('show401', 'show404', 'show500')
        );
        $authModel = new Auth();
        $privateResources = $authModel->getAclResource($userID);
        $allResources = $authModel->getAclResource(10000);


        // 添加角色和资源
        foreach ($roleList as $role) {
            $acl->addRole($role);
        }
        foreach ($publicResources as $resource => $actions) {
            $acl->addResource(new Resource($resource), $actions);
        }
        foreach ($allResources as $resource => $actions) {
            $acl->addResource(new Resource($resource), $actions);
        }


        // 公共权限 Guests
        foreach ($roleList as $role) {
            foreach ($publicResources as $resource => $actions) {
                foreach ($actions as $action) {
                    $acl->allow($role->getName(), $resource, $action);
                }
            }
        }


        // 私有权限 Users
        foreach ($privateResources as $resource => $actions) {
            foreach ($actions as $action) {
                $acl->allow('Users', $resource, $action);
            }
        }


        // 管理员 Admins
        foreach ($allResources as $resource => $actions) {
            foreach ($actions as $action) {
                $acl->allow('Admins', $resource, $action);
            }
        }


        $this->persistent->acl = $acl;


        return $this->persistent->acl;
    }


    private function checkPermission(Event $event, Dispatcher $dispatcher)
    {
        $acl = $this->getAcl();
        $userID = $this->session->get('userID');
        if (!isset($userID)) {
            $role = 'Guests';
        } else {
            $role = 'Users';
        }


        $namespaceName = $dispatcher->getNamespaceName();
        if ($namespaceName != 'MyApp\Controllers') {
            $prefix = strtolower(substr($namespaceName, strrpos($namespaceName, '\\') + 1));
            $controller = $prefix . '/' . $dispatcher->getControllerName();
        } else {
            $controller = $dispatcher->getControllerName();
        }
        $action = $dispatcher->getActionName();


        // 无权限
        if ($acl->isResource($controller) != $acl->isAllowed($role, $controller, $action)) {
            $dispatcher->forward([
                'namespace' => 'MyApp\Controllers',
                'controller' => 'errors',
                'action' => 'show401'
            ]);
            return false;
        }
    }


}
