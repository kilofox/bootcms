<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 用户授权库。处理用户登录与退出，还有密码加密。
 *
 * @package BootPHP/授权
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
abstract class Auth {

	// Auth 实例
	protected static $_instance;

	/**
	 * 单例模式
	 * @return Auth
	 */
	public static function instance()
	{
		if (!isset(self::$_instance))
		{
			// 为该类型加载配置信息
			$config = BootPHP::$config->load('auth');
			if (!$type = $config->get('driver'))
				$type = 'db';
			// 设置 session 类名
			$class = 'Auth_' . ucfirst($type);
			// 创建一个新的 session 实例
			self::$_instance = new $class($config);
		}
		return self::$_instance;
	}

	protected $_session;
	protected $_config;

	/**
	 * 加载 Session 和配置选项
	 * @return	void
	 */
	public function __construct($config = array())
	{
		// 保存对象中的配置信息
		$this->_config = $config;
		$this->_session = Session::instance($this->_config['session_type']);
	}

	abstract protected function _login($username, $password, $remember);

	abstract public function password($username);

	abstract public function check_password($password);

	/**
	 * 从 session 中取得当前登录用户
	 * 如果当前没有用户登录，则返回 NULL
	 * @return	mixed
	 */
	public function get_user($default = NULL)
	{
		return $this->_session->get($this->_config['session_key'], $default);
	}

	/**
	 * 尝试登录
	 * @param	string	要登录的用户名
	 * @param	string	要校验的密码
	 * @param	boolean	开启自动登录
	 * @return	boolean
	 */
	public function login($username, $password, $remember = false)
	{
		if (empty($password))
			return false;
		return $this->_login($username, $password, $remember);
	}

	/**
	 * 移除相关 session 变量，使用户退出
	 * @param	boolean	彻底销毁 session
	 * @return	boolean
	 */
	public function logout($destroy = false)
	{
		if ($destroy === true)
		{
			// 彻底销毁 session
			$this->_session->destroy();
		}
		else
		{
			// 从 session 中移除用户
			$this->_session->delete($this->_config['session_key']);
			// 重新生成 session_id
			$this->_session->regenerate();
		}
		// 双重验证
		return !$this->logged_in();
	}

	/**
	 * 检查是否存在一个激活的 session。
	 * @return	mixed
	 */
	public function logged_in()
	{
		return $this->get_user() !== NULL;
	}

	/**
	 * Perform a hmac hash, using the configured method.
	 * @param	string  string to hash
	 * @return	string
	 */
	public function hash($str)
	{
		if (!$this->_config['hash_key'])
			throw new BootPHP_Exception('必须在您的授权配置中设置一个有效的 hash 键。');
		return hash_hmac($this->_config['hash_method'], $str, $this->_config['hash_key']);
	}

	/**
	 * 完成登录
	 * @param	object	用户
	 * @return boolean
	 */
	protected function complete_login($user)
	{
		// 重新生成 session_id
		$this->_session->regenerate();
		// 将用户名存入 session
		$this->_session->set($this->_config['session_key'], $user);
		return true;
	}

}
