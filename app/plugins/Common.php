<?php
/**
 * Package  Common.php
 * Author:  joe@xxtime.com
 * Date:    2015-07-20
 * Time:    上午12:43
 * Link:    http://www.xxtime.com
 */

function debug()
{
    echo "<meta charset='UTF-8'><pre style='padding:20px; background: #000000; color: #FFFFFF;'>\r\n";
    if (func_num_args()) {
        foreach (func_get_args() as $k => $v) {
            echo "------- Debug $k -------<br/>\r\n";
            print_r($v);
            echo "<br/>\r\n";
        }
    }
    echo '</pre>';
    exit;
}

function write_log($log = '', $file = 'debug.txt')
{
    global $config;
    $log_file = APP_PATH . $config->application->logsDir . $file;
    $handle = fopen($log_file, "a+b");
    $text = date('Y-m-d H:i:s') . ' ' . $log . "\r\n";
    fwrite($handle, $text);
    fclose($handle);
}
