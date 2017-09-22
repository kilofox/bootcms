<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * Cookie 辅助类。
 *
 * @package BootPHP
 * @category 辅助类
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Cookie {

	/**
	 * @var string 添加到 cookie 的加盐
	 */
	public static $salt = NULL;

	/**
	 * @var	integer	cookie 过期前的秒数
	 */
	public static $expiration = 0;

	/**
	 * @var string 限定 cookie 的可用路径
	 */
	public static $path = '/';

	/**
	 * @var string 限定 cookie 的可用域
	 */
	public static $domain = NULL;

	/**
	 * @var	boolean	只通过安全连接传输 cookie
	 */
	public static $secure = false;

	/**
	 * @var	boolean	只通过 HTTP 传输 cookie，禁用 Javascript 访问
	 */
	public static $httponly = false;

	/**
	 * 获得签名的 cookie 的值。没有签名的 cookie 将不会返回。如果 cookie 签名存在，但无效，该 cookie 会被删除。
	 *
	 *     // 获取 "theme" cookie，如果 cookie 不存在，那么用 "blue"
	 *     $theme = Cookie::get('theme', 'blue');
	 *
	 * @param string cookie 名称
	 * @param mixed 要返回的默认值
	 * @return string
	 */
	public static function get($key, $default = NULL)
	{
		if (!isset($_COOKIE[$key]))
		{
			// cookie 不存在
			return $default;
		}
		// 获得 cookie 值
		$cookie = $_COOKIE[$key];
		// 找到加盐与内容的分割位置
		$split = strlen(Cookie::salt($key, NULL));
		if (isset($cookie[$split]) && $cookie[$split] === '~')
		{
			// 将加盐与值分离
			list ($hash, $value) = explode('~', $cookie, 2);
			if (Cookie::salt($key, $value) === $hash)
			{
				// Cookie 签名有效
				return $value;
			}
			// Cookie 签名无效，删除之
			Cookie::delete($key);
		}
		return $default;
	}

	/**
	 * 设置一个签名的cookie。注意，所有的 cookie 值必须为字符串，而且不能自动序列化！
	 *
	 * 	// 设置 cookie "theme"
	 * 	Cookie::set('theme', 'red');
	 *
	 * @param string cookie 名称
	 * @param string cookie 值
	 * @param integer	以秒为单位的生命周期
	 * @return boolean
	 * @uses Cookie::salt
	 */
	public static function set($name, $value, $expiration = NULL)
	{
		if ($expiration === NULL)
		{
			// 使用默认过期时间
			$expiration = Cookie::$expiration;
		}
		if ($expiration !== 0)
		{
			// 过期时间要求为 UNIX 时间戳
			$expiration += time();
		}
		// Add the salt to the cookie value
		$value = Cookie::salt($name, $value) . '~' . $value;
		return setcookie($name, $value, $expiration, Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httponly);
	}

	/**
	 * 通过使值为空且过期来删除。
	 *
	 * 	Cookie::delete('theme');
	 *
	 * @param string cookie 名称
	 * @return boolean
	 * @uses Cookie::set
	 */
	public static function delete($name)
	{
		// 清除 cookie
		unset($_COOKIE[$name]);
		// 废除 cookie，使其失效
		return setcookie($name, NULL, -86400, Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httponly);
	}

	/**
	 * 根据名称和值，为 cookie 生成一个加盐字符串。
	 *
	 * 	$salt = Cookie::salt('theme', 'red');
	 *
	 * @param string cookie 名称
	 * @param string cookie 值
	 * @return string
	 */
	public static function salt($name, $value)
	{
		// 需要一个有效的加盐字符串
		if (!Cookie::$salt)
		{
			throw new BootPHP_Exception('A valid cookie salt is required. Please set Cookie::$salt.');
		}
		// 确定用户代理
		$agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'unknown';
		return sha1($agent . $name . $value . Cookie::$salt);
	}

}
