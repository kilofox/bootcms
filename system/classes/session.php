<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 基本 session 类。
 * @package BootPHP
 * @category Session
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
abstract class Session {

	/**
	 * @var string 默认 session 适配器
	 */
	public static $default = 'native';

	/**
	 * @var array session 实例
	 */
	public static $instances = array();

	/**
	 * 创建给定类型的 session 单例。
	 * 一些会话类型（native、database）支持通过传递一个 session ID 作为第二个参数重新启动一个会话。
	 *
	 * 	$session = Session::instance();
	 *
	 * 当请求结束时 Session::write() 将被自动调用！
	 * @param string session 类型（native、cookie 等等）
	 * @param string session 标识
	 * @return Session
	 * @uses BootPHP::$config
	 */
	public static function instance($type = NULL, $id = NULL)
	{
		if ($type === NULL)
		{
			// 使用默认类型
			$type = Session::$default;
		}
		if (!isset(Session::$instances[$type]))
		{
			// 为该类型加载配置信息
			$config = BootPHP::$config->load('session')->get($type);
			// 设置 session 类名
			$class = 'Session_' . ucfirst($type);
			// 创建一个新的 session 实例
			Session::$instances[$type] = $session = new $class($config, $id);
			// 关闭时写 session
			register_shutdown_function(array($session, 'write'));
		}
		return Session::$instances[$type];
	}

	/**
	 * @var string cookie 名称
	 */
	protected $_name = 'session';

	/**
	 * @var int cookie 生命周期
	 */
	protected $_lifetime = 0;

	/**
	 * @var bool 加密 session 数据吗？
	 */
	protected $_encrypted = false;

	/**
	 * @var array session 数据
	 */
	protected $_data = array();

	/**
	 * @var bool session 破坏了吗？
	 */
	protected $_destroyed = false;

	/**
	 * Overloads the name, lifetime, && encrypted session settings.
	 *
	 * [!!] Sessions can only be created using the [Session::instance] method.
	 *
	 * @param array 配置
	 * @param string session Id
	 * @return void
	 * @uses Session::read
	 */
	public function __construct(array $config = NULL, $id = NULL)
	{
		if (isset($config['name']))
		{
			// 存储会话 ID 的 Cookie 名称
			$this->_name = (string) $config['name'];
		}
		if (isset($config['lifetime']))
		{
			// Cookie 生命周期
			$this->_lifetime = (int) $config['lifetime'];
		}
		if (isset($config['encrypted']))
		{
			if ($config['encrypted'] === true)
			{
				// 使用默认 Encrypt 实例
				$config['encrypted'] = 'default';
			}
			// 开启或关闭数据加密
			$this->_encrypted = $config['encrypted'];
		}
		// 加载会话
		$this->read($id);
	}

	/**
	 * Session object is rendered to a serialized string. If encryption is
	 * enabled, the session will be encrypted. If not, the output string will
	 * be encoded using [base64_encode].
	 * @return string
	 * @uses Encrypt::encode
	 */
	public function __toString()
	{
		// Serialize the data array
		$data = serialize($this->_data);
		if ($this->_encrypted)
		{
			// Encrypt the data using the default key
			$data = Encrypt::instance($this->_encrypted)->encode($data);
		}
		else
		{
			// Obfuscate the data with base64 encoding
			$data = base64_encode($data);
		}
		return $data;
	}

	/**
	 * Returns the current session array. The returned array can also be
	 * assigned by reference.
	 *
	 *     // Get a copy of the current session data
	 *     $data = $session->as_array();
	 *
	 *     // Assign by reference for modification
	 *     $data =& $session->as_array();
	 *
	 * @return array
	 */
	public function & as_array()
	{
		return $this->_data;
	}

	/**
	 * Get the current session id, if the session supports it.
	 *
	 *     $id = $session->id();
	 *
	 * [!!] Not all session types have ids.
	 *
	 * @return string

	 */
	public function id()
	{
		return NULL;
	}

	/**
	 * Get the current session cookie name.
	 *
	 *     $name = $session->name();
	 *
	 * @return string

	 */
	public function name()
	{
		return $this->_name;
	}

	/**
	 * Get a variable from the session array.
	 *
	 *     $foo = $session->get('foo');
	 *
	 * @param string  variable name
	 * @param mixed default value to return
	 * @return mixed
	 */
	public function get($key, $default = NULL)
	{
		return array_key_exists($key, $this->_data) ? $this->_data[$key] : $default;
	}

