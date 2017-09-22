<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 数据库授权驱动
 *
 * @package	BootPHP/授权
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Auth_Db extends Auth {

	/**
	 * Checks if a session is active.
	 *
	 * @param mixed $role Role name string, role ORM object, or array with role names
	 * @return boolean
	 */
	public function logged_in()
	{
		// 从 session 中得到用户
		$user = $this->get_user();
		if ($user)
			return true;
		else
			return false;
	}

	/**
	 * 登录一个用户
	 *
	 * @param string 用户名
	 * @param string 密码
	 * @param boolean 开启自动登录
	 * @return boolean
	 */
	protected function _login($username, $password, $remember)
	{
		$user = NULL;
		if (is_string($username))
		{
			// 加载用户信息
			if (Valid::email($username))
			{
				$user = Model::factory('User')->loadByEmail($username);
			}
			else
			{
				$user = Model::factory('User')->loadByUsername($username);
			}
		}
		if (is_string($password))
		{
			// 创建加密的密码
			$password = $this->hash($password);
		}
		// 如果密码匹配，完成登录
		if ($user && $user->password === $password)
		{
			if ($remember === true)
			{
				$time = time();
				// Token 数据
				$create = new stdClass();
				$create->user_id = $user->id;
				$create->created = $time;
				$create->expires = $time + $this->_config['lifetime'];
				$create->user_agent = sha1(Request::$user_agent);
				// 创建一个新的自动登录令牌
				$token = Model::factory('user_token');
				$token = $token->create($create);
				// 设置自动登录 Cookie
				Cookie::set('authautologin', $token->token, $this->_config['lifetime']);
			}
			// 完成登录
			$this->complete_login($user);
			return true;
		}
		// 登录失败
		return false;
	}

	/**
	 * 登录一个用户，基于 COOKIE 验证自动登录
	 *
	 * @return mixed
	 */
	public function auto_login()
	{
		if ($tokenCookie = Cookie::get('authautologin'))
		{
			// 加载令牌与用户
			$token = Model::factory('user_token');
			$tokenInfo = $token->loadByToken($tokenCookie);
			if ($tokenInfo->id)
			{
				if ($tokenInfo->user_agent === sha1(Request::$user_agent))
				{
					// 设置新的令牌
					Cookie::set('authautologin', $tokenInfo->token, $tokenInfo->expires - time());
					// 用找到的数据完成登录
					$user = Model::factory('User')->load($tokenInfo->user_id);
					$this->complete_login($user);
					// 自动登录成功
					return $user;
				}
				// 令牌无效
				$token->delete();
			}
		}
		return false;
	}

	/**
	 * 从 session 中取得当前登录用户（用于自动登录检查）。
	 * 如果当前没有登录用户，则返回 false。
	 *
	 * @return mixed
	 */
	public function get_user($default = NULL)
	{
		$user = parent::get_user($default);
		if (!$user)
		{
			// 检查“自动登录”
			$user = $this->auto_login();
		}
		return $user;
	}

	/**
	 * 退出用户，并移除所有自动登录 Cookie
	 *
	 * @param boolean 彻底摧毁 session
	 * @return boolean
	 */
	public function logout($destroy = false)
	{
		if ($tokenCookie = Cookie::get('authautologin'))
		{
			// 删除自动登录 Cookie，防止重新登录
			Cookie::delete('authautologin');
			// 从数据库中清除自动登录令牌
			$token = Model::factory('user_token');
			$tokenInfo = $token->loadByToken($tokenCookie);
			if ($tokenInfo->id)
			{
				$token->delete();
			}
		}
		return parent::logout($destroy);
	}

	/**
	 * 为用户名取得存储的密码
	 *
	 * @param mixed 用户名字符串或用户对象
	 * @return string
	 */
	public function password($user)
	{
		if (!is_object($user))
		{
			$username = $user;
			// 加载用户
			$user = Model::factory('User');
			$user = $user->loadByUsername($username);
		}
		return $user->password;
	}

	/**
	 * 完成用户登录，增加登录次数，并设置 Session 数据
	 *
	 * @param object 用户
	 * @return void
	 */
	protected function complete_login($user)
	{
		// 保存用户
		Model::factory('User')->updateLogin($user->id);
		return parent::complete_login($user);
	}

	/**
	 * 与原始密码（加密的）比较。只为当前（已登录）用户。
	 *
	 * @param string 密码
	 * @return boolean
	 */
	public function check_password($password)
	{
		$user = $this->get_user();
		if (!$user)
			return false;
		return ($this->hash($password) === $user->password);
	}

}
