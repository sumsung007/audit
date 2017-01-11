<?php


$config = [
    'subject' => '2016q4',
    'from'    => '2016-10-01 00:00:00',
    'to'      => '2016-12-31 23:59:59',
    'audit'   => ['host' => '127.0.0.1', 'port' => 3306, 'db' => 'audit', 'user' => 'dev', 'pass' => 'dev123456'],
    'trade'   => [
        'de' => ['host' => '127.0.0.1', 'port' => 3306, 'db' => 'app_100', 'user' => 'dev', 'pass' => 'dev123456'],
        'pt' => ['host' => '127.0.0.1', 'port' => 3306, 'db' => 'app_100', 'user' => 'dev', 'pass' => 'dev123456'],
    ],
    'servers' => [
        '3001' => ['host' => '127.0.0.1', 'port' => 3306, 'db' => 'app_100', 'user' => 'dev', 'pass' => 'dev123456'],
    ],
];


return $config;