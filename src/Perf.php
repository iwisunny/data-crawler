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

class Perf
{
    protected static $labelPoints=[];
    protected static $cntLabel=0;

    public static function start()
    {
        defined('_START') or define('_START', microtime(true));
    }

    public static function label($label_name='')
    {
        if(empty($label_name)){
            $label_name=substr(md5('data-crawler'), 0, 6).'-'. (++self::$cntLabel);
        }

        if(!isset(self::$labelPoints[$label_name])){
            self::$labelPoints[$label_name]=microtime(true);
        }

        return $label_name;
    }

    public static function summary()
    {
        echo PHP_EOL, PHP_EOL;
        print_r([
            'cpu_time'=> round(microtime(true) - _START, 3). ' Sec',
            'peak_memory'=> floatval(memory_get_peak_usage(true)/1024/1024).' MB',
            'current_memory'=> floatval(memory_get_usage(true)/1024/1024).' MB'
        ]);
    }
}
