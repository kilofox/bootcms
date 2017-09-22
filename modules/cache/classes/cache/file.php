<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * BootPHP 缓存文件驱动。
 * 这是最慢的缓存方式之一。
 *
 * --- 设置举例 ---
 * 	return array(
 * 		'file'	=> array(					// 文件驱动组
 * 			'driver'	=> 'file',			// 使用文件驱动
 * 			'cache_dir'	=> APPPATH.'cache',	// 缓存位置
 * 		)
 * 	);
 *
 * 在只需要一个缓存组的情况下，如果组名命名为“default”，那么实例化一个缓存实例时，就不需要传递组名。
 *
 * --- 一般缓存组配置 ---
 * 	名称		 | 必需	 | 描述
 * ------------- | ----- | ------------------------------------
 * 	driver		 | 是	 | （字符串）要使用的驱动类型
 * 	cache_dir	 | 否	 | （字符串）该缓存实例要使用的缓存路径
 *
 * --- 系统需求 ---
 * 	PHP 5.2.4 或更高
 *
 * @package		BootPHP/缓存
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Cache_File extends Cache {

	/**
	 * 创建一个基于字符串的加密的文件名。用于为每一个缓存文件创建短的惟一 ID。
	 *
	 * 	// 创建缓存文件名
	 * 	$filename = Cache_File::filename($this->_sanitize_id($id));
	 *
	 * @param	string	要加密的文件名字符串
	 * @return	string
	 */
	protected static function filename($string)
	{
		return sha1($string) . '.cache';
	}

	/**
	 * @var		string	缓存目录
	 */
	protected $_cache_dir;

	/**
	 * 构造文件缓存驱动。这个方法不能被外部调用。这个文件缓存驱动必须使用 Cache::instance() 方法来实例化。
	 * @param	array	设置
	 * @throws	Cache_Exception
	 */
	protected function __construct(array $config)
	{
		// 设置父类
		parent::__construct($config);
		try
		{
			$directory = Arr::get($this->_config, 'cache_dir', BootPHP::$cache_dir);
			$this->_cache_dir = new SplFileInfo($directory);
		}
		// PHP < 5.3 异常处理
		catch (ErrorException $e)
		{
			$this->_cache_dir = $this->_make_directory($directory, 0777, true);
		}
		// PHP >= 5.3 异常处理
		catch (UnexpectedValueException $e)
		{
			$this->_cache_dir = $this->_make_directory($directory, 0777, true);
		}
		// 如果定义的目录是一个文件，那就从这儿滚出去
		if ($this->_cache_dir->isFile())
		{
			throw new Cache_Exception('不能用一个已经存在的文件 :resource 作为缓存目录', array(':resource' => $this->_cache_dir->getRealPath()));
		}
		// 检查目录的读状态
		if (!$this->_cache_dir->isReadable())
		{
			throw new Cache_Exception('缓存目录 :resource 不可读', array(':resource' => $this->_cache_dir->getRealPath()));
		}
		// 检查目录的写状态
		if (!$this->_cache_dir->isWritable())
		{
			throw new Cache_Exception('缓存目录 :resource 不可写', array(':resource' => $this->_cache_dir->getRealPath()));
		}
	}

	/**
	 * 根据 id 获取一个缓存值项
	 *
	 * 	// 从文件组获取缓存项
	 * 	$data = Cache::instance('file')->get('foo');
	 *
	 * 	// 从文件组获取缓存项，如果丢失，则返回 'bar'
	 * 	$data = Cache::instance('file')->get('foo', 'bar');
	 *
	 * @param	string	要得到的缓存的 id
	 * @param	string	缓存丢失时返回的默认值
	 * @return	mixed
	 * @throws  Cache_Exception
	 */
	public function get($id, $default = NULL)
	{
		$filename = Cache_File::filename($this->_sanitize_id($id));
		$directory = $this->_resolve_directory($filename);
		try
		{
			// 打开文件
			$file = new SplFileInfo($directory . $filename);
			// 如果文件不存在
			if (!$file->isFile())
			{
				// 返回默认值
				return $default;
			}
			else
			{
				// 打开文件并解析数据
				$created = $file->getMTime();
				$data = $file->openFile();
				$lifetime = $data->fgets();
				if ($data->eof())
				{
					throw new Cache_Exception(__METHOD__ . ' 损坏的缓存文件！');
				}
				$cache = '';
				while ($data->eof() === false)
				{
					$cache .= $data->fgets();
				}
				unset($data);
				// 测试期限
				if ($created + (int) $lifetime < time())
				{
					// 删除文件
					$this->_delete_file($file, false, true);
					return $default;
				}
				else
				{
					return unserialize($cache);
				}
			}
		}
		catch (ErrorException $e)
		{
			// 处理反序列化失败导致的 ErrorException
			if ($e->getCode() === E_NOTICE)
			{
				throw new Cache_Exception(__METHOD__ . ' 反序列化缓存对象失败：' . $e->getMessage());
			}
			// 否则抛出异常
			throw $e;
		}
	}

	/**
	 * 以 id 和生命周期为缓存设值
	 *
	 * 	$data = 'bar';
	 * 	// 在文件组中将 'bar' 置给 'foo'，使用默认期限
	 * 	Cache::instance('file')->set('foo', $data);
	 * 	// 在文件组中将 'bar' 置给 'foo'，30 秒期限
	 * 	Cache::instance('file')->set('foo', $data, 30);
	 *
	 * @param	string	缓存项的 id
	 * @param	string	置给缓存的数据
	 * @param	integer	以“秒”为单位的生命周期
	 * @return	boolean
	 */
	public function set($id, $data, $lifetime = NULL)
	{
		$filename = Cache_File::filename($this->_sanitize_id($id));
		$directory = $this->_resolve_directory($filename);
		// 如果生命周期为 NULL
		if ($lifetime === NULL)
		{
			// 设置默认期限
			$lifetime = Arr::get($this->_config, 'default_expire', Cache::DEFAULT_EXPIRE);
		}
		// 打开目录
		$dir = new SplFileInfo($directory);
		// 如果目录路径不是一个目录
		if (!$dir->isDir())
		{
			// 创建目录
			if (!mkdir($directory, 0777, true))
			{
				throw new Cache_Exception(__METHOD__ . ' 无法创建目录：:directory', array(':directory' => $directory));
			}
			// chmod 用于解决潜在的 umask 问题
			chmod($directory, 0777);
		}
		// 打开文件进行检查
		$resouce = new SplFileInfo($directory . $filename);
		$file = $resouce->openFile('w');
		try
		{
			$data = $lifetime . "\n" . serialize($data);
			$file->fwrite($data, strlen($data));
			return (bool) $file->fflush();
		}
		catch (ErrorException $e)
		{
			// 如果序列化出现错误异常
			if ($e->getCode() === E_NOTICE)
			{
				// 抛出缓存错误
				throw new Cache_Exception(__METHOD__ . ' 序列化缓存数据失败：' . $e->getMessage());
			}
			// 否则抛出错误异常
			throw $e;
		}
	}

	/**
	 * 删除基于 id 的缓存项
	 *
	 * 	// 从文件组删除 'foo' 项
	 * 	Cache::instance('file')->delete('foo');
	 *
	 * @param	string	要移除的缓存 id
	 * @return	boolean
	 */
	public function delete($id)
	{
		$filename = Cache_File::filename($this->_sanitize_id($id));
		$directory = $this->_resolve_directory($filename);
		return $this->_delete_file(new SplFileInfo($directory . $filename), false, true);
	}

	/**
	 * 删除所有缓存项
	 * 在共享内存缓存系统中，要谨慎使用这个方法，因为它会擦除所有客户端系统的每一个文件。
	 *
	 * 	// 删除文件组中的所有缓存项
	 * 	Cache::instance('file')->deleteAll();
	 *
	 * @return	boolean
	 */
	public function deleteAll()
	{
		return $this->_delete_file($this->_cache_dir, true);
	}

	/**
	 * 垃圾回收方法，用于清理过期的缓存项。
	 * @return	void
	 */
	public function garbage_collect()
	{
		$this->_delete_file($this->_cache_dir, true, false, true);
		return;
	}

	/**
	 * 递归删除文件，并在出现任何错误时返回 false
	 *
	 * 	// 删除文件或文件夹，同时保留父目录，并忽略所有错误
	 * 	$this->_delete_file($folder, true, true);
	 *
	 * @param	SplFileInfo	文件
	 * @param	boolean	保留父目录
	 * @param	boolean	忽略错误，以防止异常打断执行
	 * @param	boolean	只是过期的文件
	 * @return	boolean
	 * @throws  Cache_Exception
	 */
	protected function _delete_file(SplFileInfo $file, $retain_parent_directory = false, $ignore_errors = false, $only_expired = false)
	{
		try
		{
			// 如果是文件
			if ($file->isFile())
			{
				try
				{
					// 处理忽略的文件
					if (in_array($file->getFilename(), $this->config('ignore_on_delete')))
					{
						$delete = false;
					}
					// 如果 $only_expired 未设置
					elseif ($only_expired === false)
					{
						// 我们要删除这个文件
						$delete = true;
					}
					else
					{
						// 评估文件期限，用以标记为删除
						$json = $file->openFile('r')->current();
						$data = json_decode($json);
						$delete = $data->expiry < time();
					}
					// 如果设置了删除标志，则删除文件
					if ($delete === true)
						return unlink($file->getRealPath());
					else
						return false;
				}
				catch (ErrorException $e)
				{
					// 捕捉文件删除警告
					if ($e->getCode() === E_WARNING)
					{
						throw new Cache_Exception(__METHOD__ . ' 删除文件失败：:file', array(':file' => $file->getRealPath()));
					}
				}
			}
			// 如果是目录
			elseif ($file->isDir())
			{
				$files = new DirectoryIterator($file->getPathname());
				while ($files->valid())
				{
					// 提取文件名称
					$name = $files->getFilename();
					// 如果名称不是“点”
					if ($name != '.' && $name != '..')
					{
						// 创建新的文件源
						$fp = new SplFileInfo($files->getRealPath());
						// 删除该文件
						$this->_delete_file($fp);
					}
					// 移动文件指针
					$files->next();
				}
				// 如果设置为保留父目录，返回
				if ($retain_parent_directory)
				{
					return true;
				}
				try
				{
					// 移除文件迭代（修复 Windows PHP 打开 DirectoryIterator 的权限问题）
					unset($files);
					// 尝试移除父目录
					return rmdir($file->getRealPath());
				}
				catch (ErrorException $e)
				{
					// 捕捉目录删除警告
					if ($e->getCode() === E_WARNING)
					{
						throw new Cache_Exception(__METHOD__ . ' 删除目录失败：:directory', array(':directory' => $file->getRealPath()));
					}
					throw $e;
				}
			}
			else
			{
				// 文件已经被删除
				return false;
			}
		}
		catch (Exception $e)
		{
			// 如果打开了“忽略错误”
			if ($ignore_errors === true)
				return false;
			// 抛出异常
			throw $e;
		}
	}

	/**
	 * 根据文件名解析缓存目录的实际路径
	 *
	 * 	// 取得缓存目录的真实路径
	 * 	$realpath = $this->_resolve_directory($filename);
	 *
	 * @param	string	要解析的文件名
	 * @return	string
	 */
	protected function _resolve_directory($filename)
	{
		return $this->_cache_dir->getRealPath() . DIRECTORY_SEPARATOR . $filename[0] . $filename[1] . DIRECTORY_SEPARATOR;
	}

	/**
	 * 生成缓存目录（如果不存在的话）。简单地包装 mkdir。
	 * @param	string	目录
	 * @param	string	mode
	 * @param	string	递归
	 * @param	string	context
	 * @return	SplFileInfo
	 * @throws	Cache_Exception
	 */
	protected function _make_directory($directory, $mode = 0777, $recursive = false, $context = NULL)
	{
		if (!mkdir($directory, $mode, $recursive, $context))
		{
			throw new Cache_Exception('创建默认缓存目录失败：:directory', array(':directory' => $directory));
		}
		chmod($directory, $mode);
		return new SplFileInfo($directory);
		;
	}

}
