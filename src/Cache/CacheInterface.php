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

interface CacheInterface
{
    public function get($key);
    public function set($key, $data, $expire=0);
    public function del($key);
    public function all();
    public function flush();
    public function has($key);
}
