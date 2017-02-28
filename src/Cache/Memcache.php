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

extension_loaded('memcached') or die('need memcached extension');

use Memcached;

class Memcache implements CacheInterface
{
    private static $serverId=0;
    private static $instance;

    public function __construct()
    {
        //connect server
        if(!isset(self::$instance)){
            self::$instance=new Memcached(++self::$serverId);

            //fixme: when append, need OPT_COMPRESSION=false
            self::$instance->setOption(Memcached::OPT_COMPRESSION, false);
        }

        $servers=self::$instance->getServerList();
        if(!count($servers)){
            self::$instance->addServers(config('cache.memcache'));
        }

        return self::$instance;
    }

    public function __call($name, $args)
    {
        return call_user_func_array([self::$instance, $name], $args);
    }

    public function get($key)
    {
        return self::$instance->get($key);
    }

    public function set($key, $data, $expire=0)
    {
        return self::$instance->set($key, $data, $expire);
    }

    public function del($key)
    {
        return self::$instance->delete($key);
    }

    public function all()
    {
        return self::$instance->fetchAll();
    }

    public function flush()
    {
        return self::$instance->flush();
    }

    public function has($key)
    {
        return self::$instance->get($key) !== false;
    }

}
