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

namespace Tests;

class HelperTest extends \PHPUnit_Framework_TestCase
{
    public function test_common_helper()
    {
        $cache_conf=config('cache');
        dump($cache_conf);
    }
}
