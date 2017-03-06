data-crawler
===

## 1.开始

` composer install`

## 2.测试

` ./vendor/bin/phpunit`

## 3.示例
####分析一个赌球平台的数据
 (*从首页入口,获取近100个url, 每个url分别包含次级页约400个,总计4万个页面*)

` php bin/analyse.php`

## 4.依赖项
1) php-curl扩展  
2) memcached(用于缓存入口的html)  
3) phpquery(用于解析curl获取的页面DOM)  
4) phpexcel(用户将分析得到的数据生成excel报表)  
5) guzzle/http(用于封装curl请求,可选)  
6) illuminate/database(用户数据库设计和操作,抽取的laravel ORM模块)  
  
## 5.待改进
1) ~~bin/analyse.php设置在cli模式,单进程,cpu利用率不高,内存比较理想,峰值在200M以内~~  
2) 考虑结合laravel/lumen 在队列中实现分析脚本  
3) 考虑curl_multi_*函数,去掉guzzle库,实现性能更高的curl请求  
4) 考虑php多进程,或者reactphp异步方式运行脚本  

