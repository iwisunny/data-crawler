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
use wangxi\Crawler\Cache\Memcache;

Perf::start();

//phpQuery::$debug=true;

$http=new Client();

$mmc=Memcache::init();

//$mmc->flush();
//$mmc->set('foo', 'bat');
//$mmc->getDelayed(['foo']);
//dump($mmc->getServerList());

//todo: save page urls to url.txt
$init_url='http://trade.500.com/bjdc/'; //初始的url

$site_key=md5($init_url);
$site_cont=$mmc->get($site_key);

if(false===$site_cont){
    $res=$http->get($init_url);
    $status_code=$res->getStatusCode();
    if($status_code>=300){
        throw new Exception('request failed');
    }
    $site_cont=$res->getBody();
    if(!is_string($site_cont)){
        $site_cont=$site_cont->getContents();
    }

//    $site_cont=mb_convert_encoding($site_cont,'utf-8', 'gbk');    //fixme: dont do this
//    file_put_contents(__DIR__.'/doc.html', $site_cont, LOCK_EX);

    $mmc->set($site_key, $site_cont, 3600);
}

//fixme: since site encode is gb2312
$doc=phpQuery::newDocument($site_cont, 'text/html;charset=utf-8');

//$xpath=new DOMXPath($doc->document);
//dump($xpath->query('//div[@class="b-top"]/select'));

$select_opts=[];
foreach(pq('#expect_select > option') as $opt){
    $v=pq($opt)->val();
    $v=explode('|', $v)[0];
    $select_opts[]=$v;
}
usort($select_opts, function($a,$b){
    return intval($a) - intval($b) < 0;
});
dump($select_opts);

//$site_url='http://odds.500.com/fenxi/shuju-615908.shtml';


//获取比赛的头部信息
$t_head=pq('.odds_hd_cont')->eq(0);
$t_head_info=[];
dump($t_head->length);

$teams=$t_head->find('.odds_hd_list');
if(!$teams->length){
    throw new Exception('invalid teams');
}

$team_left=$teams->eq(0);
$team_right=$teams->eq(1);
$center_info=$t_head->find('.odds_hd_center');

$t_head_info['left']=[
  'team_name'=> $team_left->children('li:first-child')->text(),
  'desc'=> $team_left->children('li:last-child')->text()
];

$t_head_info['right']=[
    'team_name'=> $team_right->children('li:first-child')->text(),
    'desc'=> $team_right->children('li:last-child')->text()
];

$t_head_info['center']=[
  'game_name'=> $center_info->find('.hd_name')->text(),
  'game_time'=> $center_info->find('.game_time')->text(),
  'game_score'=> $center_info->find('.odds_hd_bf')->text()
];

print_r($t_head_info);

//获取联赛积分排名
$jf_paimin_cols=['比赛', '胜', '平','负','进','失','净','积分','排名','胜率'];
$jf_paimin_rows=['总成绩','主场','客场'];
$jf_box=pq('.M_box')->eq(0);
$jf_box_info=[];
$jf_content=pq($jf_box)->find('.M_content>div>table');
foreach($jf_box->find('.M_sub_title>.team_name') as $idx=> $item){
    $t_name=pq($item)->text();
    $jf_box_info[$t_name]=[];

    foreach(pq($jf_content)->eq($idx)->find('tr')->filter(function($idx){
        return $idx>0;
    }) as $i=>$row){
//        dump(pq($row)->length);
        $tds=pq($row)->find('td')->filter(function($idx){
            return $idx>0;
        });
//        dump($tds->length);
        foreach($tds as $td){
            $jf_box_info[$t_name][$i][]=pq($td)->text();
        }
    }
}

//print_r($jf_box_info);

//最近战绩, 主客场战绩
$zj_info=$zj_zk_info=[];
foreach(['team_a', 'team_b'] as $t_name){
    $selector='.M_box.record .'.$t_name.' .M_content .bottom_info>p';
    foreach(pq($selector) as $j=>$item){
        $txt=pq($item)->text();
        if($j==0){
            $zj_info[]=$txt;
        }
        else{
            $zj_zk_info[]=$txt;
        }
    }
}
//print_r($zj_info);
//print_r($zj_zk_info);

//平均数据
$pj_cols=['总平均数','主场','客场'];
$pj_rows=['平均入球','平均失球'];
$pj_info=[];
$pj_box=pq('.M_box.integral')->filter(':last');
foreach($pj_box->find('.M_sub_title>.team_name') as $k=>$team_name){
    $name=pq($team_name)->text();
    $pj_info[$name]=[];

    foreach(pq($pj_box)->find('.M_content table')->eq($k)->find('tr')->filter(function($idx){
        return $idx>0;
    }) as $i=>$row){
        $tds=pq($row)->find('td')->filter(function($idx){
            return $idx>0;
        });
        foreach($tds as $td){
            $pj_info[$name][$i][]=pq($td)->text();
        }
    }
}
print_r($pj_info);

//script prof
Perf::summary();

