<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 包含 BootPHP 的底层辅助方法：
 *
 * - 环境初始化
 * - 在找级联文件系统内查找文件
 * - 类的自动加载与透明扩展
 * - 变量与路径调试
 *
 * @package BootPHP
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class BootPHP {

	// 发行版本
	const VERSION = '1.0.2';
	// 为保持一致性和便利性，设置通用环境类型常量
	const PRODUCTION = 10;
	const STAGING = 20;
	const TESTING = 30;
	const DEVELOPMENT = 40;
	// 添加到所有生成的 PHP 文件中的安全检查
	const FILE_SECURITY = '<?php defined(\'SYSPATH\') || exit(\'Access Denied.\');';
	// 缓存文件的格式: 头部, 缓存名称, 数据
	const FILE_CACHE = ":header \n\n// :name\n\n:data\n";

	/**
	 * @var string 当前环境名
	 */
	public static $environment = self::DEVELOPMENT;

	/**
	 * @var boolean 如果 BootPHP 运行在 windows 上，则为 true
	 */
	public static $is_windows = false;

	/**
	 * @var boolean 如果 [magic quotes] 已开启，则为 true
	 */
	public static $magic_quotes = false;

	/**
	 * @var boolean 是否记录错误与异常
	 */
	public static $log_errors = false;

	/**
	 * @var boolean 如果 PHP 安全模式为 on，则为 true
	 */
	public static $safe_mode = false;

	/**
	 * @var string
	 */
	public static $content_type = 'text/html';

	/**
	 * @var string 字符集
	 */
	public static $charset = 'utf-8';

	/**
	 * @var string 存储 BootPHP 的服务器名称
	 */
	public static $server_name = '';

	/**
	 * @var  array 该实例的主机名称列表
	 */
	public static $hostnames = array();

	/**
	 * @var string 应用的基URL
	 */
	public static $base_url = '/';

	/**
	 * @var string 应用索引文件，添加到 BootPHP 生成的链接中。由 [BootPHP::init] 设置。
	 */
	public static $index_file = 'index.php';

	/**
	 * @var string 缓存目录，由 [BootPHP::cache] 使用。由 [BootPHP::init] 设置。
	 */
	public static $cache_dir;

	/**
	 * @var integer 缓存的默认生命周期，以秒为单位。由 [BootPHP::cache] 使用。由 [BootPHP::init] 设置。
	 */
	public static $cache_life = 60;

	/**
	 * @var boolean 是否对 [BootPHP::find_file] 使用内部缓存，不应用到 [BootPHP::cache]。由 [BootPHP::init] 设置。
	 */
	public static $caching = false;

	/**
	 * @var boolean 是否开启 [profiling]（BootPHP/profiling）。由 [BootPHP::init] 设置。
	 */
	public static $profiling = true;

	/**
	 * @var boolean 开启 BootPHP 缓存并显示 PHP 错误与异常。由 [BootPHP::init] 设置。
	 */
	public static $errors = true;

	/**
	 * @var array 在停机时显示的错误类型
	 */
	public static $shutdown_errors = array(E_PARSE, E_ERROR, E_USER_ERROR);

	/**
	 * @var boolean 设置 X-Powered-By header
	 */
	public static $expose = false;

	/**
	 * @var object 日志对象
	 */
	public static $log;

	/**
	 * @var object 配置对象
	 */
	public static $config;

	/**
	 * @var boolean [BootPHP::init] 被调用了吗？
	 */
	protected static $_init = false;

	/**
	 * @var array 当前激活模块
	 */
	protected static $_modules = array();

	/**
	 * @var array 用于查找文件的包含路径
	 */
	protected static $_paths = array(APPPATH, SYSPATH);

	/**
	 * @var array 文件路径缓存，当 [BootPHP::init] 的 cacheing 为 true 时使用。
	 */
	protected static $_files = array();

	/**
	 * @var boolean 在执行期间文件路径缓存改变了吗？当 [BootPHP::init] 的 caching 为 true 时内部使用。
	 */
	protected static $_files_changed = false;

	/**
	 * 初始化环境：
	 *
	 * - 关闭 register_globals 和 magic_quotes_gpc
	 * - 决定当前环境
	 * - 设置全局
	 * - 净化 GET、POST 和 COOKIE 变量
	 * - 转换 GET、POST 和 COOKIE 变量为全局性质
	 *
	 * 可以有下面的设置：
	 *
	 * 类型      | 设置       | 描述                                                                                                                                                                                                  | 默认值
	 * ----------|------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------
	 * `string`  | base_url   | 您的应用的基 URL。它应该是从 DOCROOT 到 index.php 文件的“相对”路径。换句话说，如果 BootPHP 是在一个子文件夹下，将它设置到这个子文件夹，否则用默认值。开头的斜线是必需的，结尾的斜线是可选的。         | `'/'`
	 * `string`  | index_file | [front controller] 的名称。这是 BootPHP 用来生成像 [HTML::anchor()] 和 [URL::base()] 这样的相对 urls 的。通常是 `index.php`。要从 urls 中移除 index.php，把它设置为 `false`。                         | `'index.php'`
	 * `string`  | charset    | 用于所有输入和输出的字符集                                                                                                                                                                            | `'utf-8'`
	 * `string`  | cache_dir  | BootPHP 的缓存目录。[BootPHP::cache] 用于简单的内部缓存，比如 [Fragments](BootPHP/fragments) 和 **\[caching database queries](this should link somewhere)**. [Cache module](cache) 无须做任何事情。   | `APPPATH.'cache'`
	 * `integer` | cache_life | 生命周期，以秒为单位，[BootPHP::cache] 缓存项                                                                                                                                                         | `60`
	 * `boolean` | errors     | BootPHP 是否捕捉 PHP 错误和未捕获的异常，并显示 `error_view`。更多信息见 [Error Handling](BootPHP/errors)。<br /><br />推荐设置：开发时为 `true`，生产服务器上为 `false`。                              | `true`
	 * `boolean` | profile    | 是否开启 [Profiler](BootPHP/profiling)。<br /><br />找荐设置：开发时为 `true`，生产服务器上为 `false`。
	 * `boolean` | caching    | 缓存文件，以加快 [BootPHP::find_file] 的速度。[BootPHP::cache]、[Fragments](BootPHP/fragments) 或 [Cache module](cache) 无须做任何事情。<br /><br />推荐设置：开发时为 `false`，生产服务器上为 `true`。 | `false`
	 *
	 * @throws BootPHP_Exception
	 * @param array 设置数组。见上面。
	 * @return void
	 * @uses BootPHP::globals
	 * @uses BootPHP::sanitize
	 * @uses BootPHP::cache
	 * @uses Profiler
	 */
	public static function init(array $settings = NULL)
	{
		if (self::$_init)
		{
			// 不允许二次执行
			return;
		}
		// 现在 BootPHP 初始化了
		self::$_init = true;
		if (isset($settings['profile']))
		{
			// 启用分析
			self::$profiling = (bool) $settings['profile'];
		}
		// 启动输出缓冲
		ob_start();
		if (isset($settings['errors']))
		{
			// 启用错误处理
			self::$errors = (bool) $settings['errors'];
		}
		if (self::$errors === true)
		{
			// 启用 BootPHP 异常处理，添加堆栈跟踪和错误源。
			set_exception_handler(array('BootPHP_Exception', 'handler'));
			// 启用 BootPHP 错误处理，转换所有 PHP 错误为异常。
			set_error_handler(array('BootPHP', 'error_handler'));
		}
		// 启用 BootPHP 的关闭处理程序，以捕捉 E_FATAL 错误。
		register_shutdown_function(array('BootPHP', 'shutdown_handler'));
		if (ini_get('register_globals'))
		{
			// 反转 register_globals 的效果
			self::globals();
		}
		if (isset($settings['expose']))
		{
			self::$expose = (bool) $settings['expose'];
		}
		// 确定是否是在 Windows 环境下运行
		self::$is_windows = (DIRECTORY_SEPARATOR === '\\');
		// 确定是否是以安全模式运行
		self::$safe_mode = (bool) ini_get('safe_mode');
		if (isset($settings['cache_dir']))
		{
			if (!is_dir($settings['cache_dir']))
			{
				try
				{
					// 创建缓存目录
					mkdir($settings['cache_dir'], 0755, true);
					// 设置权限（必须手动设置，以修改 umask 问题）
					chmod($settings['cache_dir'], 0755);
				}
				catch (Exception $e)
				{
					throw new BootPHP_Exception('Could not create cache directory :dir', array(':dir' => Debug::path($settings['cache_dir'])));
				}
			}
			// 设置缓存目录路径
			self::$cache_dir = realpath($settings['cache_dir']);
		}
		else
		{
			// 使用默认缓存目录
			self::$cache_dir = APPPATH . 'cache';
		}
		if (!is_writable(self::$cache_dir))
		{
			throw new BootPHP_Exception('Directory :dir must be writable', array(':dir' => Debug::path(self::$cache_dir)));
		}
		if (isset($settings['cache_life']))
		{
			// 设置默认缓存生命周期
			self::$cache_life = (int) $settings['cache_life'];
		}
		if (isset($settings['caching']))
		{
			// 开启或关闭内部缓存
			self::$caching = (bool) $settings['caching'];
		}
		if (self::$caching === true)
		{
			// 加载文件路径缓存
			self::$_files = self::cache('self::find_file()');
		}
		if (isset($settings['charset']))
		{
			// 设置系统字符集
			self::$charset = strtolower($settings['charset']);
		}
		if (function_exists('mb_internal_encoding'))
		{
			// 对同样的字符集设置 MB 扩展
			mb_internal_encoding(self::$charset);
		}
		if (isset($settings['base_url']))
		{
			// 设置基 URL
			self::$base_url = rtrim($settings['base_url'], '/') . '/';
		}
		if (isset($settings['index_file']))
		{
			// 设置索引文件
			self::$index_file = trim($settings['index_file'], '/');
		}
		// 确定是否启用了极其邪恶的 magic quotes
		self::$magic_quotes = (bool) get_magic_quotes_gpc();
		// 清理所有请求变量
		$_GET = self::sanitize($_GET);
		$_POST = self::sanitize($_POST);
		$_COOKIE = self::sanitize($_COOKIE);
		// 加载记录器
		self::$log = Log::instance();
		// 加载配置
		self::$config = new Config;
	}

	/**
	 * 清理环境:
	 *
	 * - Restore the previous error && exception handlers
	 * - Destroy the BootPHP::$log && BootPHP::$config objects
	 *
	 * @return void
	 */
	public static function deinit()
	{
		if (self::$_init)
		{
			// 移除自动加载器
			spl_autoload_unregister(array('BootPHP', 'auto_load'));
			if (self::$errors)
			{
				// Go back to the previous error handler
				restore_error_handler();
				// Go back to the previous exception handler
				restore_exception_handler();
			}
			// Destroy objects created by init
			self::$log = self::$config = NULL;
			// Reset internal storage
			self::$_modules = self::$_files = array();
			self::$_paths = array(APPPATH, SYSPATH);
			// Reset file cache status
			self::$_files_changed = false;
			// BootPHP is no longer initialized
			self::$_init = false;
		}
	}

	/**
	 * Reverts the effects of the `register_globals` PHP setting by unsetting
	 * all global varibles except for the default super globals (GPCS, etc),
	 * which is a [potential security hole.][ref-wikibooks]
	 *
	 * This is called automatically by [BootPHP::init] if `register_globals` is on.
	 *
	 *
	 * [ref-wikibooks]: http://en.wikibooks.org/wiki/PHP_Programming/Register_Globals
	 *
	 * @return void
	 */
	public static function globals()
	{
		if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS']))
		{
			// Prevent malicious GLOBALS overload attack
			echo "Global variable overload attack detected! Request aborted.\n";
			// Exit with an error status
			exit(1);
		}
		// Get the variable names of all globals
		$global_variables = array_keys($GLOBALS);
		// Remove the standard global variables from the list
		$global_variables = array_diff($global_variables, array(
			'_COOKIE',
			'_ENV',
			'_GET',
			'_FILES',
			'_POST',
			'_REQUEST',
			'_SERVER',
			'_SESSION',
			'GLOBALS',
		));
		foreach ($global_variables as $name)
		{
			// Unset the global variable, effectively disabling register_globals
			unset($GLOBALS[$name]);
		}
	}

	/**
	 * Recursively sanitizes an input variable:
	 *
	 * - Strips slashes if magic quotes are enabled
	 * - Normalizes all newlines to LF
	 *
	 * @param mixed 任意变量
	 * @return mixed 已过滤的变量
	 */
	public static function sanitize($value)
	{
		if (is_array($value) || is_object($value))
		{
			foreach ($value as $key => $val)
			{
				// Recursively clean each value
				$value[$key] = self::sanitize($val);
			}
		}
		elseif (is_string($value))
		{
			if (self::$magic_quotes === true)
			{
				// Remove slashes added by magic quotes
				$value = stripslashes($value);
			}
			if (strpos($value, "\r") !== false)
			{
				// Standardize newlines
				$value = str_replace(array("\r\n", "\r"), "\n", $value);
			}
		}
		return $value;
	}

	/**
	 * Provides auto-loading support of classes that follow BootPHP's [class
	 * naming conventions](bootphp/conventions#class-names-and-file-location).
	 * See [Loading Classes](bootphp/autoloading) for more information.
	 *
	 * Class names are converted to file names by making the class name
	 * lowercase && converting underscores to slashes:
	 *
	 *     // 载入 classes/my/class/name.php
	 *     BootPHP::auto_load('My_Class_Name');
	 *
	 * You should never have to call this function, as simply calling a class
	 * will cause it to be called.
	 *
	 * This function must be enabled as an autoloader in the bootstrap:
	 *
	 *     spl_autoload_register(array('BootPHP', 'auto_load'));
	 *
	 * @param string $class 类名
	 * @return boolean
	 */
	public static function auto_load($class)
	{
		try
		{
			// 将类名转换成路径
			$file = str_replace('_', '/', strtolower($class));
			if ($path = self::find_file('classes', $file))
			{
				// 载入类文件
				require $path;
				// 类已经找到
				return true;
			}
			// 类在文件系统中不存在
			return false;
		}
		catch (Exception $e)
		{
			BootPHP_Exception::handler($e);
			exit;
		}
	}

	/**
	 * Changes the currently enabled modules. Module paths may be relative
	 * or absolute, but must point to a directory:
	 *
	 *     BootPHP::modules(array('modules/foo', MODPATH.'bar'));
	 *
	 * @param array list of module paths
	 * @return array enabled modules
	 */
	public static function modules(array $modules = NULL)
	{
		if ($modules === NULL)
		{
			// Not changing modules, just return the current set
			return self::$_modules;
		}
		// Start a new list of include paths, APPPATH first
		$paths = array(APPPATH);
		foreach ($modules as $name => $path)
		{
			if (is_dir($path))
			{
				// Add the module to include paths
				$paths[] = $modules[$name] = realpath($path) . DIRECTORY_SEPARATOR;
			}
			else
			{
				// This module is invalid, remove it
				throw new BootPHP_Exception('Attempted to load an invalid || missing module \':module\' at \':path\'', array(
				':module' => $name,
				':path' => Debug::path($path),
				));
			}
		}
		// Finish the include paths by adding SYSPATH
		$paths[] = SYSPATH;
		// Set the new include paths
		self::$_paths = $paths;
		// Set the current module list
		self::$_modules = $modules;
		foreach (self::$_modules as $path)
		{
			$init = $path . 'init.php';
			if (is_file($init))
			{
				// Include the module initialization file once
				require_once $init;
			}
		}
		return self::$_modules;
	}

	/**
	 * Returns the the currently active include paths, including the
	 * application, system, and each module's path.
	 *
	 * @return array
	 */
	public static function include_paths()
	{
		return self::$_paths;
	}

	/**
	 * Searches for a file in the [Cascading Filesystem](bootphp/files), and
	 * returns the path to the file that has the highest precedence, so that it
	 * can be included.
	 *
	 * When searching the "config", "messages", || "i18n" directories, || when
	 * the `$array` flag is set to true, an array of all the files that match
	 * that path in the [Cascading Filesystem](bootphp/files) will be returned.
	 * These files will return arrays which must be merged together.
	 *
	 * If no extension is given, the default extension (`EXT` set in
	 * `index.php`) will be used.
	 *
	 *     // Returns an absolute path to views/template.php
	 *     BootPHP::find_file('views', 'template');
	 *
	 *     // Returns an absolute path to media/css/style.css
	 *     BootPHP::find_file('media', 'css/style', 'css');
	 *
	 *     // Returns an array of all the "mimes" configuration files
	 *     BootPHP::find_file('config', 'mimes');
	 *
	 * @param string $dir 目录名（views, i18n, classes, extensions, 等等）
	 * @param string $file 带子目录的文件名
	 * @param string $ext 要搜索的扩展
	 * @param boolean $array 返回文件的数组吗？
	 * @return array $array 为 true 时，返回文件列表
	 * @return string 单个文件路径
	 */
	public static function find_file($dir, $file, $ext = NULL, $array = false)
	{
		if ($ext === NULL)
		{
			// 使用默认扩展
			$ext = '.php';
		}
		elseif ($ext)
		{
			// Prefix the extension with a period
			$ext = ".{$ext}";
		}
		else
		{
			// 不使用扩展
			$ext = '';
		}
		// Create a partial path of the filename
		$path = $dir . DIRECTORY_SEPARATOR . $file . $ext;
		if (self::$caching === true && isset(self::$_files[$path . ($array ? '_array' : '_path')]))
		{
			// This path has been cached
			return self::$_files[$path . ($array ? '_array' : '_path')];
		}
		if ($array || $dir === 'config' || $dir === 'i18n' || $dir === 'messages')
		{
			// Include paths must be searched in reverse
			$paths = array_reverse(self::$_paths);
			// Array of files that have been found
			$found = array();
			foreach ($paths as $dir)
			{
				if (is_file($dir . $path))
				{
					// This path has a file, add it to the list
					$found[] = $dir . $path;
				}
			}
		}
		else
		{
			// The file has not been found yet
			$found = false;
			foreach (self::$_paths as $dir)
			{
				if (is_file($dir . $path))
				{
					// 路径已找到
					$found = $dir . $path;
					// 停止搜索
					break;
				}
			}
		}
		if (self::$caching === true)
		{
			// Add the path to the cache
			self::$_files[$path . ($array ? '_array' : '_path')] = $found;
			// Files have been changed
			self::$_files_changed = true;
		}
		return $found;
	}

	/**
	 * 在指定目录的【级联文件系统（bootphp/files）】的任何位置递归查找所有的文件，并返回所有找到的文件的数组，按字母顺序排序。
	 *
	 *     // 查找所有视图文件。
	 *     $views = BootPHP::list_files('views');
	 *
	 * @param string 目录名
	 * @param array 要搜索的路径列表
	 * @return array
	 */
	public static function list_files($directory = NULL, array $paths = NULL)
	{
		if ($directory !== NULL)
		{
			// 添加目录分隔符
			$directory .= DIRECTORY_SEPARATOR;
		}
		if ($paths === NULL)
		{
			// 使用默认路径
			$paths = self::$_paths;
		}
		// 为文件创建数组
		$found = array();
		foreach ($paths as $path)
		{
			if (is_dir($path . $directory))
			{
				// 创建一个新的目录迭代器
				$dir = new DirectoryIterator($path . $directory);
				foreach ($dir as $file)
				{
					// 取得文件名
					$filename = $file->getFilename();
					if ($filename[0] === '.' || $filename[strlen($filename) - 1] === '~')
					{
						// 路过所有的隐藏文件和 UNIX 备份文件
						continue;
					}
					// 相对文件名是数组的键
					$key = $directory . $filename;
					if ($file->isDir())
					{
						if ($sub_dir = self::list_files($key, $paths))
						{
							if (isset($found[$key]))
							{
								// 追加子目录列表
								$found[$key] += $sub_dir;
							}
							else
							{
								// 创建新的子目录列表
								$found[$key] = $sub_dir;
							}
						}
					}
					else
					{
						if (!isset($found[$key]))
						{
							// 将新文件到添加列表中
							$found[$key] = realpath($file->getPathName());
						}
					}
				}
			}
		}
		// 按字母顺序排序结果
		ksort($found);
		return $found;
	}

	/**
	 * 在完全空的范围内，载入一个文件，并返回输出：
	 *
	 *     $foo = BootPHP::load('foo.php');
	 *
	 * @param string
	 * @return mixed
	 */
	public static function load($file)
	{
		return include $file;
	}

	/**
	 * 为字符串和数组提供简单的基于文件的缓存：
	 *
	 *     // 设置 'foo' 缓存
	 *     BootPHP::cache('foo', 'Hello, world');
	 *
	 *     // 得到 'foo' 缓存
	 *     $foo = BootPHP::cache('foo');
	 *
	 * 所有缓存存储为由 var_export() 生成的 PHP 代码。
	 * 缓存对象可能不会按照期望工作。存储具有递归的引用、对象或数组，将导致 E_FATAL。
	 *
	 * 缓存目录与默认缓存生命周期由 BootPHP::init 设置
	 *
	 * @throws BootPHP_Exception
	 * @param string $name 缓存名
	 * @param mixed $data 要缓存的数据
	 * @param integer $lifetime 缓存有效的秒数
	 * @return mixed 用于获取
	 * @return boolean 用于设置
	 */
	public static function cache($name, $data = NULL, $lifetime = NULL)
	{
		// 缓存文件是名字的一个散列值
		$file = sha1($name) . '.txt';
		// 缓存目录按键分开，以防止文件系统过载
		$dir = self::$cache_dir . DIRECTORY_SEPARATOR . $file[0] . $file[1] . DIRECTORY_SEPARATOR;
		if ($lifetime === NULL)
		{
			// 使用默认生命周期
			$lifetime = self::$cache_life;
		}
		if ($data === NULL)
		{
			if (is_file($dir . $file))
			{
				if ((time() - filemtime($dir . $file)) < $lifetime)
				{
					// 返回缓存
					try
					{
						return unserialize(file_get_contents($dir . $file));
					}
					catch (Exception $e)
					{
						// 缓存已损坏，让返回正常发生。
					}
				}
				else
				{
					try
					{
						// 缓存已过期
						unlink($dir . $file);
					}
					catch (Exception $e)
					{
						// 缓存很可能已被删除，让返回正常发生。
					}
				}
			}
			// 缓存未找到
			return NULL;
		}
		if (!is_dir($dir))
		{
			// 创建缓存目录
			mkdir($dir, 0777, true);
			// 设置权限（必须手动设置，以修正 Linux 的 umask 问题）
			chmod($dir, 0777);
		}
		// 强制数据作为字符串
		$data = serialize($data);
		try
		{
			// 写缓存
			return (bool) file_put_contents($dir . $file, $data, LOCK_EX);
		}
		catch (Exception $e)
		{
			// 写缓存失败
			return false;
		}
	}

	/**
	 * 从文件获取消息。消息是存储在 `messages/` 目录下用一个键来引用的任意字符串。
	 * 翻译不是由返回的值来完成的。更多信息见消息文件（bootphp/files/messages）。
	 *
	 *     // 从 messages/text.php 中得到 'username'
	 *     $username = BootPHP::message('text', 'username');
	 *
	 * @param string $file 文件名
	 * @param string $path 要得到的键路径
	 * @param mixed $default 路径不存在时使用的默认值
	 * @return string 给定路径的消息字符串
	 * @return array 没有指定路径时，返回完整的消息列表
	 * @uses Arr::merge
	 * @uses Arr::path
	 */
	public static function message($file, $path = NULL, $default = NULL)
	{
		static $messages;
		if (!isset($messages[$file]))
		{
			// 创建一个新的消息列表
			$messages[$file] = array();
			if ($files = self::find_file('messages', $file))
			{
				foreach ($files as $f)
				{
					// 将所有的消息递归
					$messages[$file] = Arr::merge($messages[$file], self::load($f));
				}
			}
		}
		if ($path === NULL)
		{
			// 返回所有消息
			return $messages[$file];
		}
		else
		{
			// 使用路径得到消息
			return Arr::path($messages[$file], $path, $default);
		}
	}

	/**
	 * PHP 错误处理器，将所有的错误转换为 ErrorExceptions。这个处理器关联 error_reporting 设置。
	 *
	 * @throws ErrorException
	 * @return true
	 */
	public static function error_handler($code, $error, $file = NULL, $line = NULL)
	{
		if (error_reporting() & $code)
		{
			// 错误不是由当前 error_reporting 设置来抑制的。
			// 将错误转换成一个 ErrorException。
			throw new ErrorException($error, $code, 0, $file, $line);
		}
		return true;
	}

	/**
	 * 捕获那些没有被错误处理器捕获的错误，如 E_PARSE。
	 *
	 * @uses BootPHP_Exception::handler
	 * @return void
	 */
	public static function shutdown_handler()
	{
		if (!self::$_init)
		{
			// 未激活时不执行
			return;
		}
		try
		{
			if (self::$caching === true && self::$_files_changed === true)
			{
				// 写文件路径缓存
				self::cache('self::find_file()', self::$_files);
			}
		}
		catch (Exception $e)
		{
			// 将异常传递给处理器
			BootPHP_Exception::handler($e);
		}
		if (self::$errors && $error = error_get_last() && in_array($error['type'], self::$shutdown_errors))
		{
			// 清理输出缓冲
			ob_get_level() && ob_clean();
			// 为了友好调试，假设一个异常
			BootPHP_Exception::handler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
			// 现在关闭，以避免“死循环”
			exit;
		}
	}

}
