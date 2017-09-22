<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 路由用来根据请求的 URI 确定控制器与动作。
 * 每个路由生成一个用于匹配 URI 和路由的正则表达式。
 * 路由也可能包含用于设置控制器、动作和参数的键。
 *
 * 每个 <键> 将使用默认正则表达式模式转换成正则表达式。您可以为该键提供模式，以覆盖默认模式:
 *
 * 	// 这个路由将只匹配 <id> 为数字
 * 	Route::set('user', 'user/<action>/<id>', array('id' => '\d+'));
 *
 * 	// 这个路由将匹配 <path> 为任何东西
 * 	Route::set('file', '<path>', array('path' => '.*'));
 *
 * 另外，也可以在 URI 定义中使用括号，来创建可选的部分:
 *
 *     // 这是一个标准的默认路由，而且没有必需的键
 *     Route::set('default', '(<controller>(/<action>(/<id>)))');
 *
 *     // 这个路由只需要 <file> 键
 *     Route::set('file', '(<path>/)<file>(.<format>)', array('path' => '.*', 'format' => '\w+'));
 *
 * 路由还提供了一种方法来生成 URI（称为“反向路由”），这使得它们能够以非常强大且灵活的方式来生成内部链接。
 *
 * @package BootPHP
 * @category Base
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Route {

	// 定义 <segment> 的模式
	const REGEX_KEY = '<([a-zA-Z0-9_]++)>';
	// 哪些可以作为 <segment> 值的一部分
	const REGEX_SEGMENT = '[^/.,;?\n]++';
	// 路由正则表达式中哪些必须被转义
	const REGEX_ESCAPE = '[.\\+*?[^\\]${}=!|]';

	/**
	 * @var string 所有路由的默认协议
	 *
	 * @example	'http://'
	 */
	public static $default_protocol = 'http://';

	/**
	 * @var array 有效的本地主机条目列表
	 */
	public static $localhosts = array(false, '', 'local', 'localhost');

	/**
	 * @var string 所有路由的默认动作
	 */
	public static $default_action = 'index';

	/**
	 * @var bool 是否缓存路由
	 */
	public static $cache = false;

	/**
	 * @var	array
	 */
	protected static $_routes = array();

	/**
	 * @var	callback	路由的回调方法
	 */
	protected $_callback;

	/**
	 * @var string 路由 URI
	 */
	protected $_uri = '';

	/**
	 * @var	array
	 */
	protected $_regex = array();

	/**
	 * @var	array
	 */
	protected $_defaults = array('action' => 'index', 'host' => false);

	/**
	 * @var	string
	 */
	protected $_route_regex;

	/**
	 * 创建一个新的路由。为键设置 URI 和正则表达式。
	 * 路由应该始终由 [Route::set] 来创建，否则它们将无法正确保存。
	 *
	 * 	$route = new Route($uri, $regex);
	 *
	 * 参数 $uri 可以是基本的正则匹配的字符串，也可以是一个有效的回调或匿名函数（php 5.3+）。
	 * 如果使用回调或匿名函数，您的方法应该返回一个包含正确的路由键的数组。
	 * 如果希望路由“可逆”，您需要传递路由字符串作为第三个参数。
	 *
	 * 	$route = new Route(function($uri)
	 * 	{
	 * 		if ( list($controller, $action, $param) = explode('/', $uri) && $controller == 'foo' && $action == 'bar' )
	 * 		{
	 * 			return array(
	 * 				'controller' => 'foobar',
	 * 				'action' => $action,
	 * 				'id' => $param,
	 * 			);
	 * 		},
	 * 		'foo/bar/<id>'
	 * 	});
	 *
	 * @param mixed 路由 URI 模式或 lamba/回调函数
	 * @param array 键模式
	 * @return void
	 * @uses Route::_compile
	 */
	public function __construct($uri = NULL, $regex = NULL)
	{
		if ($uri === NULL)
		{
			// 假设路由来自缓存
			return;
		}
		if (!is_string($uri) && is_callable($uri))
		{
			$this->_callback = $uri;
			$this->_uri = $regex;
			$regex = NULL;
		}
		else if (!empty($uri))
		{
			$this->_uri = $uri;
		}
		if (!empty($regex))
		{
			$this->_regex = $regex;
		}
		// 保存编译了的正则
		$this->_route_regex = Route::compile($uri, $regex);
	}

	/**
	 * 存储一个命名的路由，并返回它。如果“action”未定义，那么将始终设置为“index”。
	 *
	 * 	Route::set('default', '(<controller>(/<action>(/<id>)))')
	 * 		->defaults(array(
	 * 			'controller' => 'welcome',
	 * 		));
	 *
	 * @param string 路由名
	 * @param string URI 模式
	 * @param array 路由的键的正则表达式
	 * @return Route
	 */
	public static function set($name, $uri_callback = NULL, $regex = NULL)
	{
		return Route::$_routes[$name] = new Route($uri_callback, $regex);
	}

	/**
	 * 检索一个命名的路由。
	 *
	 * 	$route = Route::get('default');
	 *
	 * @param string 路由名
	 * @return Route
	 * @throws	BootPHP_Exception
	 */
	public static function get($name)
	{
		if (!isset(Route::$_routes[$name]))
		{
			throw new BootPHP_Exception('The requested route does not exist: :route', array(':route' => $name));
		}
		return Route::$_routes[$name];
	}

	/**
	 * 检索所有命名的路由。
	 *
	 * 	$routes = Route::all();
	 *
	 * @return array	路由名称
	 */
	public static function all()
	{
		return Route::$_routes;
	}

	/**
	 * 获得路由名称。
	 *
	 * 	$name = Route::name($route)
	 *
	 * @param object	Route 实例
	 * @return string
	 */
	public static function name(Route $route)
	{
		return array_search($route, Route::$_routes);
	}

	/**
	 * 保存或者加载路由缓存。如果您的路由长时间保持不变，用它来重新加载缓存中的路由，而不是在加载每个页面时重新定义它们。
	 *
	 * 	if ( !Route::cache() ) Route::cache(true);
	 *
	 * @param boolean	缓存当前路由
	 * @return void	保存路由时
	 * @return boolean	加载路由时
	 * @uses BootPHP::cache
	 */
	public static function cache($save = false)
	{
		if ($save === true)
		{
			// 缓存所有定义的路由
			BootPHP::cache('Route::cache()', Route::$_routes);
		}
		else
		{
			if ($routes = BootPHP::cache('Route::cache()'))
			{
				Route::$_routes = $routes;
				// 路由已缓存
				return Route::$cache = true;
			}
			else
			{
				// 路由未缓存
				return Route::$cache = false;
			}
		}
	}

	/**
	 * 根据路由名称创建一个 URL。这是一个快捷写法：
	 *
	 * 	echo URL::site(Route::get($name)->uri($params), $protocol);
	 *
	 * @param string 路由名称
	 * @param array URI 参数
	 * @param mixed 协议字符串或布尔值，添加协议与域
	 * @return string
	 * @uses URL::site
	 */
	public static function url($name, array $params = NULL, $protocol = NULL)
	{
		$route = Route::get($name);
		// 创建一个带路由的 URI，并将其转换为 URL
		if ($route->is_external())
			return Route::get($name)->uri($params);
		else
			return URL::site(Route::get($name)->uri($params), $protocol);
	}

	/**
	 * 返回编译的路由正则表达式。将键和可选的组翻译成正确的 PCRE 正则表达式。
	 *
	 * 	$compiled = Route::compile(
	 * 		'<controller>(/<action>(/<id>))',
	 * 		array(
	 * 			'controller' => '[a-z]+',
	 * 			'id' => '\d+',
	 * 		)
	 * 	);
	 *
	 * @return string
	 * @uses Route::REGEX_ESCAPE
	 * @uses Route::REGEX_SEGMENT
	 */
	public static function compile($uri, array $regex = NULL)
	{
		if (!is_string($uri))
			return;
		// 转义除 ( ) < > 以外的所有 preg_quote 将要转义的东西
		$expression = preg_replace('#' . Route::REGEX_ESCAPE . '#', '\\\\$0', $uri);
		if (strpos($expression, '(') !== false)
		{
			// 使 URI 中的可选部分不被捕捉并且是可选的。
			$expression = str_replace(array('(', ')'), array('(?:', ')?'), $expression);
		}
		// 为键插入默认正则
		$expression = str_replace(array('<', '>'), array('(?P<', '>' . Route::REGEX_SEGMENT . ')'), $expression);
		if ($regex)
		{
			$search = $replace = array();
			foreach ($regex as $key => $value)
			{
				$search[] = "<$key>" . Route::REGEX_SEGMENT;
				$replace[] = "<$key>$value";
			}
			// 用用户规定的正则替换默认正则
			$expression = str_replace($search, $replace, $expression);
		}
		return '#^' . $expression . '$#uD';
	}

	/**
	 * 当键不存在时，为它们提供默认值。默认动作将总是“index”，除非它在这里重载。
	 *
	 * 	$route->defaults(array(
	 * 		'controller'	=> 'welcome',
	 * 		'action'		=> 'index'
	 * 	));
	 *
	 * 如果没有传递参数，该方法将充当 getter。
	 *
	 * @param array 键值
	 * @return $this 或数组
	 */
	public function defaults(array $defaults = NULL)
	{
		if ($defaults === NULL)
		{
			return $this->_defaults;
		}
		$this->_defaults = $defaults;
		return $this;
	}

	/**
	 * 测试路由是否匹配所给 URI。匹配成功则返回所有路由参数的数组。匹配失败则返回布尔值 false。
	 *
	 * 	// 参数: controller = users, action = edit, id = 10
	 * 	$params = $route->matches('users/edit/10');
	 *
	 * 这个方法应该总是在一个 if/else 块中使用：
	 *
	 * 	if ( $params = $route->matches($uri) )
	 * 	{
	 * 		// 解析参数
	 * 	}
	 *
	 * @param string 要匹配的 URI
	 * @return array	如果成功
	 * @return false	如果失败
	 */
	public function matches($uri)
	{
		if ($this->_callback)
		{
			$closure = $this->_callback;
			$params = call_user_func($closure, $uri);
			if (!is_array($params))
				return false;
		}
		else
		{
			if (!preg_match($this->_route_regex, $uri, $matches))
				return false;
			$params = array();
			foreach ($matches as $key => $value)
			{
				if (is_int($key))
				{
					// 路过所有未命名的键
					continue;
				}
				// 为所有匹配的键赋值
				$params[$key] = $value;
			}
		}
		foreach ($this->_defaults as $key => $value)
		{
			if (!isset($params[$key]) || $params[$key] === '')
			{
				// 为不匹配的键赋默认值
				$params[$key] = $value;
			}
		}
		return $params;
	}

	/**
	 * 该路由是否为远程控制器的外部路由。
	 *
	 * @return boolean
	 */
	public function is_external()
	{
		return !in_array(Arr::get($this->_defaults, 'host', false), Route::$localhosts);
	}

	/**
	 * 基于给定的参数，为当前路由生成 URI。
	 *
	 * 	// 使用“default”路由: “users/profile/10”
	 * 	$route->uri(array(
	 * 		'controller' => 'users',
	 * 		'action'	 => 'profile',
	 * 		'id'		 => '10'
	 * 	));
	 *
	 * @param array URI 参数
	 * @return string
	 * @throws	BootPHP_Exception
	 * @uses Route::REGEX_Key
	 */
	public function uri(array $params = NULL)
	{
		// 从路由的 URI 开始
		$uri = $this->_uri;
		if (strpos($uri, '<') === false && strpos($uri, '(') === false)
		{
			// 这是一个静态路由，无需替换任何东西
			if (!$this->is_external())
				return $uri;
			// 如果本地主机设置中没有协议
			if (strpos($this->_defaults['host'], '://') === false)
			{
				// 使用定义的默认协议
				$params['host'] = Route::$default_protocol . $this->_defaults['host'];
			}
			else
			{
				// 使用提供的带协议的主机
				$params['host'] = $this->_defaults['host'];
			}
			// 编译最终的URI并返回它
			return rtrim($params['host'], '/') . '/' . $uri;
		}
		while (preg_match('#\([^()]++\)#', $uri, $match))
		{
			// 搜索匹配的值
			$search = $match[0];
			// 从匹配中去除括号，作为替换内容
			$replace = substr($match[0], 1, -1);
			while (preg_match('#' . Route::REGEX_KEY . '#', $replace, $match))
			{
				list($key, $param) = $match;
				if (isset($params[$param]))
				{
					// 用参数值替换键
					$replace = str_replace($key, $params[$param], $replace);
				}
				else
				{
					// 该组丢失了参数
					$replace = '';
					break;
				}
			}
			// 在 URI 中替换该组
			$uri = str_replace($search, $replace, $uri);
		}
		while (preg_match('#' . Route::REGEX_KEY . '#', $uri, $match))
		{
			list($key, $param) = $match;
			if (!isset($params[$param]))
			{
				// 查找默认
				if (isset($this->_defaults[$param]))
				{
					$params[$param] = $this->_defaults[$param];
				}
				else
				{
					// 需要未分组的参数
					throw new BootPHP_Exception('Required route parameter not passed: :param', array(
					':param' => $param,
					));
				}
			}
			$uri = str_replace($key, $params[$param], $uri);
		}
		// 修剪 URI 中多余的斜杠
		$uri = preg_replace('#//+#', '/', rtrim($uri, '/'));
		if ($this->is_external())
		{
			// 需要将主机添加到 URI 中
			$host = $this->_defaults['host'];
			if (strpos($host, '://') === false)
			{
				// 使用定义的默认协议
				$host = Route::$default_protocol . $host;
			}
			// 清理主机并将其放到 URI 中
			$uri = rtrim($host, '/') . '/' . $uri;
		}
		return $uri;
	}

}
