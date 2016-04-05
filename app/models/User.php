<?php

namespace MyApp\Models;

use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;

class User extends Model
{

    public function initialize()
    {
        $this->setConnectionService('data');
        $this->setSource("users");
    }

    public function get_user()
    {
        $sql = "SELECT * FROM users";
        $connection = DI::getDefault()->get('data');
        $result = $connection->query($sql);
        $result->setFetchMode(Db::FETCH_ASSOC);
        $result = $result->fetch();
        return $result;
    }

}
