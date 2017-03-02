<?php
/**
 * Summary data analyse
 * Description
 * @package
 * @author    Wang Xi <iwisunny@gmail.com>
 * @copyright (C) 2017 Wang Xi. All rights reserved.
 * @version 0.1
 */
require __DIR__.'/../vendor/autoload.php';

use wangxi\Crawler\Perf;
use wangxi\Crawler\Analyse\Page;
use wangxi\Crawler\Logger;

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);
ini_set('memory_limit', '256M');

Perf::start();
Logger::setDebug(true);

$page=new Page();

$pages=$page->getPageContentOfLevelOne();

//todo: use multi process
foreach($pages as $url=> $pg){
    $page->analysePage($pg, $url);
}

Perf::summary();

