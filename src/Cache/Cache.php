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

use GuzzleHttp\Client;
use Exception;
use wangxi\Crawler\Logger;

class Cache
{
    /**
     * @var string
     */
    protected static $driver;

    /**
     * @var CacheInterface
     */
    protected static $obj;

    protected static $acceptDriver=['file', 'memcache', 'redis'];

    const CONN_TIME_OUT=5;

    public static function init($driver='memcache')
    {
        if(in_array($driver, self::$acceptDriver)){
            self::$driver=$driver;
        }
        else{
            self::$driver='memcache';
        }

        $ns_prefix=substr(self::class, 0, strrpos(self::class, '\\'));

        if(class_exists($class=$ns_prefix.'\\'. ucfirst(self::$driver))){
            if(!isset(self::$obj)){
                self::$obj=new $class();
            }
        }
        else{
            throw new \Exception('class: '. $class.' not exists');
        }

        return self::$obj;
    }

    //biz related
    public static function fetchByUrl($url, $need_cache=true)
    {
        //todo: validate url
        if(empty($url) || strpos($url, 'http')===false){
            throw new Exception('invalid url');
        }

        $site_key=cryptKey($url);
        $site_cont=self::$obj->get($site_key);

        if(false===$site_cont){
            Logger::info('cache miss, '. $url);

            $http=new Client();

            $res=$http->get($url,[
                'timeout'=>self::CONN_TIME_OUT
            ]);

            $status_code=$res->getStatusCode();
            if($status_code>=400){
                throw new Exception('request failed');
            }
            $site_cont=$res->getBody();
            if(is_object($site_cont)){
                $site_cont=$site_cont->getContents();
            }

            if($need_cache){
                self::$obj->set($site_key, $site_cont, 3600*6);
            }

        }

        return $site_cont;
    }


}
