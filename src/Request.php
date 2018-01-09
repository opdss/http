<?php

namespace Opdss\Http;

class Request
{
	/**
	 * CURL操作对象
	 * @var resource
	 */
	private $handler;

	/**
	 * Url地址
	 * @var string
	 */
	private $url;

	/**
	 * 发送内容
	 * @var mixed
	 */
	private $content;

	/**
	 * CurlOptions
	 * @var array
	 */
	private $options = array();

	/**
	 * header头
	 * @var array
	 */
	private $headers = array();

	/**
	 * Cookies
	 * @var array
	 */
	private $cookies = array();

	/**
	 * 保存Cookie文件的文件名
	 * @var string
	 */
	private $cookieFileName = '';

	/**
	 * 失败重试次数
	 * @var int
	 */
	private $retry = 0;

	/**
	 * 是否使用代理
	 * @var bool
	 */
	private $useProxy = false;

	/**
	 * 代理设置
	 * @var array
	 */
	private $proxy = array();

	/**
	 * 是否验证证书
	 * @var bool
	 */
	private $isVerifyCA = false;

	/**
	 * CA根证书路径
	 * @var string
	 */
	private $caCert;

	/**
	 * 连接超时时间，单位：毫秒
	 * @var int
	 */
	private $connectTimeout = 30000;

	/**
	 * 总超时时间，单位：毫秒
	 * @var int
	 */
	private $timeout = 0;

	/**
	 * 下载限速，为0则不限制，单位：字节
	 * @var int
	 */
	private $downloadSpeed;

	/**
	 * 上传限速，为0则不限制，单位：字节
	 * @var int
	 */
	private $uploadSpeed;

	/**
	 * 用于连接中需要的用户名
	 * @var string
	 */
	private $username;

	/**
	 * 用于连接中需要的密码
	 * @var string
	 */
	private $password;

	/**
	 * 请求结果保存至文件的配置
	 * @var mixed
	 */
	private $saveFileOption = array();

	/**
	 * 代理认证方式
	 */
	private static $proxyAuths = array(
		'basic' => CURLAUTH_BASIC,
		'ntlm' => CURLAUTH_NTLM
	);

	/**
	 * 代理类型
	 */
	private static $proxyType = array(
		'http' => CURLPROXY_HTTP,
		'socks4' => CURLPROXY_SOCKS4,
		'socks4a' => 6,    // CURLPROXY_SOCKS4A
		'socks5' => CURLPROXY_SOCKS5,
	);

	/**
	 * 默认请求方法
	 * Request constructor.
	 * @param null $url
	 */
	private static $methods = array(
		'GET', 'POST', 'DELETE', 'PATCH', 'HEAD', 'PUT'
	);

	/**
	 * __construct
	 * @return mixed
	 */
	public function __construct($url = null)
	{
		$this->open();
		$url AND $this->url = $url;
		$this->cookieFileName = tempnam(sys_get_temp_dir(), '');
	}

	public function init()
	{
		$this->close();
		$this->open();
		return $this;
	}

	public function __destruct()
	{
		$this->close();
	}

	private function open()
	{
		$this->handler = curl_init();
		$this->retry = 0;
		$this->headers = $this->options = array();
		$this->url = $this->content = '';
		$this->useProxy = false;
		$this->proxy = array(
			'auth' => 'basic',
			'type' => 'http',
		);
		$this->isVerifyCA = false;
		$this->caCert = null;
		$this->connectTimeout = 30000;
		$this->timeout = 0;
		$this->downloadSpeed = null;
		$this->uploadSpeed = null;
		$this->username = null;
		$this->password = null;
		$this->saveFileOption = array();
	}

	private function close()
	{
		if (null !== $this->handler) {
			curl_close($this->handler);
			$this->handler = null;
		}
	}

	/**
	 * 创建一个新会话
	 * @return Request
	 */
	public static function factory($url = null)
	{
		return new self($url);
	}

	/**
	 * 设置Url
	 * @param mixed $url
	 * @return Request
	 */
	public function url($url)
	{
		$this->url = $url;
		return $this;
	}

	/**
	 * 设置发送内容，requestBody的别名
	 * @param mixed $content
	 * @return Request
	 */
	public function content($content)
	{
		return $this->requestBody($content);
	}

