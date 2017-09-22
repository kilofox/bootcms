<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 图片处理支持。允许对图像调整大小、裁剪等。
 *
 * @package		BootPHP/图像
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
abstract class Image {

	// 调整大小的限制
	const NONE = 0x01;
	const WIDTH = 0x02;
	const HEIGHT = 0x03;
	const AUTO = 0x04;
	const INVERSE = 0x05;
	// 翻转方向
	const HORIZONTAL = 0x11;
	const VERTICAL = 0x12;

	/**
	 * @var	string	默认驱动：GD、ImageMagick 等
	 */
	public static $default_driver = 'GD';
	// 驱动检查的状态
	protected static $_checked = false;

	/**
	 * 加载图像，并准备对其操作。
	 *
	 * 	$image = Image::factory('upload/test.jpg');
	 *
	 * @param	string	图像文件路径
	 * @param	string	驱动类型：GD、ImageMagick 等
	 * @return	Image
	 * @uses	Image::$default_driver
	 */
	public static function factory($file, $driver = NULL)
	{
		if ($driver === NULL)
		{
			// 使用默认驱动
			$driver = Image::$default_driver;
		}
		// 设置类名
		$class = 'Image_' . $driver;
		return new $class($file);
	}

	/**
	 * @var	string	图像文件路径
	 */
	public $file;

	/**
	 * @var	integer	图像宽度
	 */
	public $width;

	/**
	 * @var	integer	图像高度
	 */
	public $height;

	/**
	 * @var	integer	IMAGETYPE_* 常量之一
	 */
	public $type;

	/**
	 * @var	string	图像的 MIME 类型
	 */
	public $mime;

	/**
	 * 加载图像信息。如果图像不存在或不是图像，将抛出异常。
	 * @param	string	图像文件路径
	 * @return	void
	 * @throws	BootPHP_Exception
	 */
	public function __construct($file)
	{
		try
		{
			// 获取文件的实际路径
			$file = realpath($file);
			// 获取图像信息
			$info = getimagesize($file);
		}
		catch (Exception $e)
		{
			// 读取图像时忽略所有错误
		}
		if (empty($file) || empty($info))
		{
			throw new BootPHP_Exception('Not an image or invalid image: :file', array(':file' => Debug::path($file)));
		}
		// 存储图像信息
		$this->file = $file;
		$this->width = $info[0];
		$this->height = $info[1];
		$this->type = $info[2];
		$this->mime = image_type_to_mime_type($this->type);
	}

	/**
	 * 渲染当前图像。
	 * [!!] The output of this function is binary and must be rendered with the
	 * appropriate Content-Type header or it will not be displayed correctly!
	 *
	 * @return	string
	 */
	public function __toString()
	{
		try
		{
			// 渲染当前图像
			return $this->render();
		}
		catch (Exception $e)
		{
			if (is_object(BootPHP::$log))
			{
				// Get the text of the exception
				$error = BootPHP_Exception::text($e);
				// Add this exception to the log
				BootPHP::$log->add(Log::ERROR, $error);
			}
			// Showing any kind of error will be "inside" image data
			return '';
		}
	}

	/**
	 * 调整图像尺寸到指定大小。按比例调整大小，宽度或高度可以省略。
	 *
	 * 	// 将长边调整为 200 像素，保持宽高比（大图调小后较小）
	 * 	$image->resize(200, 200);
	 *
	 * 	// 将短边调整为 200 像素，保持宽高比（大图调小后较大）
	 * 	$image->resize(200, 200, Image::INVERSE);
	 *
	 * 	// 调整为 500 像素的宽度，保持宽高比
	 * 	$image->resize(500, NULL);
	 *
	 * 	// 调整为 500 像素的高度，保持宽高比
	 * 	$image->resize(NULL, 500);
	 *
	 * 	// 调整为 200x500 像素，忽略宽高比
	 * 	$image->resize(200, 500, Image::NONE);
	 *
	 * @param	integer	新的宽度
	 * @param	integer	新的高度
	 * @param	integer	主尺寸
	 * @return	$this
	 * @uses	Image::_do_resize
	 */
	public function resize($width = NULL, $height = NULL, $master = NULL)
	{
		if ($master === NULL)
		{
			// 自动选择主尺寸
			$master = Image::AUTO;
		}
		// Image::WIDTH and Image::HEIGHT deprecated. You can use it in old projects,
		// but in new you must pass empty value for non-master dimension
		elseif ($master == Image::WIDTH && !empty($width))
		{
			$master = Image::AUTO;
			// Set empty height for backward compatibility
			$height = NULL;
		}
		elseif ($master == Image::HEIGHT && !empty($height))
		{
			$master = Image::AUTO;
			// Set empty width for backward compatibility
			$width = NULL;
		}
		if (empty($width))
		{
			if ($master === Image::NONE)
			{
				// Use the current width
				$width = $this->width;
			}
			else
			{
				// If width not set, master will be height
				$master = Image::HEIGHT;
			}
		}
		if (empty($height))
		{
			if ($master === Image::NONE)
			{
				// Use the current height
				$height = $this->height;
			}
			else
			{
				// If height not set, master will be width
				$master = Image::WIDTH;
			}
		}
		switch ($master)
		{
			case Image::AUTO:
				// 用最大缩小比率选择方向
				$master = ($this->width / $width) > ($this->height / $height) ? Image::WIDTH : Image::HEIGHT;
				break;
			case Image::INVERSE:
				// 用最小缩小比率选择方向
				$master = ($this->width / $width) > ($this->height / $height) ? Image::HEIGHT : Image::WIDTH;
				break;
		}
		switch ($master)
		{
			case Image::WIDTH:
				// Recalculate the height based on the width proportions
				$height = $this->height * $width / $this->width;
				break;
			case Image::HEIGHT:
				// Recalculate the width based on the height proportions
				$width = $this->width * $height / $this->height;
				break;
		}
		// Convert the width and height to integers, minimum value is 1px
		$width = max(round($width), 1);
		$height = max(round($height), 1);
		$this->_do_resize($width, $height);
		return $this;
	}

