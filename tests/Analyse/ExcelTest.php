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

namespace Tests\Analyse;

//use wangxi\Crawler\Analyse\Excel;
use PHPExcel;
use PHPExcel_IOFactory;
use wangxi\Crawler\Perf;
use wangxi\Crawler\Analyse\Page;
use wangxi\Crawler\Logger;

class ExcelTest extends \PHPUnit_Framework_TestCase
{
    public function test_excel_lib()
    {
        $doc_path=__DIR__.'/../../doc/';

        Logger::setDebug(true);

        //todo 生成单个分析页的excel数据
        $page=new Page();

        $pages_level_one=$page->getPageContentOfLevelOne(1);
//        dump(array_keys($pages_level_one));

        //todo: need use queue job
        foreach($pages_level_one as $pg){
            //now only analyse one page and quit
            $page->analysePage($pg);
        }

        $excel=new PHPExcel();
        $excel->getProperties()->setCreator('wang');

        $ws=$excel->getSheet(0);
//        $ws->setCellValue();
//        $excel->getSheetByName()
//        $excel->createSheet();
//        $excel->removeSheetByIndex();
//        $excel->getActiveSheet()->setCellValueByColumnAndRow()
        $ws->setTitle('sj-642484');

//        $ws->fromArray()

        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save($doc_path.'fenxi-170301.xlsx');

        //fixme: 避免循环引用造成内存溢出
        $excel->disconnectWorksheets();
        unset($objPHPExcel);

    }
}
