<?php

namespace MyApp\Models;

use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;
use GeoIp2\Database\Reader;

class Utils extends Model
{


    static public function tips($type = 'info', $message = '', $redirect = '')
    {
        $flash = json_encode(
            array(
                'type' => $type,
                'message' => $message,
                'redirect' => $redirect
            )
        );
        DI::getDefault()->get('cookies')->set('flash', $flash, time() + 10);
        DI::getDefault()->get('cookies')->send();
        header('Location:/tips');
        exit();
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
