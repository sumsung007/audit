<?php

namespace MyApp\Models;

use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;

class Demo extends Model
{

    private $dbConnectionData;

    public function initialize()
    {
        $this->setConnectionService('data');
        $this->setSource("users");
        $this->dbConnectionData = DI::getDefault()->get('data');
    }


    // 写操作
    // link https://docs.phalconphp.com/zh/latest/reference/db.html#binding-parameters
    public function demo()
    {
        // 写操作 SQL占位符 insert update delete
        $sql = "INSERT INTO `robots`(`name`, `year`) VALUES (?, ?)";
        $sql = "UPDATE `robots` SET `name` = ? WHERE `id` = ?";
        $sql = "DELETE FROM `robots` WHERE `name`=? AND `id` = ?";
        $success = $this->dbConnectionData->execute($sql, array('JoeChu', 1987));


        // 可用以下操作替换上述方法


        // 插入 方法一
        $success = $this->dbConnectionData->insert(
            "robots",
            array("JoeChu", 1987),
            array("name", "year")
        );


        // 插入 方法二
        $success = $this->dbConnectionData->insertAsDict(
            "robots",
            array(
                "name" => "JoeChu",
                "year" => 1987
            )
        );


        // 更新 方法一
        $success = $this->dbConnectionData->update(
            "robots",
            array("name"),
            array("JoeChu"),
            array(
                'conditions' => 'id = ?',
                'bind' => array(101),
                'bindTypes' => array(PDO::PARAM_INT) // Optional parameter
            )
        );


        // 更新 方法二
        $success = $this->dbConnectionData->updateAsDict(
            "robots",
            array(
                "name" => "JoeChu"
            ),
            array(
                'conditions' => 'id = ?',
                'bind' => array(101),
                'bindTypes' => array(PDO::PARAM_INT) // Optional parameter
            )
        );


        // 删除
        $success = $this->dbConnectionData->delete("robots", "id = ?", array(101));
    }


    // 读操作
    public function findDemo()
    {
        // link https://docs.phalconphp.com/zh/latest/reference/db.html#binding-parameters
        $sql = "SELECT * FROM users WHERE username=:username";
        $bind = array('username' => 'demo@xxtime.com');
        $query = $this->dbConnectionData->query($sql, $bind); //$query->numRows();
        $query->setFetchMode(Db::FETCH_ASSOC);
        $data = $query->fetchAll(); // fetch
        dump($sql, $data);
    }


    // 事务
    public function transactionsDemo()
    {

        try {
            // 开始一个事务
            $this->dbConnectionData->begin();

            // 执行一些操作
            $this->dbConnectionData->execute("DELETE `robots` WHERE `id` = 101");
            $this->dbConnectionData->execute("DELETE `robots` WHERE `id` = 102");
            $this->dbConnectionData->execute("DELETE `robots` WHERE `id` = 103");

            // 提交操作，如果一切正常
            $this->dbConnectionData->commit();

        } catch (Exception $e) {
            // 如果发现异常，回滚操作
            $this->dbConnectionData->rollback();
        }

    }

}