	/**
	 * 裁剪指定大小的图像。宽度或高度可以省略，这时使用当前宽度或高度。
	 *
	 * 如果没有指定偏移量，将使用轴的中心。
	 * 如果指定了偏移量为 true，将使用轴的底部。
	 *
	 * 	// 从中间裁剪 200x200 像素的图像
	 * 	$image->crop(200, 200);
	 *
	 * @param	integer	新的宽度
	 * @param	integer	新的高度
	 * @param	mixed	从左偏移
	 * @param	mixed	从上偏移
	 * @return	$this
	 * @uses	Image::_do_crop
	 */
	public function crop($width, $height, $offset_x = NULL, $offset_y = NULL)
	{
		if ($width > $this->width)
		{
			// 使用当前宽度
			$width = $this->width;
		}
		if ($height > $this->height)
		{
			// 使用当前高度
			$height = $this->height;
		}
		if ($offset_x === NULL)
		{
			// 居中 X 偏移
			$offset_x = round(($this->width - $width) / 2);
		}
		elseif ($offset_x === true)
		{
			// 置底 X 偏移
			$offset_x = $this->width - $width;
		}
		elseif ($offset_x < 0)
		{
			// 从右侧设置 X 偏移
			$offset_x = $this->width - $width + $offset_x;
		}
		if ($offset_y === NULL)
		{
			// 居中 Y 偏移
			$offset_y = round(($this->height - $height) / 2);
		}
		elseif ($offset_y === true)
		{
			// 置底 Y 偏移
			$offset_y = $this->height - $height;
		}
		elseif ($offset_y < 0)
		{
			// 从底部设置 Y 偏移
			$offset_y = $this->height - $height + $offset_y;
		}
		// 判断最大可用宽度与高度
		$max_width = $this->width - $offset_x;
		$max_height = $this->height - $offset_y;
		if ($width > $max_width)
		{
			// 使用最大可用宽度
			$width = $max_width;
		}
		if ($height > $max_height)
		{
			// 使用最大可用高度
			$height = $max_height;
		}
		$this->_do_crop($width, $height, $offset_x, $offset_y);
		return $this;
	}

	/**
	 * Rotate the image by a given amount.
	 *
	 * 	// Rotate 45 degrees clockwise
	 * 	$image->rotate(45);
	 *
	 * 	// Rotate 90% counter-clockwise
	 * 	$image->rotate(-90);
	 *
	 * @param	integer	 degrees to rotate: -360-360
	 * @return	$this
	 * @uses    Image::_do_rotate
	 */
	public function rotate($degrees)
	{
		// Make the degrees an integer
		$degrees = (int) $degrees;
		if ($degrees > 180)
		{
			do
			{
				// Keep subtracting full circles until the degrees have normalized
				$degrees -= 360;
			}
			while ($degrees > 180);
		}
		if ($degrees < -180)
		{
			do
			{
				// Keep adding full circles until the degrees have normalized
				$degrees += 360;
			}
			while ($degrees < -180);
		}
		$this->_do_rotate($degrees);
		return $this;
	}

