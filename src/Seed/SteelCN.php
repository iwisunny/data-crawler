<?php
/**
 * Summary
 * Description
 * @package
 * @author    Wang Xi <iwisunny@gmail.com>
 * @copyright (C) 2017 Wang Xi. All rights reserved.
 * @version 0.1
 * Date 17-2-16
 */

namespace wangxi\Crawler\Seed;

use wangxi\Crawler\SiteParser;

class SteelCN extends SiteParser
{
    protected $siteUrl='http://baojia.steelcn.cn/';

    //钢材类型
    protected $types=[
        '卷板',
        '中板',
        '建材',
        '大中型材',
        'H型钢',
        '无缝管',
        '直缝焊管',
        '结构钢'
    ];

    public function getMainData()
    {
        $elems=[];

        //解析报价表格
        $tables=$this->query('//table[@id="table"]');

        foreach($tables as $idx=> $tb){
            $bj=[];

            //解析表头
            $theads=$this->query('thead/tr/th', $tb);
            $len_heads=$theads->length;
            $cnt_city=$len_heads-2; //todo 去掉品名和日期列

            foreach($theads as $th){
                $bj['city'][]=trim($th->nodeValue);
            }

            $bj['city']=array_slice($bj['city'], 2);

            //fixme: 解析body
            $trows=$this->query('tr/td', $tb);
            $len_rows=$trows->length;

            $cur_prod_name='';  //当前品名
            $idx_prod_name=0;
            $cur_day='';
            $row_key='';

            foreach($trows as $i=> $td){
                $val=trim($td->nodeValue);

                if($i % ($len_heads*2 -1) ==0){
                    $idx_prod_name=$i;
                    $cur_prod_name=$val;

                    $row_key=implode('_', ['row', substr(md5($cur_prod_name), 0, 6), $cur_prod_name]);
                    if(!isset($bj[$row_key])){
                        $bj[$row_key]=[];
                    }
                    continue;
                }

                $delta=$i-$idx_prod_name;

                if($delta== 1 || $delta==$len_heads){
                    //日期标志
                    $cur_day=$val;
                    $bj[$row_key][$cur_day]=[];
                }
                else{
                    $bj[$row_key][$cur_day][]=$val;
                }

            }

            $elems['baojia_'.$this->types[$idx]]=$bj;
        }

        return $this->setMain($elems);
    }
}
