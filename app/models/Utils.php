<?php

namespace MyApp\Models;

use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;
use GeoIp2\Database\Reader;

class Utils extends Model
{


    static public function tips($type = 'info', $message = '', $seconds = 0, $redirect = '')
    {
        $flash = json_encode(
            array(
                'type' => $type,
                'message' => $message,
                'seconds' => !empty($seconds) ? $seconds : 3,
                'redirect' => $redirect ? $redirect : 'javascript:history.back(-1)'
            )
        );
        DI::getDefault()->get('cookies')->set('flash', $flash, time() + 30);
        DI::getDefault()->get('cookies')->send();
        header('Location:/tips');
        exit();
    }


    static public function outputJSON($data = [])
    {
        header("Content-type:application/json; charset=utf-8");
        exit(json_encode($data, JSON_UNESCAPED_UNICODE));
    }


    public function getLocation($ipAddress = '')
    {
        if (in_array($ipAddress, ['127.0.0.1'])) {
            return;
        }
        if (!file_exists(APP_DIR . '/config/GeoLite2-City.mmdb')) {
            return;
        }
        $reader = new Reader(APP_DIR . '/config/GeoLite2-City.mmdb');
        $record = $reader->city($ipAddress);
        $location = $record->country->names['zh-CN'] . ' ' . $record->mostSpecificSubdivision->names['zh-CN'] . ' ' . $record->city->names['zh-CN'];
        $location .= ' ' . $record->location->latitude . ' ' . $record->location->longitude;
        return $location;
    }

}