	/**
	 * 设置参数，requestBody的别名
	 * @param mixed $params
	 * @return Request
	 */
	public function params($params)
	{
		return $this->requestBody($params);
	}

	/**
	 * 设置请求主体
	 * @param mixed $requestBody
	 * @return Request
	 */
	public function requestBody($requestBody)
	{
		$this->content = $requestBody;
		return $this;
	}

	/**
	 * 批量设置CURL的Option
	 * @param array $options
	 * @return Request
	 */
	public function options($options)
	{
		foreach ($options as $key => $value) {
			$this->options[$key] = $value;
		}
		return $this;
	}

	/**
	 * 设置CURL的Option
	 * @param int $option
	 * @param mixed $value
	 * @return Request
	 */
	public function option($option, $value)
	{
		$this->options[$option] = $value;
		return $this;
	}

	/**
	 * 批量设置CURL的Option
	 * @param array $options
	 * @return Request
	 */
	public function headers($headers)
	{
		$this->headers = array_merge($this->headers, $headers);
		return $this;
	}

	/**
	 * 设置CURL的Option
	 * @param int $option
	 * @param mixed $value
	 * @return Request
	 */
	public function header($header, $value)
	{
		$this->headers[$header] = $value;
		return $this;
	}

	/**
	 * 设置Accept
	 * @param string $accept
	 * @return Request
	 */
	public function accept($accept)
	{
		$this->headers['Accept'] = $accept;
		return $this;
	}

	/**
	 * 设置Accept-Language
	 * @param string $acceptLanguage
	 * @return Request
	 */
	public function acceptLanguage($acceptLanguage)
	{
		$this->headers['Accept-Language'] = $acceptLanguage;
		return $this;
	}

	/**
	 * 设置Accept-Encoding
	 * @param string $acceptEncoding
	 * @return Request
	 */
	public function acceptEncoding($acceptEncoding)
	{
		$this->headers['Accept-Encoding'] = $acceptEncoding;
		return $this;
	}

	/**
	 * 设置Accept-Ranges
	 * @param string $acceptRanges
	 * @return Request
	 */
	public function acceptRanges($acceptRanges)
	{
		$this->headers['Accept-Ranges'] = $acceptRanges;
		return $this;
	}

	/**
	 * 设置Cache-Control
	 * @param string $cacheControl
	 * @return Request
	 */
	public function cacheControl($cacheControl)
	{
		$this->headers['Cache-Control'] = $cacheControl;
		return $this;
	}

	/**
	 * 设置Cookies
	 * @param array $headers
	 * @return Request
	 */
	public function cookies($headers)
	{
		$this->cookies = array_merge($this->cookies, $headers);
		return $this;
	}

	/**
	 * 设置Cookie
	 * @param string $name
	 * @param string $value
	 * @return Request
	 */
	public function cookie($name, $value)
	{
		$this->cookies[$name] = $value;
		return $this;
	}

	/**
	 * 设置Content-Type
	 * @param string $contentType
	 * @return Request
	 */
	public function contentType($contentType)
	{
		$this->headers['Content-Type'] = $contentType;
		return $this;
	}

	/**
	 * 设置Range
	 * @param string $range
	 * @return Request
	 */
	public function range($range)
	{
		$this->headers['Range'] = $range;
		return $this;
	}

	/**
	 * 设置Referer
	 * @param string $referer
	 * @return Request
	 */
	public function referer($referer)
	{
		$this->headers['Referer'] = $referer;
		return $this;
	}

	/**
	 * 设置User-Agent
	 * @param string $userAgent
	 * @return Request
	 */
	public function userAgent($userAgent)
	{
		$this->headers['User-Agent'] = $userAgent;
		return $this;
	}

	/**
	 * 设置失败重试次数，状态码非200时重试
	 * @param string $userAgent
	 * @return Request
	 */
	public function retry($retry)
	{
		$this->retry = $retry < 0 ? 0 : $retry;   //至少请求1次，即重试0次
		return $this;
	}

