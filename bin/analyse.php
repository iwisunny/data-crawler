<?php
/**
 * Summary data analyse
 * Description
 * @package
 * @author    Wang Xi <iwisunny@gmail.com>
 * @copyright (C) 2017 Wang Xi. All rights reserved.
 * @version 0.1
 * Date 17-2-27
 */
require __DIR__.'/../vendor/autoload.php';

use wangxi\Crawler\Perf;
use wangxi\Crawler\Cache\Cache;
use wangxi\Crawler\Analyse\Page;
use wangxi\Crawler\Logger;

Perf::start();
Logger::setDebug(true);

//$cache=Cache::init();

$page=new Page();

$pages_level_one=$page->getPageContentOfLevelOne(50);
//dump(array_keys($pages_level_one));

foreach($pages_level_one as $pg){
    $page->analysePage($pg);
}

Perf::summary();