	/**
	 * Flip the image along the horizontal or vertical axis.
	 *
	 * 	// Flip the image from top to bottom
	 * 	$image->flip(Image::HORIZONTAL);
	 *
	 * 	// Flip the image from left to right
	 * 	$image->flip(Image::VERTICAL);
	 *
	 * @param	integer	方向：Image::HORIZONTAL、Image::VERTICAL
	 * @return	$this
	 * @uses	Image::_do_flip
	 */
	public function flip($direction)
	{
		if ($direction !== Image::HORIZONTAL)
		{
			// 垂直翻转
			$direction = Image::VERTICAL;
		}
		$this->_do_flip($direction);
		return $this;
	}

	/**
	 * 用指定的数量锐化图像。
	 *
	 * 	// 锐化图像 20%
	 * 	$image->sharpen(20);
	 *
	 * @param	integer	锐化数量：1-100
	 * @return	$this
	 * @uses	Image::_do_sharpen
	 */
	public function sharpen($amount)
	{
		// The amount must be in the range of 1 to 100
		$amount = min(max($amount, 1), 100);
		$this->_do_sharpen($amount);
		return $this;
	}

	/**
	 * 为图像添加倒影。倒影的最不透明的部分等于不透明度设置，然后淡出为完全透明。
	 * Alpha 透明度将被保留。
	 *
	 * 	// 创建一个 50 像素的倒影，不透明度从 0 到 100% 渐变
	 * 	$image->reflection(50);
	 *
	 * 	// 创建一个 50 像素的倒影，不透明度从 100% 到 0 渐变
	 * 	$image->reflection(50, 100, true);
	 *
	 * 	// 创建一个 50 像素的倒影，不透明度从 0 到 60% 渐变
	 * 	$image->reflection(50, 60, true);
	 *
	 * 注意：默认情况下，倒影会从顶部透明到底部不透明。
	 *
	 * @param	integer	倒影高度
	 * @param	integer	倒影不透明度：0-100
	 * @param	boolean	true 为淡入，false 为淡出
	 * @return	$this
	 * @uses	Image::_do_reflection
	 */
	public function reflection($height = NULL, $opacity = 100, $fade_in = false)
	{
		if ($height === NULL || $height > $this->height)
		{
			// Use the current height
			$height = $this->height;
		}
		// The opacity must be in the range of 0 to 100
		$opacity = min(max($opacity, 0), 100);
		$this->_do_reflection($height, $opacity, $fade_in);
		return $this;
	}

	/**
	 * 为图像添加指定不透明度的水印。Alpha 透明度将被保留。
	 *
	 * 如果没有指定偏移量，则使用轴的中心。
	 * 如果指定偏移量为 true，则使用轴的底部。
	 *
	 * 	// 将水印添加到图像的右下角
	 * 	$mark = Image::factory('upload/watermark.png');
	 * 	$image->watermark($mark, true, true);
	 *
	 * @param	object	水印图像实例
	 * @param	integer	左偏移量
	 * @param	integer	上偏移量
	 * @param	integer	水印的不透明度：1-100
	 * @return	$this
	 * @uses	Image::_do_watermark
	 */
	public function watermark(Image $watermark, $offset_x = NULL, $offset_y = NULL, $opacity = 100)
	{
		if ($offset_x === NULL)
		{
			// 居中 X 偏移
			$offset_x = round(($this->width - $watermark->width) / 2);
		}
		elseif ($offset_x === true)
		{
			// 置底 X 偏移
			$offset_x = $this->width - $watermark->width;
		}
		elseif ($offset_x < 0)
		{
			// 从右侧设置 X 偏移
			$offset_x = $this->width - $watermark->width + $offset_x;
		}
		if ($offset_y === NULL)
		{
			// 居中 Y 偏移
			$offset_y = round(($this->height - $watermark->height) / 2);
		}
		elseif ($offset_y === true)
		{
			// 置底 Y 偏移
			$offset_y = $this->height - $watermark->height;
		}
		elseif ($offset_y < 0)
		{
			// 从底部设置 Y 偏移
			$offset_y = $this->height - $watermark->height + $offset_y;
		}
		// 不透明度必须在 1 到 100 的范围内
		$opacity = min(max($opacity, 1), 100);
		$this->_do_watermark($watermark, $offset_x, $offset_y, $opacity);
		return $this;
	}

	/**
	 * 设置图像的背景色。这只适用于带 Alpha 透明度的图像。
	 *
	 * 	// 使图像背景为黑色
	 * 	$image->background('#000');
	 *
	 * 	// 使图像背景为带有 50% 不透明度的黑色
	 * 	$image->background('#000', 50);
	 *
	 * @param	string	十六进制颜色值
	 * @param	integer	背景不透明度：0-100
	 * @return	$this
	 * @uses	Image::_do_background
	 */
	public function background($color, $opacity = 100)
	{
		if ($color[0] === '#')
		{
			$color = substr($color, 1);
		}
		if (strlen($color) === 3)
		{
			// 将十六进制的简写形式转换为普通写法
			$color = preg_replace('/./', '$0$0', $color);
		}
		// 将十六进制转换成 RGB 值
		list($r, $g, $b) = array_map('hexdec', str_split($color, 2));
		// 不透明度必须在 0 到 100 的范围内
		$opacity = min(max($opacity, 0), 100);
		$this->_do_background($r, $g, $b, $opacity);
		return $this;
	}

