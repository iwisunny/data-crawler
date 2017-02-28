<?php
/**
 * Summary
 * Description
 * @package
 * @author    Wang Xi <iwisunny@gmail.com>
 * @copyright (C) 2017 Wang Xi. All rights reserved.
 * @version 0.1
 * Date 17-2-27
 */

extension_loaded('memcached') or die('need memcached ext');

$m=new Memcached('mmc');
$m->addServer('127.0.0.1', 7036);

//$m->setOption(Memcached::OPT_COMPRESSION, false);
//print_r(get_class_methods($m));
//print_r($m->getServerList());
//print_r($m->fetchAll());

//$m->set('name', 'wangxi');
//echo $m->get('name');
//$m->append('name', 'love php');
//$m->getDelayed(['name']);
//$res=$m->fetchAll();
//if(false===$res){
//    echo $m->getResultCode(), $m->getResultMessage();
//}
//else{
//    print_r($res);
//}


$keys=$m->getAllKeys();
if(false===$keys){
    echo $m->getResultCode(), $m->getResultMessage();
}
var_dump($keys);
//$m->getDelayed($keys);
//print_r($m->fetchAll());
