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
2) memcached(缓存入口的html)  
3) phpquery(后端解析curl获取的页面,生成DOM结构)  
4) phpexcel(将分析数据可视化,生成excel报表)  
5) guzzle/http(*用于封装curl请求,可选*)  
6) illuminate/database(数据库设计和操作,抽取的laravel ORM模块)  
  
## 5.待改进
- [ ]bin/analyse.php运行在cli模式,单进程cpu利用率不高,考虑用多进程(内存会增大)  
- [ ]考虑结合laravel/lumen 在队列中实现分析脚本  
- [ ]考虑curl_multi_*函数,去掉guzzle库,实现性能更高的http请求  
- [ ]考虑reactphp异步实现  
- [ ]将memcached更换为redis,尝试在redis中做分析  
- [ ]将结构化的数据入库,数据库用laravel的eloquent管理和操作  
- [ ]将本项目精简,抽出为一个composer package
