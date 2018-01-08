<?php

namespace Opdss\Http;

class Response
{
	/**
	 * CURL信息
	 * @var mixed
	 */
	private $curlInfo;

	/**
	 * 请求返回结果
	 * @var mixed
	 */
	public $response;

	/**
	 * 返回头, 包含中间所有请求(即包含重定向)的返回头
	 * @var array
	 */
	public $allHeaders;

	/**
	 * Cookie
	 * @var array
	 */
	private $cookies;

	/**
	 * 头部内容
	 * @var mixed
	 */
	public $httpHeader;

	/**
	 * 返回结果
	 * @var string
	 */
	private $body;

	/**
	 * __construct
	 * @return mixed 
	 */
	public function __construct($response, $curlInfo)
	{
		$this->curlInfo = $curlInfo;
		$this->response = $response;
	}

	/**
	 * curl详细信息
	 * @param null $key
	 * @return null
	 */
	public function getCurlInfo($key = null)
	{
		return $key ? (isset($this->curlInfo[$key]) ? $this->curlInfo[$key] : null) : $this->curlInfo;
	}

	/**
	 * 获取http状态码
	 * @return int 
	 */
	public function httpCode()
	{
		return $this->getCurlInfo('http_code');
	}

	/**
	 * 获取请求头信息
	 * @return null
	 */
	public function requestHeader()
	{
		return $this->getCurlInfo('request_header');
	}

	/**
	 * 获取响应头信息string
	 * @return bool|mixed|string
	 */
	public function getHttpHeader()
	{
		if ($this->httpHeader === null) {
			$this->httpHeader = substr($this->response, 0, $this->getCurlInfo('header_size'));
		}
		return $this->httpHeader;
	}

	/**
	 * 获取相应信息
	 * @return bool|string
	 */
	public function getBody()
	{
		if ($this->body === null) {
			$this->body = substr($this->response, $this->getCurlInfo('header_size'));
		}
		return $this->body;
	}

	/**
	 * 获取cookie
	 * @param $name
	 * @return mixed|null
	 */
	public function getCookie($name)
	{
		$cookies = $this->getCookies();
		return isset($cookies[$name]) ? $cookies[$name] : null;
	}

	/**
	 * 获取cookie
	 * @return array|null
	 */
	public function getCookies()
	{
		if ($this->cookies === null) {
			$this->cookies = $this->parseCookie();
		}
		return $this->cookies;
	}

	/**
	 * 获取响应头信息
	 * @param $name
	 * @return mixed|null
	 */
	public function getHeader($name)
	{
		$headers = $this->getHeaders();
		return isset($headers[$name]) ? $headers[$name] : null;
	}

	/**
	 * 获取响应头信息
	 * @param bool $all
	 * @return array|mixed|null
	 */
	public function getHeaders($all = false)
	{
		if ($this->allHeaders === null) {
			$this->allHeaders = $this->parseHeader();
		}
		return $all ? $this->allHeaders : end($this->allHeaders);
	}

    /**
     * 直返返回response数据
     * @return string
     */
	public function __toString()
    {
        return $this->getBody();
    }

	/**
	 * 处理header
	 */
	private function parseHeader()
	{
		$allHeaders = array();
		$rawHeaders = explode("\r\n\r\n", trim($this->getHttpHeader()), 2);
		$requestCount = count($rawHeaders);
		for($i=0; $i<$requestCount; ++$i){
			$allHeaders[$i] = $this->parseHeaderOneRequest($rawHeaders[$i]);
		}
		return $allHeaders;
	}

	/**
	 * parseHeaderOneRequest
	 * @param string $piece 
	 * @return array
	 */
	private function parseHeaderOneRequest($piece){
		$tmpHeaders = array();
		$lines = explode("\r\n", $piece);
		$linesCount = count($lines);
		//从1开始，第0行包含了协议信息和状态信息，排除该行
		for($i=1; $i<$linesCount; ++$i){
			$line = trim($lines[$i]);
			if(empty($line) || strpos($line, ':') === false) continue;
			list($key, $value) = explode(':', $line, 2);
			$key = trim($key);
			$value = trim($value);
			if(isset($tmpHeaders[$key])){
				if(is_array($tmpHeaders[$key])){
					$tmpHeaders[$key][] = $value;
				}else{
					$tmp = $tmpHeaders[$key];
					$tmpHeaders[$key] = array(
						$tmp,
						$value
					);
				}
			}else{
				$tmpHeaders[$key] = $value;
			}
		}
		return $tmpHeaders;
	}

	/**
	 * 处理cookie
	 */
	private function parseCookie()
	{
		$cookies = array();
		$count = preg_match_all('/set-cookie\s*:\s*([^\r\n]+)/i', $this->getHttpHeader(), $matches);
		for($i = 0; $i < $count; ++$i)
		{
			$list = explode(';', $matches[1][$i]);
			$count2 = count($list);
			if(isset($list[0]))
			{
				list($cookieName, $value) = explode('=', $list[0]);
				$cookieName = trim($cookieName);
				$cookies[$cookieName] = array('value'=>$value);
				for($j = 1; $j < $count2; ++$j)
				{
					list($name, $value) = explode('=', $list[$j]);
					$cookies[$cookieName][trim($name)] = $value;
				}
			}
		}
		return $cookies;
	}
}