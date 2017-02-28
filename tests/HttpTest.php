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

use GuzzleHttp\Client;

class HttpTest extends \PHPUnit_Framework_TestCase
{
    public function test_guzzle_client()
    {
        $http=new Client();
        $res=$http->get('http://iwisunny.com');
        echo $res->getBody();
    }
}