	/**
	 * 代理
	 * @param string $server
	 * @param int $port
	 * @param string $type
	 * @param string $auth
	 * @return Request
	 */
	public function proxy($server, $port, $type = 'http', $auth = 'basic')
	{
		$this->useProxy = true;
		$this->proxy = array(
			'server' => $server,
			'port' => $port,
			'type' => $type,
			'auth' => $auth,
		);
		return $this;
	}

	/**
	 * 设置超时时间
	 * @param int $timeout 总超时时间，单位：毫秒
	 * @param int $connectTimeout 连接超时时间，单位：毫秒
	 * @return Request
	 */
	public function timeout($timeout = null, $connectTimeout = null)
	{
		if (null !== $timeout) {
			$this->timeout = $timeout;
		}
		if (null !== $connectTimeout) {
			$this->connectTimeout = $connectTimeout;
		}
		return $this;
	}

	/**
	 * 限速
	 * @param int $download 下载速度，为0则不限制，单位：字节
	 * @param int $upload 上传速度，为0则不限制，单位：字节
	 * @return Request
	 */
	public function limitRate($download = 0, $upload = 0)
	{
		$this->downloadSpeed = $download;
		$this->uploadSpeed = $upload;
		return $this;
	}

	/**
	 * 设置用于连接中需要的用户名和密码
	 * @param string $username
	 * @param string $password
	 * @return Request
	 */
	public function userPwd($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
		return $this;
	}

	/**
	 * 设置证书
	 * @param $caCert
	 * @return $this
	 */
	public function caCert($caCert)
	{
		$this->isVerifyCA = true;
		$this->caCert = $caCert;
		return $this;
	}

	/**
	 * 保存至文件的设置
	 * @param string $filePath
	 * @param string $fileMode
	 * @return Request
	 */
	public function saveFile($filePath, $fileMode = 'w+')
	{
		$this->saveFileOption['filePath'] = $filePath;
		$this->saveFileOption['fileModel'] = $fileMode;
		return $this;
	}

	/**
	 * 获取文件保存路径
	 * @return string
	 */
	public function getSavePath()
	{
		return $this->saveFileOption['savePath'];
	}

