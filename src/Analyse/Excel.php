<?php
/**
 * Summary phpexcel wrapper for biz logic
 * Description
 * @package
 * @author    Wang Xi <iwisunny@gmail.com>
 * @copyright (C) 2017 Wang Xi. All rights reserved.
 * @version 0.1
 * Date 17-3-1
 */

namespace wangxi\Crawler\Analyse;

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet;
use wangxi\Crawler\Logger;

class Excel
{
    protected static $cachedExcelHandlers=[];

    const COLOR_YELLOW='00ffff00';
    const COLOR_MUD='00ededed';
    const COLOR_BLUE='00a0a0ff';

    /**
     * @var PHPExcel_Worksheet
     */
    protected $activeSheet;
    protected $currentRange;

    /**
     * @var PHPExcel
     */
    protected $excel;

    protected static $countSheet=0;

    public function __construct()
    {
        $this->excel=new PHPExcel();
        $this->excel->getProperties()->setCreator('WANG');

//        self::$cachedExcelHandlers[$this->excel->getID()]=$this->excel;
    }

    public function getExcel()
    {
        return $this->excel;
    }

    public function setActiveSheet(PHPExcel_Worksheet $ws)
    {
        $this->activeSheet=$ws;
    }

    public function setRange($range = 'a1:c1')
    {
        $this->currentRange=(string)$range;
        return $this;
    }

    public function setAutoFit($range=null, PHPExcel_Worksheet $ws=null)
    {
        $range=isset($range) ? $range : $this->currentRange;
        $ws=isset($ws) ? $ws : $this->activeSheet;

        $range_parts=explode(':', $range);
        $start=$range_parts[0][0];
        $end=$range_parts[1][0];

        for ($col = ord($start); $col <= ord($end); $col++) {
            $ws->getColumnDimension(chr($col))->setAutoSize(true);
        }

        return $this;
    }

    public function fillRangeColor($range=null, $color='00ffff00', PHPExcel_Worksheet $ws=null)
    {
        $range=isset($range) ? $range : $this->currentRange;
        $ws=isset($ws) ? $ws : $this->activeSheet;

        $ws->getStyle($range)
            ->getFill()
            ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB($color);

        $style = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
        ];

        $ws->getStyle($range)->applyFromArray($style);