	/**
	 * 保存图像。如果省略文件名，则原始图像将被覆盖。
	 *
	 * 	// 保存图像为 PNG
	 * 	$image->save('uploads/fox.png');
	 *
	 * 	// 覆盖原始图像
	 * 	$image->save();
	 *
	 * 注意：如果文件存在，但是不可写，将抛出异常。
	 * 注意：如果文件不存在，目录不可写，将派出异常。
	 *
	 * @param	string	new image path
	 * @param	integer	quality of image: 1-100
	 * @return	boolean
	 * @uses	Image::_save
	 * @throws  BootPHP_Exception
	 */
	public function save($file = NULL, $quality = 100)
	{
		if ($file === NULL)
		{
			// 覆盖文件
			$file = $this->file;
		}
		if (is_file($file))
		{
			if (!is_writable($file))
			{
				throw new BootPHP_Exception('File must be writable: :file', array(':file' => Debug::path($file)));
			}
		}
		else
		{
			// 获得文件目录
			$directory = realpath(pathinfo($file, PATHINFO_DIRNAME));
			if (!is_dir($directory) || !is_writable($directory))
			{
				throw new BootPHP_Exception('Directory must be writable: :directory', array(':directory' => Debug::path($directory)));
			}
		}
		// 质量必须在 1 到 100 的范围内
		$quality = min(max($quality, 1), 100);
		return $this->_do_save($file, $quality);
	}

	/**
	 * 渲染图像并返回二进制数据。
	 *
	 * 	// 以 50% 的质量渲染图像
	 * 	$data = $image->render(NULL, 50);
	 *
	 * 	// 渲染图像为 PNG
	 * 	$data = $image->render('png');
	 *
	 * @param	string	返回的图像格式：png、jpg、gif 等
	 * @param	integer	图像质量：1-100
	 * @return	string
	 * @uses	Image::_do_render
	 */
	public function render($type = NULL, $quality = 100)
	{
		if ($type === NULL)
		{
			// 使用当前图像类型
			$type = image_type_to_extension($this->type, false);
		}
		return $this->_do_render($type, $quality);
	}

	/**
	 * 执行调整大小。
	 * @param	integer	新宽度
	 * @param	integer	新高度
	 * @return	void
	 */
	abstract protected function _do_resize($width, $height);

	/**
	 * 执行剪切。
	 * @param	integer	新宽度
	 * @param	integer	新高度
	 * @param	integer	左偏移量
	 * @param	integer	上偏移量
	 * @return	void
	 */
	abstract protected function _do_crop($width, $height, $offset_x, $offset_y);

	/**
	 * 执行旋转。
	 * @param	integer	旋转角度
	 * @return	void
	 */
	abstract protected function _do_rotate($degrees);

	/**
	 * 执行翻转。
	 * @param	integer	翻转方向
	 * @return	void
	 */
	abstract protected function _do_flip($direction);

	/**
	 * 执行锐化。
	 * @param	integer	锐化数量
	 * @return	void
	 */
	abstract protected function _do_sharpen($amount);

	/**
	 * 执行倒影。
	 * @param	integer	倒影高度
	 * @param	integer	倒影不透明度
	 * @param	boolean	true 为淡出，false 淡入
	 * @return	void
	 */
	abstract protected function _do_reflection($height, $opacity, $fade_in);

	/**
	 * 执行水印。
	 * @param	object	要水印的图像
	 * @param	integer	左偏移量
	 * @param	integer	上偏移量
	 * @param	integer	水印的不透明度
	 * @return	void
	 */
	abstract protected function _do_watermark(Image $image, $offset_x, $offset_y, $opacity);

	/**
	 * 执行背景色。
	 * @param	integer	红
	 * @param	integer	绿
	 * @param	integer	蓝
	 * @param	integer	不透明度
	 * @return void
	 */
	abstract protected function _do_background($r, $g, $b, $opacity);

	/**
	 * 执行保存。
	 * @param	string	新图像文件名
	 * @param	integer	质量
	 * @return	boolean
	 */
	abstract protected function _do_save($file, $quality);

	/**
	 * 执行渲染。
	 * @param	string	图像格式：png、jpg、gif 等
	 * @param	integer	质量
	 * @return	string
	 */
	abstract protected function _do_render($type, $quality);
}