	/**
	 * 发送请求
	 * @param string $url
	 * @param array $requestBody
	 * @return Response
	 */
	public function send($method = 'GET', $url = null, $requestBody = array())
	{
		if (null !== $url) {
			$this->url = $url;
		}
		if (!empty($requestBody)) {
			if (is_array($requestBody)) {
				$this->content = http_build_query($requestBody);
			} else if ($requestBody instanceof RequestMultipartBody) {
				$this->content = $requestBody->content();
				$this->contentType(sprintf('multipart/form-data; boundary=%s', $requestBody->getBoundary()));
			} else {
				$this->content = $requestBody;
			}
		}
		curl_setopt_array($this->handler, array(
			// 请求地址
			CURLOPT_URL => $this->url,
			// 请求方法
			CURLOPT_CUSTOMREQUEST => strtoupper($method),
			// 返回内容
			CURLOPT_RETURNTRANSFER => true,
			// 返回header
			CURLOPT_HEADER => true,
			// 发送内容
			CURLOPT_POSTFIELDS => $this->content,
			// 保存cookie
			CURLOPT_COOKIEFILE => $this->cookieFileName,
			CURLOPT_COOKIEJAR => $this->cookieFileName,
			// 自动重定向
			CURLOPT_FOLLOWLOCATION => true,
			//输出请求headers
			CURLINFO_HEADER_OUT => true,
		));
		$this->parseCA();
		$this->parseOptions();
		$this->parseProxy();
		$this->parseHeaders();
		$this->parseCookies();
		$this->parseNetwork();
		$retry = 0;
		do{
			$retry++;
			$content = curl_exec($this->handler);
			$response = new Response($content, curl_getinfo($this->handler));
			$httpCode = $response->httpCode();
			// 状态码为5XX才需要重试
			if ($httpCode > 0 && ((int)($httpCode / 100) != 5)) {
				break;
			}
		} while ($retry <= $this->retry); //默认执行一次，重试就重试的次数+1次

		// 关闭保存至文件的句柄
		if (isset($this->saveFileOption['fp'])) {
			fclose($this->saveFileOption['fp']);
			$this->saveFileOption['fp'] = null;
		}
		return $response;
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return Response
	 */
	public function __call($name, $arguments)
	{
		$name = strtoupper($name);
		if (in_array($name, self::$methods)) {
			return $this->send($name, (isset($arguments[0]) ? $arguments[0] : null), (isset($arguments[1]) ? $arguments[1] : null));
		}
		return new Response();
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return Response
	 */
	public static function __callStatic($name, $arguments)
	{
		$name = strtoupper($name);
		if (in_array($name, self::$methods) && isset($arguments[0])) {
			return self::factory()->send($name, $arguments[0], (isset($arguments[1]) ? $arguments[1] : null));
		}
		return new Response();
	}

	/**
	 * 处理Options
	 */
	private function parseOptions()
	{
		curl_setopt_array($this->handler, $this->options);
		// 请求结果保存为文件
		if (isset($this->saveFileOption['filePath']) && null !== $this->saveFileOption['filePath']) {
			curl_setopt_array($this->handler, array(
				CURLOPT_HEADER => false,
				CURLOPT_RETURNTRANSFER => false,
			));
			$filePath = $this->saveFileOption['filePath'];
			$last = substr($filePath, -1, 1);
			if ('/' === $last || '\\' === $last) {
				// 自动获取文件名
				$filePath .= basename($this->url);
			}
			$this->saveFileOption['savePath'] = $filePath;
			$this->saveFileOption['fp'] = fopen($filePath, isset($this->saveFileOption['fileMode']) ? $this->saveFileOption['fileMode'] : 'w+');
			curl_setopt($this->handler, CURLOPT_FILE, $this->saveFileOption['fp']);
		}
	}

	/**
	 * 处理代理
	 */
	private function parseProxy()
	{
		if ($this->useProxy) {
			curl_setopt_array($this->handler, array(
				CURLOPT_PROXYAUTH => self::$proxyAuths[$this->proxy['auth']],
				CURLOPT_PROXY => $this->proxy['server'],
				CURLOPT_PROXYPORT => $this->proxy['port'],
				CURLOPT_PROXYTYPE => 'socks5' === $this->proxy['type'] ? (defined('CURLPROXY_SOCKS5_HOSTNAME') ? CURLPROXY_SOCKS5_HOSTNAME : self::$proxyType[$this->proxy['type']]) : self::$proxyType[$this->proxy['type']],
			));
		}
	}

	/**
	 * 处理Headers
	 */
	private function parseHeaders()
	{
		curl_setopt($this->handler, CURLOPT_HTTPHEADER, $this->parseHeadersFormat());
	}

	/**
	 * 处理Cookie
	 */
	private function parseCookies()
	{
		$content = '';
		foreach ($this->cookies as $name => $value) {
			$content .= "{$name}={$value}; ";
		}
		curl_setopt($this->handler, CURLOPT_COOKIE, $content);
	}

	/**
	 * 处理成CURL可以识别的headers格式
	 * @return array
	 */
	private function parseHeadersFormat()
	{
		$headers = array();
		foreach ($this->headers as $name => $value) {
			$headers[] = $name . ':' . $value;
		}
		return $headers;
	}

	/**
	 * 处理证书
	 */
	private function parseCA()
	{
		if ($this->isVerifyCA) {
			curl_setopt_array($this->handler, array(
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_CAINFO => $this->caCert,
				CURLOPT_SSL_VERIFYHOST => 2,
			));
		} else {
			curl_setopt_array($this->handler, array(
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => 0,
			));
		}
	}

	/**
	 * 处理网络相关
	 * @return mixed
	 */
	private function parseNetwork()
	{
		// 用户名密码处理
		if ('' != $this->username) {
			$userPwd = $this->username . ':' . $this->password;
		} else {
			$userPwd = '';
		}
		curl_setopt_array($this->handler, array(
			// 连接超时
			CURLOPT_CONNECTTIMEOUT_MS => $this->connectTimeout,
			// 总超时
			CURLOPT_TIMEOUT_MS => $this->timeout,
			// 下载限速
			CURLOPT_MAX_RECV_SPEED_LARGE => $this->downloadSpeed,
			// 上传限速
			CURLOPT_MAX_SEND_SPEED_LARGE => $this->uploadSpeed,
			// 连接中用到的用户名和密码
			CURLOPT_USERPWD => $userPwd,
		));
	}
}