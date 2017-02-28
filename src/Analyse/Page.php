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

namespace wangxi\Crawler\Analyse;

use wangxi\Crawler\Cache\Cache;
use wangxi\Crawler\Logger;
use GuzzleHttp\Exception\ConnectException;
use Exception;

require_once __DIR__.'/../../helpers/phpquery/phpQuery/phpQuery.php';

class Page
{
    const CK_INIT_PAGE_IDS='init_page_select_options';

    const PAGE_ITEM_EXPIRE=3600*6;

    protected $cache;
    protected $doc; //dom handler, DomDocument

    protected $initUrl='http://trade.500.com/bjdc/';

    protected static $analysePageUrls=[];

    public function __construct()
    {
        $this->cache=Cache::init();

//        \phpQuery::$debug=true;
        \phpQuery::$defaultCharset='gbk';

        $init_page_cont=Cache::fetchByUrl($this->initUrl);
        $this->setDoc($init_page_cont);

        Logger::setDebug(true);
    }

    /**
     * @param null $doc DomDocument| phpQueryObject
     */
    public function setDoc($doc=null)
    {
//        \phpQuery::$defaultCharset='gbk';
        if(is_string($doc)){
//            $charset=mb_detect_encoding($doc, 'UTF8, GBK, EUC-CN, ISO-8859-1, AUTO', true);
//            if($charset){
////                echo $charset;
//                $doc=mb_convert_encoding($doc, 'UTF-8', $charset);
//            }

//            echo $doc;exit;

//            $doc=\phpQuery::newDocument($doc, 'text/html; charset=utf-8');
            $doc=\phpQuery::newDocument($doc);
        }

        $this->doc=$doc;
//        echo $doc->html();
//        \phpQuery::selectDocument($this->doc);
    }

    public function setInitUrl($url)
    {
        $this->initUrl=$url;
    }

    public function getAllPageIds()
    {
        $select_opts=$this->cache->get(self::CK_INIT_PAGE_IDS);
        if(false===$select_opts){
            $select_opts=[];
            foreach(pq('#expect_select > option') as $opt){
                $select_opts[]=explode('|', pq($opt)->val())[0];
            }
            usort($select_opts, function($a,$b){
                return intval($a) - intval($b) < 0;
            });

            $this->cache->set(self::CK_INIT_PAGE_IDS, $select_opts, self::PAGE_ITEM_EXPIRE);
        }
        return $select_opts;
    }

    /**
     *
     */
    public function getPageContentOfLevelOne($limit=0)
    {
        //todo:根据初始页面,拼接次级页面url
        //请求获取每一个次级页面的数据分析页的url,保存到url.txt
        $page_urls=[];
        $max_request=200;
        $retry_times=3;

        $page_ids=$this->getAllPageIds();

        if($limit>0){
            $page_ids=array_slice($page_ids, 0, $limit);
        }

        foreach($page_ids as $idx=> $page_id){
            //todo
            $p_url=$idx == 0 ? $this->initUrl : $this->initUrl.'?expect='.$page_id;

            //send request to fetch data-analyse link on each page
            //$site_url='http://odds.500.com/fenxi/shuju-615908.shtml';
            Logger::info('fetching page: '.($idx+1). ', '. $p_url);

            try{
                if($idx<$max_request){
                    $page_urls[$p_url]=Cache::fetchByUrl($p_url);
                }
                else{
                    Logger::info('exceeding max request limit');
                    break;
                }
            }
            catch(ConnectException $conn_err){
                Logger::info($conn_err->getMessage().', reconnect..');
                for($i=0; $i<$retry_times; $i++){
                    try{
                        Logger::info('retry '. ($i+1). ' times..');
                        $page_urls[$p_url]=Cache::fetchByUrl($p_url);
                        break;
                    }
                    catch(ConnectException $e){
                        ++$i;
                        continue;
                    }
                }
                Logger::info('retry limit, ignore this request');
            }
            catch(Exception $e){
                Logger::info($e->getMessage().', ignore this request');
                continue;
            }
        }

        return $page_urls;
    }

    public function analysePage($page_cont)
    {
        $this->setDoc($page_cont);

        foreach(pq('#vs_table .vs_lines') as $row){
            $req_url=pq($row)->find('td:eq(7) > a:first')->attr('href');
            Logger::info('analyse page: '.$req_url);

            if(!empty($req_url) && !array_key_exists($req_url, self::$analysePageUrls)){
//                self::$analysePageUrls[$req_url]=[];
                $pg_cont=Cache::fetchByUrl($req_url, false);    //fixme

                $this->setDoc($pg_cont);
                $this->getAnalysePageData();

                break;

            }
        }

        //cache analysePageUrl
    }

    public function getAnalysePageData()
    {
        //获取比赛的头部信息
        $t_head=pq('.odds_hd_cont')->eq(0);
        $t_head_info=[];
//        dump($t_head->length);

        $teams=$t_head->find('.odds_hd_list');
        if(!$teams->length){
            throw new Exception('invalid teams');
        }

        $team_left=$teams->eq(0);
        $team_right=$teams->eq(1);
        $center_info=$t_head->find('.odds_hd_center');

        $tn=$team_left->children('li:first-child')->text();

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
                $tds=pq($row)->find('td')->filter(function($idx){
                    return $idx>0;
                });

                foreach($tds as $td){
                    $jf_box_info[$t_name][$i][]=pq($td)->text();
                }
            }
        }

        print_r($jf_box_info);

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

        print_r($zj_info);
        print_r($zj_zk_info);

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

    }
}
