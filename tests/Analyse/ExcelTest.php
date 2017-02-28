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

use wangxi\Crawler\Analyse\Excel;
use PHPExcel;

class ExcelTest extends \PHPUnit_Framework_TestCase
{
    public function test_excel_lib()
    {
        $excel=new PHPExcel();
        var_dump($excel);
    }
}
