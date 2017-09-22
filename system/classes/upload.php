<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 与上传的文件和 [Validation] 一起工作的上传辅助类。
 *
 * 	$array = Validation::factory($_FILES);
 *
 * [!!] 记得要用 enctype="multipart/form-data" 定义您的表单，否则文件上传将不起作用！
 *
 * 可以设置以下配置属性：
 *
 * - [Upload::$remove_spaces]
 * - [Upload::$default_directory]
 *
 * @package BootPHP
 * @category 辅助类
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Upload {

	/**
	 * @var	boolean	去掉上传的文件名中的空格
	 */
	public static $remove_spaces = true;

	/**
	 * @var string 默认上传目录
	 */
	public static $default_directory = 'upload';

	/**
	 * 将上传的文件保存到新的位置。如果不提供文件名，则使用带唯一前缀的原始文件名。
	 *
	 * 这个方法应该在验证 $_FILES 数组之后使用：
	 *
	 * 	if ( $array->check() )
	 * 	{
	 * 		// 上传有效，保存
	 * 		Upload::save($array['file']);
	 * 	}
	 *
	 * @param array 上传的文件数据
	 * @param string 新的文件名
	 * @param string 新的目录
	 * @param integer	chmod 掩码
	 * @return string	成功时，新文件的完整路径
	 * @return false	失败时
	 */
	public static function save(array $file, $filename = NULL, $directory = NULL, $chmod = 0644)
	{
		if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name']))
		{
			// 忽略损坏的上传
			return false;
		}
		if ($filename === NULL)
		{
			// 使用默认文件名，带时间戳前缀
			$filename = uniqid() . $file['name'];
		}
		if (self::$remove_spaces === true)
		{
			// 从文件名中移除空格
			$filename = preg_replace('/\s+/u', '_', $filename);
		}
		if ($directory === NULL)
		{
			// 使用预先配置的上传目录
			$directory = self::$default_directory;
		}
		if (!is_dir($directory) || !is_writable(realpath($directory)))
		{
			throw new BootPHP_Exception('Directory :dir must be writable', array(':dir' => Debug::path($directory)));
		}
		// 使文件名变成一个完整的路径
		$filename = str_replace("\\", "\/", realpath($directory) . DIRECTORY_SEPARATOR . $filename);
		if (move_uploaded_file($file['tmp_name'], $filename))
		{
			if ($chmod !== false)
			{
				// 设置权限
				chmod($filename, $chmod);
			}
			// 返回新文件路径
			return $filename;
		}
		return false;
	}

	/**
	 * 测试要上传的数据是否有效，尽管文件还没有上传。
	 * 如果您确实需要一个上传的文件，请在此规则之前添加 [Upload::not_empty] 规则。
	 *
	 * 	$array->rule('file', 'Upload::valid')
	 *
	 * @param array $_FILES 项
	 * @return bool
	 */
	public static function valid($file)
	{
		return isset($file['error']) && isset($file['name']) && isset($file['type']) && isset($file['tmp_name']) && isset($file['size']);
	}

	/**
	 * 测试上传是否成功。
	 *
	 * 	$array->rule('file', 'Upload::not_empty');
	 *
	 * @param array $_FILES 项
	 * @return bool
	 */
	public static function not_empty(array $file)
	{
		return isset($file['error']) && isset($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name']);
	}

	/**
	 * 测试上传的文件是否为允许的文件类型（通过扩展名）。
	 *
	 * 	$array->rule('file', 'Upload::type', array(':value', array('jpg', 'png', 'gif')));
	 *
	 * @param array $_FILES 项
	 * @param array 允许的文件扩展
	 * @return bool
	 */
	public static function type(array $file, array $allowed)
	{
		if ($file['error'] !== UPLOAD_ERR_OK)
			return true;
		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		return in_array($ext, $allowed);
	}

	/**
	 * 测试上传的文件是否为允许的文件大小。
	 *
	 *     $array->rule('file', 'Upload::size', array(':value', '1M'))
	 *     $array->rule('file', 'Upload::size', array(':value', '2.5KiB'))
	 *
	 * @param array $_FILES 项
	 * @param string 允许的最大文件大小
	 * @return bool
	 */
	public static function size(array $file, $size)
	{
		if ($file['error'] === UPLOAD_ERR_INI_SIZE)
		{
			// 上传的文件比 PHP 允许的大（upload_max_filesize）
			return false;
		}
		if ($file['error'] !== UPLOAD_ERR_OK)
		{
			// 上传失败，无法检查大小
			return true;
		}
		// 将提供的大小转换成字节，用于比较
		$size = Num::bytes($size);
		// 测试文件大小是否小于等于最大值
		return ($file['size'] <= $size);
	}

	/**
	 * 测试上传的文件是否为图像，以及大小是否正确（可选）。
	 *
	 * 	// 文件 'image' 必须是一个图像
	 * 	$array->rule('image', 'Upload::image')
	 *
	 * 	// 文件 'photo' 最大尺寸为 640x480 像素
	 * 	$array->rule('photo', 'Upload::image', array(640, 480));
	 *
	 * 	// 文件 'image' 必须精确为 100x100 像素
	 * 	$array->rule('image', 'Upload::image', array(100, 100, true));
	 *
	 * @param array $_FILES 项
	 * @param integer	图像的最大宽度
	 * @param integer	图像的最大高度
	 * @param boolean	精确匹配宽高吗？
	 * @return boolean
	 */
	public static function image(array $file, $max_width = NULL, $max_height = NULL, $exact = false)
	{
		if (self::not_empty($file))
		{
			try
			{
				// 从上传的图像获得宽高
				list($width, $height) = getimagesize($file['tmp_name']);
			}
			catch (ErrorException $e)
			{
				// 忽略读取错误
			}
			if (empty($width) || empty($height))
			{
				// 不能得到图像尺寸，无法验证
				return false;
			}
			if (!$max_width)
			{
				// 不限宽，就用图像的宽度
				$max_width = $width;
			}
			if (!$max_height)
			{
				// 不限高，就用图像的高度
				$max_height = $height;
			}
			if ($exact)
			{
				// 检查尺寸是否完全匹配
				return $width === $max_width && $height === $max_height;
			}
			else
			{
				// 检查尺寸是否在最大范围内
				return $width <= $max_width && $height <= $max_height;
			}
		}
		return false;
	}

}
