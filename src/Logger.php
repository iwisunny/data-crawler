<?php
/**
 * Summary
 * Description
 * @package
 * @author    Wang Xi <iwisunny@gmail.com>
 * @copyright (C) 2017 Wang Xi. All rights reserved.
 * @version 0.1
 * Date 17-2-28
 */

namespace wangxi\Crawler;

class Logger
{
    protected static $debug=false;

    public static function setDebug($debug)
    {
        self::$debug=!!$debug;
    }

    public static function info($msg='')
    {
        //todo
        if(self::$debug){
            echo '[', date('Y-m-d H:i:s',time()),'] ', (string)$msg, PHP_EOL;
        }
    }

    public static function log($msg)
    {
        $log_file=date('Ymd', time()).'.log';
        $msg='['.date('Y-m-d H:i:s').'] '. var_export($msg, true). PHP_EOL;

        file_put_contents(__DIR__.'/../logs/'.$log_file, $msg, FILE_APPEND);
    }
}
