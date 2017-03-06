#!/usr/bin/env php
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

if(php_sapi_name() != 'cli'){
    echo 'can only run in cli mode', PHP_EOL;
    usage();
}

require_once __DIR__.'/../vendor/autoload.php';

function usage(){
    echo <<<EOT
[Data crawler by wangXi. v0.1.0]
/path/to/cli.php
-url <site-url|etc:http://google.com>
-seed <src/Seed/[className]>
-max-depth <depth=3>
-db-mig=migrate|reset
-print
EOT;
    echo PHP_EOL;
    exit(0);
}

function normalize_args($_argv){
    $default_args=[
        'url'=> '',
        'seed'=>'',
        'max-depth'=>3,
        'db-mig'=>'',
        'print'=>false
    ];

    if(count($_argv) <2){
        usage();
    }

    $args=array_slice($_argv, 1);

    $parsed=[];
    foreach($args as $idx=> &$arg){ //todo: notice &
        if(in_array($idx, $parsed)){
            continue;
        }
        if(false !== strpos($arg, '-')){
            $arg=ltrim($arg, '-');
//            $arg=str_replace(['-', '--'], '', $arg);
            if(false !== strpos($arg, '=')){
                $arr=explode('=', $arg, 2);
                array_splice($args, $idx, 1, ['-'.$arr[0], $arr[1]]);
                $arg=$arr[0];
            }

            if(array_key_exists($arg, $default_args)){
                if($arg=='print'){
                    $default_args[$arg]=true;
                }
                else{
                    if(isset($args[$idx+1])){
                        $default_args[$arg]=trim($args[$idx+1]);
                        $parsed[]=$idx+1;
                    }
                }
            }
        }
    }

    return $default_args;
}

$params=normalize_args($argv);

$ns_prefix='wangxi\\Crawler\\';

if(isset($params['seed']) && !empty($params['seed'])) {
    $cls=$ns_prefix.'Seed\\'. trim($params['seed']);
    if(class_exists($cls)){
        $data=(array) call_user_func_array([new $cls, 'getMainData'], []);
        print_r($data);
    }
    else{
        echo $cls, 'does not exist';
    }
}
else if(isset($params['url']) && !empty($params['url'])){
    $url=trim($params['url']);
    if(strpos('http', $url)===false){
        $url='http://'.$url;
    }

    if(class_exists($site=$ns_prefix. 'Site')){
        $site=new $site;
        $site->setUrl($url);
        echo $site->getTitle();
    }
    else{
        echo 'class '.$site. ' not exists';
    }
}

else{
    usage();
}

