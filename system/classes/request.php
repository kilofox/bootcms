<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 请求包装器。使用 Route 类来决定用什么控制器来发送请求。
 *
 * @package BootPHP
 * @category Base
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Request {

	/**
	 * @var string 客户端用户代理
	 */
	public static $user_agent = '';

	/**
	 * @var string 客户端 IP 地址
	 */
	public static $client_ip = '0.0.0.0';

	/**
	 * @var string 受信的代理服务器 IP
	 */
	public static $trusted_proxies = array('127.0.0.1', 'localhost', 'localhost.localdomain');

	/**
	 * @var	Request	主请求实例
	 */
	public static $initial;

	/**
	 * @var	Request	当前正在执行的请求实例
	 */
	public static $current;

	/**
	 * 为给定的 URI 创建一个新的请求对象。新的请求应该使用 Request::factory 方法来创建。
	 *
	 * 	$request = Request::factory($uri);
	 *
	 * 如果设置了 $cache 参数，那么该请求的响应将尝试从缓存中获取。
	 * @param string 请求的 URI
	 * @param Cache	$cache
	 * @param array $injected_routes an array of routes to use, for testing
	 * @return void
	 * @throws  Request_Exception
	 * @uses Route::all
	 * @uses Route::matches
	 */
	public static function factory($uri = true, HTTP_Cache $cache = NULL, $injected_routes = array())
	{
		// 初始化请求
		if (!self::$initial)
		{
			if (isset($_SERVER['SERVER_PROTOCOL']))
			{
				$protocol = $_SERVER['SERVER_PROTOCOL'];
			}
			else
			{
				$protocol = HTTP::$protocol;
			}
			if (isset($_SERVER['REQUEST_METHOD']))
			{
				$method = $_SERVER['REQUEST_METHOD'];
			}
			else
			{
				$method = 'GET';
			}
			if (!empty($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN))
			{
				// 这是个安全请求
				$secure = true;
			}
			if (isset($_SERVER['HTTP_REFERER']))
			{
				$referrer = $_SERVER['HTTP_REFERER'];
			}
			if (isset($_SERVER['HTTP_USER_AGENT']))
			{
				// 浏览器类型
				self::$user_agent = $_SERVER['HTTP_USER_AGENT'];
			}
			if (isset($_SERVER['HTTP_X_REQUESTED_WITH']))
			{
				// 通常用来表示 AJAX 请求
				$requested_with = $_SERVER['HTTP_X_REQUESTED_WITH'];
			}
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], self::$trusted_proxies))
			{
				// 使用转发的 IP 地址，通常当客户端使用代理服务器时设置。
				// 格式："X-Forwarded-For: client1, proxy1, proxy2"
				$client_ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				self::$client_ip = array_shift($client_ips);
				unset($client_ips);
			}
			elseif (isset($_SERVER['HTTP_CLIENT_IP']) && isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], self::$trusted_proxies))
			{
				// 使用客户端 IP
				$client_ips = explode(',', $_SERVER['HTTP_CLIENT_IP']);
				self::$client_ip = array_shift($client_ips);
				unset($client_ips);
			}
			elseif (isset($_SERVER['REMOTE_ADDR']))
			{
				// 远程 IP 地址
				self::$client_ip = $_SERVER['REMOTE_ADDR'];
			}
			if ($method !== 'GET')
			{
				// 确保原始的 body 已保存，以备将来使用
				$body = file_get_contents('php://input');
			}
			if ($uri === true)
			{
				// 尝试取得正确的 URI
				$uri = self::detectURI();
			}
			// 创建单例
			self::$initial = $request = new Request($uri, $cache);
			// 只在初始化请求中存储全局 GET 和 POST 数据
			$request->protocol($protocol)
				->query($_GET)
				->post($_POST);
			if (isset($secure))
			{
				// 设置请求安全
				$request->secure($secure);
			}
			if (isset($method))
			{
				// 设置请求方式
				$request->method($method);
			}
			if (isset($referrer))
			{
				// 设置 referrer
				$request->referrer($referrer);
			}
			if (isset($requested_with))
			{
				// 设置 requested with 变量
				$request->requested_with($requested_with);
			}
			if (isset($body))
			{
				// 设置请求 body（可以是 PUT 类型）
				$request->body($body);
			}
		}
		else
		{
			$request = new Request($uri, $cache, $injected_routes);
		}
		return $request;
	}

	/**
	 * 用 PATH_INFO、REQUEST_URI、PHP_SELF 或 REDIRECT_URL 自动检测主请求的 URI。
	 *
	 * 	$uri = Request::detectURI();
	 *
	 * @return string	主请求的 URI
	 * @throws	BootPHP_Exception
	 */
	public static function detectURI()
	{
		if (!empty($_SERVER['PATH_INFO']))
		{
			// PATH_INFO 不包含文档根目录与 index.php
			$uri = $_SERVER['PATH_INFO'];
		}
		else
		{
			// REQUEST_URI 和 PHP_SELF 包含文档根目录与 index.php
			if (isset($_SERVER['REQUEST_URI']))
			{
				/**
				 * 我们使用 REQUEST_URI 作为回调值。这样做的原因是，parse_url() 可能无法处理错误格式的 URL，例如：
				 *     http://localhost/http://example.com/hope.php
				 * 所以，与其留空处理，不如利用它。
				 */
				$uri = $_SERVER['REQUEST_URI'];
				if ($request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
				{
					// 找到了有效的 URL 路径
					$uri = $request_uri;
				}
				// 解码请求的 URI
				$uri = rawurldecode($uri);
			}
			elseif (isset($_SERVER['PHP_SELF']))
			{
				$uri = $_SERVER['PHP_SELF'];
			}
			elseif (isset($_SERVER['REDIRECT_URL']))
			{
				$uri = $_SERVER['REDIRECT_URL'];
			}
			else
			{
				throw new BootPHP_Exception('Unable to detect the URI using PATH_INFO, REQUEST_URI, PHP_SELF or REDIRECT_URL');
			}
			// 从基 URL 中得到路径，包括 index.php
			$base_url = parse_url(BootPHP::$base_url, PHP_URL_PATH);
			if (strpos($uri, $base_url) === 0)
			{
				// 从 URI 中移除基 URL
				$uri = substr($uri, strlen($base_url));
			}
			if (BootPHP::$index_file && strpos($uri, BootPHP::$index_file) === 0)
			{
				// 从 URI 中移除 index.php
				$uri = substr($uri, strlen(BootPHP::$index_file));
			}
		}
		return $uri;
	}

	/**
	 * 返回当前执行的请求。
	 * 在 [Request::execute] 被调用时，变成当前请求；在请求完成时恢复。
	 *
	 * 	$request = Request::current();
	 *
	 * @return Request
	 */
	public static function current()
	{
		return self::$current;
	}

	/**
	 * 返回框架遇到的第一个请求。
	 * 它应该仅在 [Request::factory] 第一次调用期间设置一次。
	 *
	 * 	// 获取第一个请求
	 * 	$request = Request::initial();
	 *
	 * 	// 测试当前请求是否为第一个请求
	 * 	if ( Request::initial() === Request::current() )
	 * 		// 做一些有用的事情
	 *
	 * @return Request
	 */
	public static function initial()
	{
		return self::$initial;
	}

	/**
	 * 返回客户端用户代理信息。
	 *
	 * 	// 使用谷歌 Chrome 时返回“Chrome”
	 * 	$browser = Request::user_agent('browser');
	 *
	 * Multiple values can be returned at once by using an array:
	 *
	 * 	// Get the browser && platform with a single call
	 * 	$info = Request::user_agent(array('browser', 'platform'));
	 *
	 * When using an array for the value, an associative array will be returned.
	 *
	 * @param mixed $value String to return: browser, version, robot, mobile, platform; or array of values
	 * @return mixed	requested information, false if nothing is found
	 * @uses BootPHP::$config
	 * @uses Request::$user_agent
	 */
	public static function user_agent($value)
	{
		if (is_array($value))
		{
			$agent = array();
			foreach ($value as $v)
			{
				// Add each key to the set
				$agent[$v] = self::user_agent($v);
			}
			return $agent;
		}
		static $info;
		if (isset($info[$value]))
		{
			// 该值已存在
			return $info[$value];
		}
		if ($value === 'browser' || $value == 'version')
		{
			// 加载浏览器
			$browsers = BootPHP::$config->load('user_agents')->browser;
			foreach ($browsers as $search => $name)
			{
				if (stripos(self::$user_agent, $search) !== false)
				{
					// 设置浏览器名
					$info['browser'] = $name;
					if (preg_match('#' . preg_quote($search) . '[^0-9.]*+([0-9.][0-9.a-z]*)#i', self::$user_agent, $matches))
					{
						// 设置版本号
						$info['version'] = $matches[1];
					}
					else
					{
						// 没有找到版本号
						$info['version'] = false;
					}
					return $info[$value];
				}
			}
		}
		else
		{
			// Load the search group for this type
			$group = BootPHP::$config->load('user_agents')->$value;
			foreach ($group as $search => $name)
			{
				if (stripos(self::$user_agent, $search) !== false)
				{
					// 设置值的名称
					return $info[$value] = $name;
				}
			}
		}
		// 无法找到请求的值
		return $info[$value] = false;
	}

	/**
	 * 返回接受的内容类型。如果定义了指定的类型，那么将返回该类型的特性。
	 *
	 * 	$types = Request::accept_type();
	 *
	 * @param string 内容的 MIME 类型
	 * @return mixed	所有类型的数组，或指定类型的字符串
	 * @uses Request::_parse_accept
	 */
	public static function accept_type($type = NULL)
	{
		static $accepts;
		if ($accepts === NULL)
		{
			// 解析 HTTP_ACCEPT 头
			$accepts = self::_parse_accept($_SERVER['HTTP_ACCEPT'], array('*/*' => 1.0));
		}
		if (isset($type))
		{
			// 返回该类型的特性设置
			return isset($accepts[$type]) ? $accepts[$type] : $accepts['*/*'];
		}
		return $accepts;
	}

	/**
	 * Returns the accepted languages. If a specific language is defined,
	 * the quality of that language will be returned. If the language is not
	 * accepted, false will be returned.
	 *
	 *     $langs = Request::accept_lang();
	 *
	 * @param string $lang  Language code
	 * @return mixed   An array of all types || a specific type as a string
	 * @uses Request::_parse_accept
	 */
	public static function accept_lang($lang = NULL)
	{
		static $accepts;
		if ($accepts === NULL)
		{
			// Parse the HTTP_ACCEPT_LANGUAGE header
			$accepts = self::_parse_accept($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		}
		if (isset($lang))
		{
			// Return the quality setting for this lang
			return isset($accepts[$lang]) ? $accepts[$lang] : false;
		}
		return $accepts;
	}

	/**
	 * Returns the accepted encodings. If a specific encoding is defined,
	 * the quality of that encoding will be returned. If the encoding is not
	 * accepted, false will be returned.
	 *
	 *     $encodings = Request::accept_encoding();
	 *
	 * @param string $type Encoding type
	 * @return mixed   An array of all types || a specific type as a string
	 * @uses Request::_parse_accept
	 */
	public static function accept_encoding($type = NULL)
	{
		static $accepts;
		if ($accepts === NULL)
		{
			// Parse the HTTP_ACCEPT_LANGUAGE header
			$accepts = self::_parse_accept($_SERVER['HTTP_ACCEPT_ENCODING']);
		}
		if (isset($type))
		{
			// Return the quality setting for this type
			return isset($accepts[$type]) ? $accepts[$type] : false;
		}
		return $accepts;
	}

	/**
	 * Determines if a file larger than the post_max_size has been uploaded. PHP
	 * does not handle this situation gracefully on its own, so this method
	 * helps to solve that problem.
	 *
	 * @return boolean
	 * @uses Num::bytes
	 * @uses Arr::get
	 */
	public static function post_max_size_exceeded()
	{
		// Make sure the request method is POST
		if (self::$initial->method() !== 'POST')
			return false;
		// Get the post_max_size in bytes
		$max_bytes = Num::bytes(ini_get('post_max_size'));
		// Error occurred if method is POST, && content length is too long
		return (Arr::get($_SERVER, 'CONTENT_LENGTH') > $max_bytes);
	}

	/**
	 * Process URI
	 *
	 * @param string $uri	 URI
	 * @param array   $routes  Route
	 * @return array
	 */
	public static function process_uri($uri, $routes = NULL)
	{
		// Load routes
		$routes = (empty($routes)) ? Route::all() : $routes;
		$params = NULL;
		foreach ($routes as $name => $route)
		{
			// We found something suitable
			if ($params = $route->matches($uri))
			{
				return array(
					'params' => $params,
					'route' => $route,
				);
			}
		}
		return NULL;
	}

	/**
	 * Parses an accept header && returns an array (type => quality) of the
	 * accepted types, ordered by quality.
	 *
	 *     $accept = Request::_parse_accept($header, $defaults);
	 *
	 * @param string  $header   Header to parse
	 * @param array $accepts  Default values
	 * @return array
	 */
	protected static function _parse_accept(& $header, array $accepts = NULL)
	{
		if (!empty($header))
		{
			// Get all of the types
			$types = explode(',', $header);
			foreach ($types as $type)
			{
				// Split the type into parts
				$parts = explode(';', $type);
				// Make the type only the MIME
				$type = trim(array_shift($parts));
				// Default quality is 1.0
				$quality = 1.0;
				foreach ($parts as $part)
				{
					// Prevent undefined $value notice below
					if (strpos($part, '=') === false)
						continue;
					// Separate the key && value
					list ($key, $value) = explode('=', trim($part));
					if ($key === 'q')
					{
						// There is a quality for this type
						$quality = (float) trim($value);
					}
				}
				// Add the accept type && quality
				$accepts[$type] = $quality;
			}
		}
		// Make sure that accepts is an array
		$accepts = (array) $accepts;
		// Order by quality
		arsort($accepts);
		return $accepts;
	}

	/**
	 * @var string the x-requested-with header which most likely
	 * 			   will be xmlhttprequest
	 */
	protected $_requested_with;

	/**
	 * @var string method: GET, POST, PUT, DELETE, HEAD, etc
	 */
	protected $_method = 'GET';

	/**
	 * @var string protocol: HTTP/1.1, FTP, CLI, etc
	 */
	protected $_protocol;

	/**
	 * @var  boolean
	 */
	protected $_secure = false;

	/**
	 * @var string referring URL
	 */
	protected $_referrer;

	/**
	 * @var  Route	   route matched for this request
	 */
	protected $_route;

	/**
	 * @var  Route	   array of routes to manually look at instead of the global namespace
	 */
	protected $_routes;

	/**
	 * @var  BootPHP_Response  response
	 */
	protected $_response;

	/**
	 * @var  BootPHP_HTTP_Header  headers to sent as part of the request
	 */
	protected $_header;

	/**
	 * @var  string the body
	 */
	protected $_body;

	/**
	 * @var string controller directory
	 */
	protected $_directory = '';

	/**
	 * @var string controller to be executed
	 */
	protected $_controller;

	/**
	 * @var string action to be executed in the controller
	 */
	protected $_action;

	/**
	 * @var string the URI of the request
	 */
	protected $_uri;

	/**
	 * @var  boolean  external request
	 */
	protected $_external = false;

	/**
	 * @var  array   parameters from the route
	 */
	protected $_params = array();

	/**
	 * @var array	query parameters
	 */
	protected $_get = array();

	/**
	 * @var array	post parameters
	 */
	protected $_post = array();

	/**
	 * @var array	cookies to send with the request
	 */
	protected $_cookies = array();

	/**
	 * @var BootPHP_Request_Client
	 */
	protected $_client;

	/**
	 * Creates a new request object for the given URI. New requests should be
	 * created using the [Request::instance] || [Request::factory] methods.
	 *
	 *     $request = new Request($uri);
	 *
	 * If $cache parameter is set, the response for the request will attempt to
	 * be retrieved from the cache.
	 *
	 * @param string $uri URI of the request
	 * @param HTTP_Cache   $cache
	 * @param array   $injected_routes an array of routes to use, for testing
	 * @return void
	 * @throws  Request_Exception
	 * @uses Route::all
	 * @uses Route::matches
	 */
	public function __construct($uri, HTTP_Cache $cache = NULL, $injected_routes = array())
	{
		// Initialise the header
		$this->_header = new HTTP_Header(array());
		// Assign injected routes
		$this->_injected_routes = $injected_routes;
		// Cleanse query parameters from URI (faster that parse_url())
		$split_uri = explode('?', $uri);
		$uri = array_shift($split_uri);
		// Initial request has global $_GET already applied
		if (self::$initial !== NULL)
		{
			if ($split_uri)
			{
				parse_str($split_uri[0], $this->_get);
			}
		}
		// Detect protocol (if present)
		// Always default to an internal request if we don't have an initial.
		// This prevents the default index.php from being able to proxy external pages.
		if (self::$initial === NULL || strpos($uri, '://') === false)
		{
			// Remove trailing slashes from the URI
			$uri = trim($uri, '/');
			$processed_uri = self::process_uri($uri, $this->_injected_routes);
			// Return here rather than throw exception. This will allow
			// use of Request object even with unmatched route
			if ($processed_uri === NULL)
			{
				$this->_uri = $uri;
				return;
			}
			// Store the URI
			$this->_uri = $uri;
			// Store the matching route
			$this->_route = $processed_uri['route'];
			$params = $processed_uri['params'];
			// Is this route external?
			$this->_external = $this->_route->is_external();
			if (isset($params['directory']))
			{
				// Controllers are in a sub-directory
				$this->_directory = $params['directory'];
			}
			// Store the controller
			$this->_controller = $params['controller'];
			if (isset($params['action']))
			{
				// Store the action
				$this->_action = $params['action'];
			}
			else
			{
				// Use the default action
				$this->_action = Route::$default_action;
			}
			// These are accessible as public vars && can be overloaded
			unset($params['controller'], $params['action'], $params['directory']);
			// Params cannot be changed once matched
			$this->_params = $params;
			// Apply the client
			$this->_client = new Request_Client_Internal(array('cache' => $cache));
		}
		else
		{
			// Create a route
			$this->_route = new Route($uri);
			// Store the URI
			$this->_uri = $uri;
			// Set the security setting if required
			if (strpos($uri, 'https://') === 0)
			{
				$this->secure(true);
			}
			// Set external state
			$this->_external = true;
			// Setup the client
			$this->_client = Request_Client_External::factory(array('cache' => $cache));
		}
	}

	/**
	 * Returns the response as the string representation of a request.
	 *
	 *     echo $request;
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Returns the URI for the current route.
	 *
	 *     $request->uri();
	 *
	 * @param array   $params  Additional route parameters
	 * @return string
	 * @uses Route::uri
	 */
	public function uri()
	{
		return empty($this->_uri) ? '/' : $this->_uri;
	}

	/**
	 * Create a URL string from the current request. This is a shortcut for:
	 *
	 *     echo URL::site($this->request->uri(), $protocol);
	 *
	 * @param array $params	URI parameters
	 * @param mixed $protocol  protocol string || Request object
	 * @return string

	 * @uses URL::site
	 */
	public function url($protocol = NULL)
	{
		// Create a URI with the current route && convert it to a URL
		return URL::site($this->uri(), $protocol);
	}

	/**
	 * Retrieves a value from the route parameters.
	 *
	 *     $id = $request->param('id');
	 *
	 * @param string $key Key of the value
	 * @param mixed $default Default value if the key is not set
	 * @return mixed
	 */
	public function param($key = NULL, $default = NULL)
	{
		if ($key === NULL)
		{
			// Return the full array
			return $this->_params;
		}
		return isset($this->_params[$key]) ? $this->_params[$key] : $default;
	}

	/**
	 * Redirects as the request response. If the URL does not include a
	 * protocol, it will be converted into a complete URL.
	 *
	 *     $request->redirect($url);
	 *
	 * [!!] No further processing can be done after this method is called!
	 *
	 * @param string $url Redirect location
	 * @param integer $code Status code: 301, 302, etc
	 * @return void
	 * @uses URL::site
	 * @uses Request::send_headers
	 */
	public function redirect($url = '', $code = 302)
	{
		$referrer = $this->uri();
		if (strpos($referrer, '://') === false)
		{
			$referrer = URL::site($referrer, true, BootPHP::$index_file);
		}
		if (strpos($url, '://') === false)
		{
			// Make the URI into a URL
			$url = URL::site($url, true, BootPHP::$index_file);
		}
		if (($response = $this->response()) === NULL)
		{
			$response = $this->create_response();
		}
		echo $response->status($code)
			->headers('Location', $url)
			->headers('Referer', $referrer)
			->send_headers()
			->body();
		// Stop execution
		exit;
	}

	/**
	 * Sets and gets the referrer from the request.
	 *
	 * @param string $referrer
	 * @return mixed
	 */
	public function referrer($referrer = NULL)
	{
		if ($referrer === NULL)
		{
			// Act as a getter
			return $this->_referrer;
		}
		// Act as a setter
		$this->_referrer = (string) $referrer;
		return $this;
	}

	/**
	 * Sets and gets the route from the request.
	 *
	 * @param string $route
	 * @return mixed
	 */
	public function route(Route $route = NULL)
	{
		if ($route === NULL)
		{
			// Act as a getter
			return $this->_route;
		}
		// Act as a setter
		$this->_route = $route;
		return $this;
	}

	/**
	 * Sets and gets the directory for the controller.
	 *
	 * @param string $directory Directory to execute the controller from
	 * @return mixed
	 */
	public function directory($directory = NULL)
	{
		if ($directory === NULL)
		{
			// Act as a getter
			return $this->_directory;
		}
		// Act as a setter
		$this->_directory = (string) $directory;
		return $this;
	}

	/**
	 * Sets and gets the controller for the matched route.
	 *
	 * @param string $controller Controller to execute the action
	 * @return mixed
	 */
	public function controller($controller = NULL)
	{
		if ($controller === NULL)
		{
			// Act as a getter
			return $this->_controller;
		}
		// Act as a setter
		$this->_controller = (string) $controller;
		return $this;
	}

	/**
	 * Sets and gets the action for the controller.
	 *
	 * @param string $action Action to execute the controller from
	 * @return mixed
	 */
	public function action($action = NULL)
	{
		if ($action === NULL)
		{
			// Act as a getter
			return $this->_action;
		}
		// Act as a setter
		$this->_action = (string) $action;
		return $this;
	}

	/**
	 * Provides access to the [Request_Client].
	 *
	 * @return Request_Client
	 * @return self
	 */
	public function client(Request_Client $client = NULL)
	{
		if ($client === NULL)
			return $this->_client;
		else
		{
			$this->_client = $client;
			return $this;
		}
	}

	/**
	 * Gets and sets the requested with property, which should
	 * be relative to the x-requested-with pseudo header.
	 *
	 * @param string $requested_with Requested with value
	 * @return mixed
	 */
	public function requested_with($requested_with = NULL)
	{
		if ($requested_with === NULL)
		{
			// Act as a getter
			return $this->_requested_with;
		}
		// Act as a setter
		$this->_requested_with = strtolower($requested_with);
		return $this;
	}

	/**
	 * Processes the request, executing the controller action that handles this
	 * request, determined by the [Route].
	 *
	 * 1. Before the controller action is called, the [Controller::before] method
	 * will be called.
	 * 2. Next the controller action will be called.
	 * 3. After the controller action is called, the [Controller::after] method
	 * will be called.
	 *
	 * By default, the output from the controller is captured && returned, and
	 * no headers are sent.
	 *
	 *     $request->execute();
	 *
	 * @return Response
	 * @throws Request_Exception
	 * @throws HTTP_Exception_404
	 * @uses [BootPHP::$profiling]
	 * @uses [Profiler]
	 */
	public function execute()
	{
		if (!$this->_route instanceof Route)
		{
			throw new HTTP_Exception_404('Unable to find a route to match the URI: :uri', array(
			':uri' => $this->_uri,
			));
		}
		if (!$this->_client instanceof Request_Client)
		{
			throw new Request_Exception('Unable to execute :uri without a Request_Client', array(
			':uri' => $this->_uri,
			));
		}
		return $this->_client->execute($this);
	}

	/**
	 * Returns whether this request is the initial request BootPHP received.
	 * Can be used to test for sub requests.
	 *
	 *     if ( !$request->is_initial() )
	 *         // This is a sub request
	 *
	 * @return boolean
	 */
	public function is_initial()
	{
		return ($this === self::$initial);
	}

	/**
	 * Readonly access to the [Request::$_external] property.
	 *
	 *     if ( !$request->is_external() )
	 *         // This is an internal request
	 *
	 * @return boolean
	 */
	public function is_external()
	{
		return $this->_external;
	}

	/**
	 * Returns whether this is an ajax request (as used by JS frameworks)
	 *
	 * @return boolean
	 */
	public function is_ajax()
	{
		return ($this->requested_with() === 'xmlhttprequest');
	}

	/**
	 * Generates an [ETag](http://en.wikipedia.org/wiki/HTTP_ETag) from the
	 * request response.
	 *
	 *     $etag = $request->generate_etag();
	 *
	 * [!!] If the request response is empty when this method is called, an
	 * exception will be thrown!
	 *
	 * @return string
	 * @throws Request_Exception
	 */
	public function generate_etag()
	{
		if ($this->_response === NULL)
		{
			throw new Request_Exception('No response yet associated with request - cannot auto generate resource ETag');
		}
		// Generate a unique hash for the response
		return '"' . sha1($this->_response) . '"';
	}

	/**
	 * 设置或者获取该请求的响应
	 *
	 * @param Response $response Response to apply to this request
	 * @return Response
	 * @return void
	 */
	public function response(Response $response = NULL)
	{
		if ($response === NULL)
		{
			// Act as a getter
			return $this->_response;
		}
		// Act as a setter
		$this->_response = $response;
		return $this;
	}

	/**
	 * Creates a response based on the type of request, i.e. an
	 * Request_HTTP will produce a Response_HTTP, && the same applies
	 * to CLI.
	 *
	 *     // Create a response to the request
	 *     $response = $request->create_response();
	 *
	 * @param boolean $bind Bind to this request
	 * @return Response
	 */
	public function create_response($bind = true)
	{
		$response = new Response(array('_protocol' => $this->protocol()));
		if ($bind)
		{
			// Bind a new response to the request
			$this->_response = $response;
		}
		return $response;
	}

	/**
	 * Gets or sets the HTTP method. Usually GET, POST, PUT or DELETE in traditional CRUD applications.
	 *
	 * @param string $method Method to use for this request
	 * @return mixed
	 */
	public function method($method = NULL)
	{
		if ($method === NULL)
		{
			// Act as a getter
			return $this->_method;
		}
		// Act as a setter
		$this->_method = strtoupper($method);
		return $this;
	}

	/**
	 * Gets or sets the HTTP protocol. If there is no current protocol set, it will use the default set in HTTP::$protocol
	 *
	 * @param string $protocol Protocol to set to the request/response
	 * @return mixed
	 */
	public function protocol($protocol = NULL)
	{
		if ($protocol === NULL)
		{
			if ($this->_protocol)
				return $this->_protocol;
			else
				return $this->_protocol = HTTP::$protocol;
		}
		// Act as a setter
		$this->_protocol = strtoupper($protocol);
		return $this;
	}

	/**
	 * Getter/Setter to the security settings for this request. This
	 * method should be treated as immutable.
	 *
	 * @param boolean is this request secure?
	 * @return mixed
	 */
	public function secure($secure = NULL)
	{
		if ($secure === NULL)
			return $this->_secure;
		// Act as a setter
		$this->_secure = (bool) $secure;
		return $this;
	}

	/**
	 * Gets or sets HTTP headers to the request or response. All headers
	 * are included immediately after the HTTP protocol definition during
	 * transmission. This method provides a simple array or key/value
	 * interface to the headers.
	 *
	 * @param mixed $key Key or array of key/value pairs to set
	 * @param string $value Value to set to the supplied key
	 * @return mixed
	 */
	public function headers($key = NULL, $value = NULL)
	{
		if ($key instanceof HTTP_Header)
		{
			// Act a setter, replace all headers
			$this->_header = $key;
			return $this;
		}
		if (is_array($key))
		{
			// Act as a setter, replace all headers
			$this->_header->exchangeArray($key);
			return $this;
		}
		if ($this->_header->count() === 0 && $this->is_initial())
		{
			// Lazy load the request headers
			$this->_header = HTTP::request_headers();
		}
		if ($key === NULL)
		{
			// Act as a getter, return all headers
			return $this->_header;
		}
		elseif ($value === NULL)
		{
			// Act as a getter, single header
			return ($this->_header->offsetExists($key)) ? $this->_header->offsetGet($key) : NULL;
		}
		// Act as a setter for a single header
		$this->_header[$key] = $value;
		return $this;
	}

	/**
	 * Set && get cookies values for this request.
	 *
	 * @param mixed $key Cookie name, or array of cookie values
	 * @param string $value Value to set to cookie
	 * @return string
	 * @return mixed
	 */
	public function cookie($key = NULL, $value = NULL)
	{
		if (is_array($key))
		{
			// Act as a setter, replace all cookies
			$this->_cookies = $key;
		}
		if ($key === NULL)
		{
			// Act as a getter, all cookies
			return $this->_cookies;
		}
		elseif ($value === NULL)
		{
			// Act as a getting, single cookie
			return isset($this->_cookies[$key]) ? $this->_cookies[$key] : NULL;
		}
		// Act as a setter for a single cookie
		$this->_cookies[$key] = (string) $value;
		return $this;
	}

	/**
	 * Gets or sets the HTTP body to the request or response. The body is
	 * included after the header, separated by a single empty new line.
	 *
	 * @param string $content Content to set to the object
	 * @return mixed
	 */
	public function body($content = NULL)
	{
		if ($content === NULL)
		{
			// Act as a getter
			return $this->_body;
		}
		// Act as a setter
		$this->_body = $content;
		return $this;
	}

	/**
	 * Returns the length of the body for use with content header
	 *
	 * @return integer
	 */
	public function content_length()
	{
		return strlen($this->body());
	}

	/**
	 * Renders the HTTP_Interaction to a string, producing
	 *
	 *  - Protocol
	 *  - Headers
	 *  - Body
	 *
	 *  If there are variables set to the `BootPHP_Request::$_post`
	 *  they will override any values set to body.
	 *
	 * @param boolean $response Return the rendered response, else returns the rendered request
	 * @return string
	 */
	public function render()
	{
		if (!$post = $this->post())
		{
			$body = $this->body();
		}
		else
		{
			$this->headers('content-type', 'application/x-www-form-urlencoded');
			$body = http_build_query($post, NULL, '&');
		}
		// Set the content length
		$this->headers('content-length', (string) $this->content_length());
		// If BootPHP expose, set the user-agent
		if (BootPHP::$expose)
		{
			$this->headers('user-agent', 'BootPHP Framework ' . BootPHP::VERSION);
		}
		// Prepare cookies
		if ($this->_cookies)
		{
			$cookie_string = array();
			// Parse each
			foreach ($this->_cookies as $key => $value)
			{
				$cookie_string[] = $key . '=' . $value;
			}
			// Create the cookie string
			$this->_header['cookie'] = implode('; ', $cookie_string);
		}
		$output = $this->method() . ' ' . $this->uri() . ' ' . $this->protocol() . "\r\n";
		$output .= (string) $this->_header;
		$output .= $body;
		return $output;
	}

	/**
	 * 获取或设置 HTTP 请求字符串
	 *
	 * @param mixed 键或键值对
	 * @param string 值
	 * @return mixed
	 */
	public function query($key = NULL, $value = NULL)
	{
		if (is_array($key))
		{
			// 设置，替换所有查询串
			$this->_get = $key;
			return $this;
		}
		if ($key === NULL)
		{
			// 获取，所有查询串
			return $this->_get;
		}
		elseif ($value === NULL)
		{
			// 获取，单个查询串
			return Arr::get($this->_get, $key);
		}
		// 设置，单个查询串
		$this->_get[$key] = $value;
		return $this;
	}

	/**
	 * 为请求获取或设置 HTTP POST 参数
	 *
	 * @param mixed 键或键值对
	 * @param string 值
	 * @return mixed
	 */
	public function post($key = NULL, $value = NULL)
	{
		if (is_array($key))
		{
			// 设置，替换所有域
			$this->_post = $key;
			return $this;
		}
		if ($key === NULL)
		{
			// 获取，所有域
			return $this->_post;
		}
		elseif ($value === NULL)
		{
			// 获取，单个域
			return Arr::get($this->_post, $key);
		}
		// 设置，单个域
		$this->_post[$key] = $value;
		return $this;
	}

}
