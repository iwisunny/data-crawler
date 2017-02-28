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

namespace wangxi\Crawler;

use DOMDocument;
use DOMXPath;
use InvalidArgumentException;
use Exception;
use GuzzleHttp\Client;

class HttpClient
{
    protected $reqUrl;
    protected $reqTimeout=5;
    protected $followLocation=true;
    protected $content='';  //返回结果
    protected $lastErr;

    public function __construct($url=null)
    {
        extension_loaded('curl') or die("php-curl not installed\n");

        isset($url) && $this->setUrl($url);
    }

    public function setUrl($url)
    {
        if(is_string($url) && !empty($url)){
            $this->reqUrl=rtrim((string)$url, '/');
        }
        return $this;
    }

    public function setRequestTimeout($timeout = 10)
    {
        if(is_int($timeout)){
            $this->reqTimeout=$timeout;
        }
        return $this;
    }

    public function setFollowLocation($follow = false)
    {
        $this->followLocation=$follow;
        return $this;
    }


    public function doGet($url='', $return_body=false)
    {
        $this->setUrl($url);

        if(empty($this->reqUrl)){
            throw new InvalidArgumentException('invalid url');
        }

        $sh=curl_init($this->reqUrl);
        curl_setopt($sh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($sh, CURLOPT_FOLLOWLOCATION, $this->followLocation);
        curl_setopt($sh, CURLOPT_TIMEOUT, $this->reqTimeout);

        try{
            $res=curl_exec($sh);
            $retCode=curl_getinfo($sh, CURLINFO_HTTP_CODE);

            if(false===$res){
                throw new Exception('bad http response, empty content');
            }

            curl_close($sh);
        }
        catch(Exception $e){
            $this->lastErr='curl error: '. $e->getMessage()."\n".
                'errno: '.curl_errno($sh)."\n".
                "errinfo: ".curl_error($sh);

            throw new Exception($this->lastErr);
        }

        $this->content=$res;

        if($return_body){
            return $this->content;
        }

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getXpathObj()
    {
        if(empty($this->content)){
            throw new Exception('content is invalid html fragment');
        }

        mb_internal_encoding('UTF-8');

        //strip trail content after </html>
        $content=trim(stristr($this->content, '</html>', true).'</html>');

//    echo mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');exit;

//    $orig_encoding=mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'GBK']);

        $pageDom=new DOMDocument('1.0', 'UTF-8');

        //set error level
//    $internalErrors=libxml_use_internal_errors(true);

        //todo: 必须设置xml前缀, 用@抑错, 否则中文乱码
        @$pageDom->loadHTML('<?xml encoding="UTF-8">'. $content);

        //restore error level
//    libxml_use_internal_errors($internalErrors);

        $xmlXpath=new DOMXPath($pageDom);

        return $xmlXpath;
    }

    public function doPost()
    {
        //todo
    }

    public function download()
    {

    }

    public function attachCookie()
    {

    }

    //use curl_multi_*, 多线程
    public function multiRequest()
    {

    }
}
