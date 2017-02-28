<?php
/**
 * Summary  common helper
 * Description
 * @package
 * @author    Wang Xi <iwisunny@gmail.com>
 * @copyright (C) 2017 Wang Xi. All rights reserved.
 * @version 0.1
 * Date 17-2-15
 */
function dump($val)
{
    if(is_array($val)){
        print_r($val);
    }
    else{
        var_dump($val);
    }
    exit;
}

function config($conf_item='', $default=[])
{
    $base_path=realpath(__DIR__.'/../config/');
    $dp=opendir($base_path);
    $confs=[];
    if($dp){
        while($file=readdir($dp)){
            if($file !=='.' && $file !== '..'){
                $file_name=strstr($file, '.php', true);
                $confs[$file_name]=require $base_path.'/'.$file_name.'.php';
            }
        }
    }
    closedir($dp);

    if(!empty($conf_item)){
        if(false !== strpos($conf_item, '.')){
            $parts=explode('.', $conf_item);
            return isset($confs[$parts[0]][$parts[1]]) ? $confs[$parts[0]][$parts[1]] : $default;
        }
        else{
            return isset($confs[$conf_item]) ? $confs[$conf_item] : $default;
        }
    }
    return $confs;
}
