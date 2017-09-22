<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 作为 HTML 页的包装对象，嵌入 PHP，称作“视图”。
 * 用视图对象可以将变量分配并在视图内部引用。
 *
 * @package BootPHP
 * @category Base
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class View {

	// 全局变量数组
	protected static $_global_data = array();

	/**
	 * 返回一个新的视图对象。如果没有定义“file”参数，必须调用 [View::set_filename]。
	 *
	 * 	$view = View::factory($file);
	 *
	 * @param string 视图文件名
	 * @param array 值的数组
	 * @return View
	 */
	public static function factory($file = NULL, array $data = NULL)
	{
		return new View($file, $data);
	}

	/**
	 * 视图包含进来时，捕捉生成的输出。
	 * 视图数据将被提取出来构造局部变量。这个方法是静态的，以防止对象范围解析。
	 *
	 * 	$output = View::capture($file, $data);
	 *
	 * @param string 文件名
	 * @param array 变量
	 * @return string
	 */
	protected static function capture($bootphp_view_filename, array $bootphp_view_data)
	{
		// 将视图变量导入到局部命名空间
		extract($bootphp_view_data, EXTR_SKIP);
		if (View::$_global_data)
		{
			// 将全局视图变量导入到局部命名空间
			extract(View::$_global_data, EXTR_SKIP);
		}
		// 捕捉视图输出
		ob_start();
		try
		{
			// 在当前范围内加载视图
			include $bootphp_view_filename;
		}
		catch (Exception $e)
		{
			// 删除输出缓冲
			ob_end_clean();
			// 重新抛出异常
			throw $e;
		}
		// 获得捕捉到的输出并关闭缓冲
		return ob_get_clean();
	}

	/**
	 * 设置全局变量，与 [View::set] 类似，不同的是变量可以被所有视图访问。
	 *
	 * 	View::set_global($name, $value);
	 *
	 * @param string 变量名
	 * @param mixed 值
	 * @return void
	 */
	public static function set_global($key, $value = NULL)
	{
		View::$_global_data[$key] = $value;
	}

	/**
	 * 通过引用分配全局变量，与 [View::bind] 类似，不同的是变量可以被所有视图访问。
	 *
	 * 	View::bind_global($key, $value);
	 *
	 * @param string 变量名
	 * @param mixed 引用的变量
	 * @return void
	 */
	public static function bind_global($key, &$value)
	{
		View::$_global_data[$key] = &$value;
	}

	// 视图文件名
	protected $_file;
	// 局部变量数组
	protected $_data = array();

	/**
	 * 设置初始的视图文件名和局部数据。视图应该总是由 [View::factory] 来创建。
	 *
	 * 	$view = new View($file);
	 *
	 * @param string 视图文件名
	 * @param array 值的数组
	 * @return void
	 * @uses View::set_filename
	 */
	public function __construct($file = NULL, array $data = NULL)
	{
		if ($file !== NULL)
		{
			$this->set_filename($file);
		}
		if ($data !== NULL)
		{
			// 将值添加到当前数据
			$this->_data = $data + $this->_data;
		}
	}

	/**
	 * 魔术方法，搜索指定的变量并返回它的值。
	 * 局部变量在全局变量之前返回。
	 *
	 * 	$value = $view->foo;
	 *
	 * [!!] 如果没有设置变量，将会抛出异常。
	 *
	 * @param string 变量名
	 * @return mixed
	 * @throws	BootPHP_Exception
	 */
	public function & __get($key)
	{
		if (array_key_exists($key, $this->_data))
		{
			return $this->_data[$key];
		}
		elseif (array_key_exists($key, View::$_global_data))
		{
			return View::$_global_data[$key];
		}
		else
		{
			throw new BootPHP_Exception('View variable is not set: :var', array(':var' => $key));
		}
	}

	/**
	 * 魔术方法，用相同的参数调用 [View::set]。
	 *
	 * 	$view->foo = 'something';
	 *
	 * @param string 变量名
	 * @param mixed 值
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * 魔术方法，确定变量是否已设置。
	 *
	 * 	isset($view->foo);
	 *
	 * [!!] `NULL` 变量被 [isset](http://php.net/isset) 视为未设置。
	 *
	 * @param string 变量名
	 * @return boolean
	 */
	public function __isset($key)
	{
		return (isset($this->_data[$key]) || isset(View::$_global_data[$key]));
	}

	/**
	 * 魔术方法，消除指定的变量。
	 *
	 * 	unset($view->foo);
	 *
	 * @param string 变量名
	 * @return void
	 */
	public function __unset($key)
	{
		unset($this->_data[$key], View::$_global_data[$key]);
	}

	/**
	 * 魔术方法，返回 [View::render] 的输出。
	 *
	 * @return string
	 * @uses View::render
	 */
	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch (Exception $e)
		{
			// 显示异常信息
			BootPHP_Exception::handler($e);
			return '';
		}
	}

	/**
	 * 设置视图文件名
	 *
	 * 	$view->set_filename($file);
	 *
	 * @param string 视图文件名
	 * @return View
	 * @throws	View_Exception
	 */
	public function set_filename($file)
	{
		if (($path = BootPHP::find_file('views', $file)) === false)
		{
			throw new View_Exception('The requested view :file could not be found', array(
			':file' => $file,
			));
		}
		// 存储文件路径
		$this->_file = $path;
		return $this;
	}

	/**
	 * 通过名分配一个值。
	 * 分配的值将在视图文件内作为变量使用：
	 *
	 * 	// 该值可以在视图中用 $foo 来访问
	 * 	$view->set('foo', '我的值');
	 *
	 * @param string 变量名
	 * @param mixed 值
	 * @return $this
	 */
	public function set($key, $value = NULL)
	{
		$this->_data[$key] = $value;
		return $this;
	}

	/**
	 * 通过引用分配一个值。
	 * 绑定的好处是，值可以在不重新设置的情况下而改变，也可以在有值之前绑定变量。分配的值将在视图文件内作为变量使用：
	 *
	 * 	// 该引用可以在视图中用 $ref 来访问
	 * 	$view->bind('ref', $bar);
	 *
	 * @param string 变量名
	 * @param mixed 引用的变量
	 * @return $this
	 */
	public function bind($key, &$value)
	{
		$this->_data[$key] = &$value;
		return $this;
	}

	/**
	 * 将视图对象渲染成字符串。全局和局部数据合并并提取出来，在视图文件内创建局部变量。
	 * 	$output = $view->render();
	 *
	 * [!!] 与局部变量键名相同的全局变量，会被局部变量覆盖。
	 *
	 * @param string 视图文件名
	 * @return string
	 * @throws	View_Exception
	 * @uses View::capture
	 */
	public function render($file = NULL)
	{
		if ($file !== NULL)
		{
			$this->set_filename($file);
		}
		if (empty($this->_file))
		{
			throw new View_Exception('You must set the file to use within your view before rendering');
		}
		// 合并局部与全局数据，并捕获输出
		return View::capture($this->_file, $this->_data);
	}

}
