<?php
/**
 * Summary
 * Description
 * @package
 * @author    Wang Xi <iwisunny@gmail.com>
 * @copyright (C) 2017 Wang Xi. All rights reserved.
 * @version 0.1
 * Date 17-2-15
 */

namespace Tests\Seed;

use wangxi\Crawler\Seed\SteelCN;

class SteelCNTest extends \PHPUnit_Framework_TestCase
{
    public function testSteel()
    {
        $site=new SteelCN();

        $this->assertNotEmpty($site->getTitle());
        $this->assertNotEmpty($site->getMain());

        echo 'site title: ', $site->getTitle(), PHP_EOL;

        dump($site->getMain());
    }

}
