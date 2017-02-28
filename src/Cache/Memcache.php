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

namespace wangxi\Crawler\Cache;

use Memcached;

class Memcache
{
    private static $serverId=0;
    private static $instance;

    public static function init($id='')
    {
        extension_loaded('memcached') or die('need memcached extension');

        if(!isset(self::$instance)){
            self::$instance=new Memcached($id ? $id : ++self::$serverId);
            self::$instance->setOption(Memcached::OPT_COMPRESSION, false);
        }

        $servers=self::$instance->getServerList();
        if(!count($servers)){
            self::$instance->addServers(config('cache.memcached'));
        }

        return self::$instance;
    }


    private function __construct()
    {

    }

    private function __clone()
    {

    }
}