        return $this;
    }

    public function fillData(array $data)
    {
        //set top head
        $sj_head=$data['head'];

        $this->setRange('a1:c2')
            ->fillRangeColor()->setAutoFit();

        $this->activeSheet->fromArray(['比赛名', '时间', '比分'], null, 'A1');

        $this->activeSheet->fromArray(array_values($sj_head['center']),null, 'A2');

        //赛前联赛积分排名
        $liansai_range='a4:k4'; //a-k
        foreach($data['liansai'] as $team_name=> $d){
            $rank_cols=['比赛', '胜', '平','负','进','失','净','积分','排名','胜率'];
            $rank_rows=['总成绩','主场','客场'];

            $this->setRange($liansai_range)
                ->fillRangeColor(null, self::COLOR_MUD)->setAutoFit();

            $cur_begin=$this->incrementRange($liansai_range,0,true);
            $this->activeSheet->setCellValue($cur_begin, '赛前联赛积分排名  '.$team_name);

            $this->setRange($this->incrementRange($liansai_range))
                ->fillRangeColor(null, self::COLOR_BLUE);

            array_unshift($rank_cols, ' ');

            $next_begin=$this->incrementRange($cur_begin);
            $this->activeSheet->fromArray($rank_cols, null, $next_begin); //列名
            $this->activeSheet->fromArray(array_chunk($rank_rows,1), null, $this->incrementRange($next_begin));

            //安排联赛数据起始位置
            $liansai_col_begin=$cur_begin[0];
            $liansai_row_begin=intval(str_replace($liansai_col_begin,'',$cur_begin));
            //row下移2, col右移1
            $liansai_col_begin=chr(ord($liansai_col_begin)+1);
            $liansai_row_begin+=2;

            $this->activeSheet->fromArray($d, null, strtoupper($liansai_col_begin. $liansai_row_begin));

            $liansai_range=$this->incrementRange($liansai_range, 6);
        }

        //主场战绩
        $zhuchang_range=$this->incrementRange($liansai_range,6);
        $this->setRange($zhuchang_range)
            ->fillRangeColor(null, self::COLOR_MUD)->setAutoFit();
        $cur_begin=$this->incrementRange($zhuchang_range,0,true);
        $this->activeSheet->setCellValue($cur_begin, '最近主场战绩');
        $next_begin=$this->incrementRange($cur_begin,0,true);
        $this->activeSheet->fromArray(array_chunk($data['zhuchang'],1), null, $this->incrementRange($next_begin));

        //客场战绩
        $kechang_range=$this->incrementRange($liansai_range,6+3);
        $this->setRange($kechang_range)
            ->fillRangeColor(null, self::COLOR_MUD)->setAutoFit();
        $cur_begin=$this->incrementRange($kechang_range,0,true);
        $this->activeSheet->setCellValue($cur_begin, '最近客场战绩');
        $next_begin=$this->incrementRange($cur_begin,0,true);
        $this->activeSheet->fromArray(array_chunk($data['kechang'],1), null, $this->incrementRange($next_begin));

        //平均战绩
        $avg_range='m4:p4'; //m-p
        foreach($data['avg'] as $team_name=> $d){
            $pj_cols=['总平均数','主场','客场'];
            $pj_rows=['平均入球','平均失球'];

            $this->setRange($avg_range)
                ->fillRangeColor(null, self::COLOR_MUD)->setAutoFit();

            $cur_begin=$this->incrementRange($avg_range,0,true);
            $this->activeSheet->setCellValue($cur_begin, '平均数据  '.$team_name);

            $this->setRange($this->incrementRange($avg_range))
                ->fillRangeColor(null, self::COLOR_BLUE);

            array_unshift($pj_cols, ' ');

            $next_begin=$this->incrementRange($cur_begin);
            $this->activeSheet->fromArray($pj_cols, null, $next_begin); //列名
            $this->activeSheet->fromArray(array_chunk($pj_rows,1), null, $this->incrementRange($next_begin));

            //安排平均数据起始位置
            $avg_col_begin=$cur_begin[0];
            $avg_row_begin=intval(str_replace($avg_col_begin,'',$cur_begin));
            //row下移2, col右移1
            $avg_col_begin=chr(ord($avg_col_begin)+1);
            $avg_row_begin+=2;

            $this->activeSheet->fromArray($d, null, strtoupper($avg_col_begin. $avg_row_begin));

            $avg_range=$this->incrementRange($avg_range, 5);
        }

    }

    public function createWorksheetFromAnalyseData($page_url, $data=[])
    {
        if(empty($data)){
            Logger::info('bad analyse data');
            return;
        }

        $page_id=substr($page_url, strrpos($page_url, '/')+1);
        if(strpos($page_id, '.') !==false){
            $page_id=strstr($page_id, '.', true);   //去掉后缀
        }

        $excel=$this->getExcel();

        ++self::$countSheet;

        $page_id=(self::$countSheet). '-'. str_replace('shuju','sj',$page_id);

        Logger::info('add sheet '.self::$countSheet. ': '.$page_id);

        //adjust first sheet
        if(self::$countSheet == 1){
            $this->setActiveSheet($excel->getSheet(0));
        }
        else{
            $sheet = new \PHPExcel_Worksheet($excel, $page_id);
            $excel->addSheet($sheet);
            $this->setActiveSheet($sheet);
        }

        $this->activeSheet->setTitle($page_id); //worksheet的底部标题

        $this->fillData($data);
    }

    //向下平移
    public function incrementRange($range, $step=1, $return_first=false)
    {
        if(false===strpos($range, ':')){
            $col_begin=$range[0];
            $row_begin=intval(str_replace($col_begin,'',$range));
            $row_begin += $step;

            return strtoupper($col_begin. $row_begin);
        }

        $parts=explode(':', $range);
        $col_begin=$parts[0][0];
        $col_end=$parts[1][0];
        //todo
        $row_begin=intval(str_replace($col_begin,'',$parts[0]));

        $row_begin += intval($step);

        if($return_first){
            return strtoupper($col_begin. $row_begin);
        }
        return strtoupper($col_begin. $row_begin. ':'.$col_end. $row_begin);
    }

    public function clearExcel(PHPExcel $excel)
    {
        $id=$excel->getID();
//        unset(self::$cachedExcelHandlers[$id]);

        //fixme: 避免phpexcel对象 循环引用造成内存溢出
        $excel->disconnectWorksheets();
        unset($excel);
    }

    /**
     * @param        $entry_id  入口页面的id
     * @param string $file_path
     * @throws \PHPExcel_Reader_Exception
     */
    public function export($entry_id, $file_path = '')
    {
        if(empty($file_path) || file_exists($file_path)){
            $file_path=__DIR__.'/../../doc/fenxi-'.$entry_id.'.xlsx';
        }
        $writer = PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
        $writer->save($file_path);
        Logger::info('save xlsx file: '. realpath($file_path));

        $this->clearExcel($this->excel);

        self::$countSheet=0;

    }
}
