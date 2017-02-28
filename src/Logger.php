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
}
