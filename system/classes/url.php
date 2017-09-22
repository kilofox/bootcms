<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * URL 辅助类
 * @package BootPHP
 * @category 辅助类
 * @作者	Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class URL {

	/**
	 * 获取应用的基本URL。
	 * 如果要指定一个协议，以字符串或请求对象形式提供。
	 * 如果使用了协议，则由 `$_SERVER['HTTP_HOST']` 变量生成一个完整的 URL。
	 *
	 *     // 不带主机或协议的绝对 URL 路径
	 *     echo URL::base();
	 *
	 *     // 带有主机、HTTPS协议和 index.php 的绝对 URL 路径
	 *     echo URL::base('https', true);
	 *
	 *     // 来自 $request 的带有主机和协议的绝对 URL 路径
	 *     echo URL::base($request);
	 *
	 * @param mixed 协议字符串, [Request], 或 boolean
	 * @param boolean	URL中加 index 文件吗？
	 * @return string
	 * @uses BootPHP::$index_file
	 * @uses Request::protocol()
	 */
	public static function base($protocol = NULL, $index = false)
	{
		// 从配置的基本URL开始
		$base_url = BootPHP::$base_url;
		if ($protocol === true)
		{
			// 用初始请求来获得协议
			$protocol = Request::$initial;
		}
		if ($protocol instanceof Request)
		{
			// 使用当前协议
			list($protocol) = explode('/', strtolower($protocol->protocol()));
		}
		if (!$protocol)
		{
			// 使用配置的默认协议
			$protocol = parse_url($base_url, PHP_URL_SCHEME);
		}
		if ($index === true && !empty(BootPHP::$index_file))
		{
			// 将 index 文件添加到URL中
			$base_url .= BootPHP::$index_file . '/';
		}
		if (is_string($protocol))
		{
			if ($port = parse_url($base_url, PHP_URL_PORT))
			{
				// 找到了端口，将其用于URL
				$port = ':' . $port;
			}
			if ($domain = parse_url($base_url, PHP_URL_HOST))
			{
				// 从URL中去掉路径以外的东西
				$base_url = parse_url($base_url, PHP_URL_PATH);
			}
			else
			{
				// 尝试使用 HTTP_HOST，不行的话就用 SERVER_NAME
				$domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
			}
			// 将协议和域名添加到基本URL中
			$base_url = $protocol . '://' . $domain . $port . $base_url;
		}
		return $base_url;
	}

	/**
	 * 根据 URI 分段取得网站的绝对URL。
	 *
	 *     echo URL::site('foo/bar');
	 *
	 * @param string $uri		要转换的网站 URI
	 * @param mixed $protocol	协议字符串或协议来自的 [Request] 类
	 * @param boolean	$index		URL是否包含索引页
	 * @return string
	 * @uses URL::base
	 */
	public static function site($uri = '', $protocol = NULL, $index = true)
	{
		// 砍掉可能的 scheme、host、port、user 和 pass 部分
		$path = preg_replace('~^[-a-z0-9+.]++://[^/]++/?~', '', trim($uri, '/'));
		if (preg_match('/[^\x00-\x7F]/S', $path))
		{
			// 按照 RFC 1738 对非 ASCII 字符编码
			$path = preg_replace_callback('~([^/]+)~', create_function('$matches', 'return rawurlencode($matches[0]);'), $path);
		}
		// 串接 URL
		return URL::base($protocol, $index) . $path;
	}

	/**
	 * 将当前GET参数与新的或重载的参数数组合并，返回查询串的结果。
	 *
	 * 	// 返回 "?sort=title&limit=10" 与存在的GET值的组合
	 * 	$query = URL::query(array('sort' => 'title', 'limit' => 10));
	 *
	 * 通常情况下，对查询结果进行排序（或类似的东西）时会用到它。
	 *
	 * [!!] 带空值的参数排除在外。
	 *
	 * @param array $params		GET 参数的数组
	 * @param boolean	$use_get	是否包含当前请求的 GET 参数
	 * @return string
	 */
	public static function query(array $params = NULL, $use_get = true)
	{
		if ($use_get)
		{
			if ($params === NULL)
			{
				// 只用当前参数
				$params = $_GET;
			}
			else
			{
				// 合并当前的和新的参数
				$params = array_merge($_GET, $params);
			}
		}
		if (empty($params))
		{
			// 无查询参数
			return '';
		}
		// 注：参数数组中只有 NULL 值时，http_build_query 返回一个空字符串
		$query = http_build_query($params, '', '&');
		// 不要在空字符串前加 '?'
		return ($query === '') ? '' : ('?' . $query);
	}

	/**
	 * 将短语转换成 URL 安全标题。
	 *
	 * 	echo URL::title('My Blog Post');	// 'my-blog-post'
	 *
	 * @param string $title		要转换的短语
	 * @param string $separator	字分隔符（任何单个字符）
	 * @param boolean	$ascii_only	直译为ASCII？
	 * @return string
	 */
	public static function title($title, $separator = '-', $ascii_only = false)
	{
		if ($ascii_only === true)
		{
			// 移除所有非分隔符、a-z、0-9或空白字符的字符
			$title = preg_replace('![^' . preg_quote($separator) . 'a-z0-9\s]+!', '', strtolower($title));
		}
		else
		{
			// 移除所有非分隔符、字、数字或空白字符的字符
			$title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', strtolower($title));
		}
		// 用单个分隔符替换所有分隔符与空白字符
		$title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);
		// 去除首尾处的分隔符
		return trim($title, $separator);
	}

}