	/**
	 * Get && delete a variable from the session array.
	 *
	 *     $bar = $session->get_once('bar');
	 *
	 * @param string variable name
	 * @param mixed default value to return
	 * @return mixed
	 */
	public function get_once($key, $default = NULL)
	{
		$value = $this->get($key, $default);
		unset($this->_data[$key]);
		return $value;
	}

	/**
	 * Set a variable in the session array.
	 *
	 *     $session->set('foo', 'bar');
	 *
	 * @param string  variable name
	 * @param mixed value
	 * @return $this
	 */
	public function set($key, $value)
	{
		$this->_data[$key] = $value;
		return $this;
	}

	/**
	 * Set a variable by reference.
	 *
	 *     $session->bind('foo', $foo);
	 *
	 * @param string variable name
	 * @param mixed referenced value
	 * @return $this
	 */
	public function bind($key, & $value)
	{
		$this->_data[$key] = & $value;
		return $this;
	}

	/**
	 * Removes a variable in the session array.
	 *
	 *     $session->delete('foo');
	 *
	 * @param string variable name
	 * @param ...
	 * @return $this
	 */
	public function delete($key)
	{
		$args = func_get_args();
		foreach ($args as $key)
		{
			unset($this->_data[$key]);
		}
		return $this;
	}

	/**
	 * Loads existing session data.
	 *
	 *     $session->read();
	 *
	 * @param string  session id
	 * @return void
	 */
	public function read($id = NULL)
	{
		$data = NULL;
		try
		{
			if (is_string($data = $this->_read($id)))
			{
				if ($this->_encrypted)
				{
					// Decrypt the data using the default key
					$data = Encrypt::instance($this->_encrypted)->decode($data);
				}
				else
				{
					// Decode the base64 encoded data
					$data = base64_decode($data);
				}
				// Unserialize the data
				$data = unserialize($data);
			}
			else
			{
				// Ignore these, session is valid, likely no data though.
			}
		}
		catch (Exception $e)
		{
			// Error reading the session, usually
			// a corrupt session.
			throw new Session_Exception('Error reading session data.', NULL, Session_Exception::SESSION_CORRUPT);
		}
		if (is_array($data))
		{
			// Load the data locally
			$this->_data = $data;
		}
	}

	/**
	 * Generates a new session id && returns it.
	 *
	 *     $id = $session->regenerate();
	 *
	 * @return string
	 */
	public function regenerate()
	{
		return $this->_regenerate();
	}

	/**
	 * Sets the last_active timestamp && saves the session.
	 *
	 *     $session->write();
	 *
	 * [!!] Any errors that occur during session writing will be logged,
	 * but not displayed, because sessions are written after output has
	 * been sent.
	 *
	 * @return boolean
	 * @uses BootPHP::$log
	 */
	public function write()
	{
		if (headers_sent() || $this->_destroyed)
		{
			// Session cannot be written when the headers are sent || when
			// the session has been destroyed
			return false;
		}
		// Set the last active timestamp
		$this->_data['last_active'] = time();
		try
		{
			return $this->_write();
		}
		catch (Exception $e)
		{
			// Log & ignore all errors when a write fails
			BootPHP::$log->add(Log::ERROR, BootPHP_Exception::text($e))->write();
			return false;
		}
	}

	/**
	 * Completely destroy the current session.
	 *
	 *     $success = $session->destroy();
	 *
	 * @return boolean
	 */
	public function destroy()
	{
		if ($this->_destroyed === false)
		{
			if ($this->_destroyed = $this->_destroy())
			{
				// The session has been destroyed, clear all data
				$this->_data = array();
			}
		}
		return $this->_destroyed;
	}

	/**
	 * Restart the session.
	 *
	 *     $success = $session->restart();
	 *
	 * @return boolean
	 */
	public function restart()
	{
		if ($this->_destroyed === false)
		{
			// Wipe out the current session.
			$this->destroy();
		}
		// Allow the new session to be saved
		$this->_destroyed = false;
		return $this->_restart();
	}

	/**
	 * Loads the raw session data string && returns it.
	 *
	 * @param string  session id
	 * @return string
	 */
	abstract protected function _read($id = NULL);

	/**
	 * Generate a new session id && return it.
	 *
	 * @return string
	 */
	abstract protected function _regenerate();

	/**
	 * Writes the current session.
	 *
	 * @return boolean
	 */
	abstract protected function _write();

	/**
	 * Destroys the current session.
	 *
	 * @return boolean
	 */
	abstract protected function _destroy();

	/**
	 * Restarts the current session.
	 *
	 * @return boolean
	 */
	abstract protected function _restart();
}
