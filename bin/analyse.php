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

use GuzzleHttp\Client;
use wangxi\Crawler\Perf;
use wangxi\Crawler\Cache\Cache;
use wangxi\Crawler\Analyse\Page;
use wangxi\Crawler\Logger;

set_time_limit(0);

Perf::start();
Logger::setDebug(true);

mb_internal_encoding('UTF-8');

//phpQuery::$debug=true;
phpQuery::$defaultCharset='gbk';

$cache=Cache::init();
$cache->flush();

$page=new Page();

$pages_level_one=$page->getPageContentOfLevelOne(1);
//dump(array_keys($pages_level_one));

foreach($pages_level_one as $pg_url=> $pg_cont){
    $page->analysePageUrl($pg_cont);
}

//script prof
Perf::summary();

