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

namespace wangxi\Crawler;

abstract class SiteParser
{
    protected $siteUrl = '';
    protected $siteTitle = '';
    protected $mainData = [];

    /**
     * @var HttpClient
     */
    protected $client;

    /**
     * @var \DOMXPath
     */
    protected $xpathHandler;

    public function __construct(HttpClient $client = null)
    {
        //todo: ?? only in php7
//        $this->client=$client ?: new HttpClient();
        $this->client = isset($client) ? $client : new HttpClient();
        $this->client->setUrl($this->siteUrl);
    }

    public function setHttpClient($client = null)
    {
        $this->client = $client;
    }

    public function setUrl($url='')
    {
        $this->siteUrl=$url;
        $this->client->setUrl($url);
    }

    public function fetch()
    {
        if(empty($this->client->getContent())){
            $this->client->doGet();
        }

        $this->xpathHandler = $this->client->getXpathObj();

        //todo
    }

    public function query($xpath_query='', $context=null)
    {
        if(!isset($this->xpathHandler)){
            $this->fetch();
        }

        if(!empty($xpath_query)){
            return $this->xpathHandler->query($xpath_query, $context);
        }

        throw new \Exception('xpath not initialized');
    }

    public function getTitle()
    {
        $site_title=$this->query('//title')->item(0);

        return $this->siteTitle = trim($site_title->nodeValue);
    }

    abstract public function getMainData();

    public function setMain($data = [])
    {
        return $this->mainData = $data;
    }
}
