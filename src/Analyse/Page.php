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
use wangxi\Crawler\Analyse\Excel;

require_once __DIR__.'/../../helpers/phpquery/phpQuery/phpQuery.php';

class Page
{
    const CK_INIT_PAGE_IDS='init_page_select_options';

    const PAGE_ITEM_EXPIRE=3600*6;

    protected $cache;
    protected $doc; //dom handler, DomDocument

    protected $initUrl='http://trade.500.com/bjdc/';

    protected static $analysePageUrls=[];   //每个数据分析页的url

    protected static $idUrlMap=[];  //页面参数id到次级页url的映射

    public function __construct()
    {
        $this->cache=Cache::init();

//        \phpQuery::$debug=true;
        \phpQuery::$defaultCharset='gbk';

        $init_page_cont=Cache::fetchByUrl($this->initUrl);
        $this->setDoc($init_page_cont);
    }

    /**
     * @param null $doc DomDocument| phpQueryObject
     */
    public function setDoc($doc=null)
    {
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

    public function getIdUrlMap()
    {
        return self::$idUrlMap;
    }

    public function getPageContentOfLevelOne($limit=0)
    {
        //todo:根据初始页面,拼接次级页面url
        //请求获取每一个次级页面的数据分析页的url,保存到url.txt
        $page_urls=[];

        $max_request=200;   //todo
        $retry_times=3;

        $page_ids=$this->getAllPageIds();

        if($limit>0){
            $page_ids=array_slice($page_ids, 0, $limit);
        }

        foreach($page_ids as $idx=> $page_id){
            $p_url=$idx == 0 ? $this->initUrl : $this->initUrl.'?expect='.$page_id;

            self::$idUrlMap[$page_id]=$p_url;
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

    public function analysePage($page_cont, $url)
    {
        $this->setDoc($page_cont);

        //todo 获取当前分析页的上级页面参数id作为excel文件id
        $entry_id=array_flip($this->getIdUrlMap())[$url];

        if(file_exists($file=__DIR__.'/../../doc/fenxi-'.$entry_id.'.xlsx')){
            Logger::info($file.' generated, ignore it');
            return;
        }

        //每一次分析, 一个excel对应该页面的所有抓取数据,excel的单个worksheet对应每一次url进入抓取
        $excel=new Excel();

        $max_limit=10;
        $i=0;
        $retry_times=3;
        foreach(pq('#vs_table .vs_lines') as $row){
            $req_url=pq($row)->find('td:eq(7) > a:first')->attr('href');
            Logger::info('analyse page: '.$req_url);

            if(!empty($req_url) && !array_key_exists($req_url, self::$analysePageUrls)){

                for($i=0; $i<$retry_times; $i++){
                    try{
                        $i>0 && Logger::info('retry '. ($i). ' times..');
                        $pg_cont=Cache::fetchByUrl($req_url, false);    //no need cache these page html

                        break;
                    }
                    catch(ConnectException $e){
                        ++$i;
                        continue;
                    }

                }

                if($i == $retry_times){
                    Logger::info('retry '.$retry_times.', ignore '.$req_url);
                    continue;
                }


                $this->setDoc($pg_cont);    //switch to current document

                $data=$this->getAnalysePageData();

                //收集单个analyse data,并添加到excel的一个worksheet
                $excel->createWorksheetFromAnalyseData($req_url, $data);

//                ++$i;
//                if($i>$max_limit){
//                    break;
//                }
            }
        }

        $excel->export($entry_id);
    }

    protected function dump($info, $data=null)
    {
        echo (string)$info, PHP_EOL;
        if(isset($data)){
            echo var_export($data, true), PHP_EOL;
        }
    }

    public function getAnalysePageData()
    {
        $d=[];  //return data

        //获取比赛的头部信息
        $t_head=pq('.odds_hd_cont')->eq(0);
        $t_head_info=[];

        $teams=$t_head->find('.odds_hd_list');
        if(!$teams->length){
            throw new Exception('比赛头部信息未找到');
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

        $d['head']=$t_head_info;
//        $this->dump('比赛头部信息', $t_head_info);

        //获取联赛积分排名
//        $jf_paimin_cols=['比赛', '胜', '平','负','进','失','净','积分','排名','胜率'];
//        $jf_paimin_rows=['总成绩','主场','客场'];
        $jf_box=pq('.M_box')->filter(function($idx, $cur){
            return strpos(trim(pq($cur)->find('.M_title')->text()), '赛前联赛') !== false;
        });

        $jf_box_info=[];
        $jf_content=pq($jf_box)->find('.M_content table');
        if(!$jf_content){
            throw new Exception('联赛积分排名未找到');
        }

        foreach($jf_box->find('.M_sub_title>.team_name') as $idx=> $item){
            $t_name=pq($item)->text();
            $jf_box_info[$t_name]=[];

            foreach(pq($jf_content)->eq($idx)->find('tr:gt(0)') as $i=> $row){
                $tds=pq($row)->find('td:gt(0)');
                foreach($tds as $td){
                    $jf_box_info[$t_name][$i][]=pq($td)->text();
                }
            }
        }
        $d['liansai']=$jf_box_info;
//        $this->dump('赛前联赛积分排名', $jf_box_info);

        //最近战绩, 主客场战绩
        $zj_info=$zj_zk_info=[];
        foreach(['team_a', 'team_b'] as $t_name){
            $selector='.M_box.record .'.$t_name.' .M_content .bottom_info>p';
            $elem=pq($selector);
            if(!$elem->length){
                throw new Exception($t_name.' 主客场战绩未找到');
            }

            foreach($elem as $j=>$item){
                $txt=pq($item)->text();
                if($j==0){
                    $zj_info[]=$txt;
                }
                else{
                    $zj_zk_info[]=$txt;
                }
            }
        }

        $d['zhuchang']=$zj_info;
//        $this->dump('主场战绩',$zj_info);

        $d['kechang']=$zj_zk_info;
//        $this->dump('客场战绩',$zj_zk_info);

        //平均数据
//        $pj_cols=['总平均数','主场','客场'];
//        $pj_rows=['平均入球','平均失球'];
        $pj_info=[];
        $pj_box=pq('.M_box.integral')->filter(':last');
        if(!$pj_box->length){
            throw new Exception('平均数据未找到');
        }

        foreach($pj_box->find('.M_sub_title>.team_name') as $k=>$team_name){
            $name=pq($team_name)->text();
            $pj_info[$name]=[];

            foreach(pq($pj_box)->find('.M_content table')->eq($k)->find('tr:gt(0)') as $i=>$row){
                $tds=pq($row)->find('td:gt(0)');
                foreach($tds as $td){
                    $pj_info[$name][$i][]=pq($td)->text();
                }
            }
        }

        $d['avg']=$pj_info;
//        $this->dump('平均数据', $pj_info);

        return $d;
    }
}
