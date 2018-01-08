<?php

namespace Opdss\Demo;

use Opdss\Http\Request;

spl_autoload_register(function ($name) {
	$baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
	$baseNameSpace = 'Opdss\\Http\\';
	$name = str_replace('\\', DIRECTORY_SEPARATOR, $name);
	$baseNameSpace = str_replace('\\', DIRECTORY_SEPARATOR, $baseNameSpace);
	$path = str_replace($baseNameSpace, $baseDir, $name);
	$path = $path . '.php';
	if (file_exists($path)) {
		include $path;
	}
});

/*
$h = Request::factory();


// 设置单个header
//$h->header('test', '666');
// 设置多个header
$h->headers(array(
	'test2' => '777',
	'test3' => '888',
));
// socks4代理
//$h->proxy('127.0.0.1',1080,'socks4');
// socks5代理
//$h->proxy('127.0.0.1',1080,'socks5');
// http代理
$h->proxy('172.20.15.10', 8888);
$res = $h->get('https://www.baidu.com');
$res = $h->get('https://www.baidu.com');

//$h->get('https://www.baidu.com');
$h1 = Request::factory();
$h1->proxy('172.20.15.10', 8888);
$h1->get('https://www.google.com');
$h1->get('https://www.google.com');

// 设置POST内容方法1
$h->content('123');
// 设置POST内容方法2
$h->params(array(
	'id' => 2,
));
// cURL设置多个
$h->options(array(
	CURLOPT_HEADER => true,
));
// cURL设置单个
$h->option(CURLOPT_HEADER, true);
//结果存储文件
//$h->saveFile(__DIR__.'/tmp.log');
// POST请求+POST参数
//$r = $h->post('https://www.istimer.com/s',array('wd'=>'搜索词'));
// GET请求+GET参数+失败重试3次
//$r = $h->retry(3)->get('http://www.istimer.com/',array('wd'=>'搜索词'));
//var_dump($r->httpCode());
//var_dump($r->body,$r->headers,$r->cookies);
//var_dump($r->body);*/
$http = Request::factory();
$response = $http->retry(3)// 失败重试3次
	->proxy('172.20.15.10', 8888)
	->userAgent('Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)')
	->referer('http://www.istimer.com/')
	->accept('text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8')
	->acceptLanguage('zh-CN,zh;q=0.8')
	->header('ttt', '23232')
	->params((array('ttt', '23232')))
	->get('http://dm.istimer.com/wechat/score');
//var_dump($http->handler);
//var_dump($response->getBody());
//var_dump($response->getCookies());
var_dump($response->getHeaders(1));
//var_dump($response->getCurlInfo());