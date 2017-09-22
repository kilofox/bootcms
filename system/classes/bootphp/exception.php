<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * BootPHP 异常类。
 * 可以使用 [I18n] 类翻译异常。
 *
 * @package BootPHP
 * @category 异常
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class BootPHP_Exception extends Exception {

	/**
	 * @var array PHP 错误代码 => 人类可读的名称
	 */
	public static $php_errors = array(
		E_ERROR => 'Fatal Error',
		E_USER_ERROR => 'User Error',
		E_PARSE => 'Parse Error',
		E_WARNING => 'Warning',
		E_USER_WARNING => 'User Warning',
		E_STRICT => 'Strict',
		E_NOTICE => 'Notice',
		E_RECOVERABLE_ERROR => 'Recoverable Error',
	);

	/**
	 * @var string 错误渲染视图
	 */
	public static $error_view = 'bootphp/error';

	/**
	 * @var string 错误视图内容类型
	 */
	public static $error_view_content_type = 'text/html';

	/**
	 * 创建一个新的翻译了的异常。
	 *
	 *     throw new BootPHP_Exception('Something went terrible wrong, :user',
	 *     array(':user' => $user));
	 *
	 * @param string 错误消息
	 * @param array 翻译变量
	 * @param integer|string 异常代码
	 * @return void
	 */
	public function __construct($message, array $variables = NULL, $code = 0)
	{
		if (defined('E_DEPRECATED'))
		{
			// E_DEPRECATED 仅在 PHP 5.3.0 以上版本中存在
			self::$php_errors[E_DEPRECATED] = 'Deprecated';
		}
		// 设置消息
		$message = __($message, $variables);
		// Pass the message and integer code to the parent
		parent::__construct($message, (int) $code);
		// Save the unmodified code
		$this->code = $code;
	}

	/**
	 * Magic object-to-string method.
	 *
	 * @uses self::text
	 * @return string
	 */
	public function __toString()
	{
		return self::text($this);
	}

	/**
	 * Inline exception handler, displays the error message, source of the exception, and the stack trace of the error.
	 *
	 * @uses self::text
	 * @param object exception object
	 * @return boolean
	 */
	public static function handler(Exception $e)
	{
		try
		{
			// Get the exception information
			$type = get_class($e);
			$code = $e->getCode();
			$message = $e->getMessage();
			$file = $e->getFile();
			$line = $e->getLine();
			// Get the exception backtrace
			$trace = $e->getTrace();
			if ($e instanceof ErrorException)
			{
				if (isset(self::$php_errors[$code]))
				{
					// Use the human-readable error name
					$code = self::$php_errors[$code];
				}
				if (version_compare(PHP_VERSION, '5.3', '<'))
				{
					// Workaround for a bug in ErrorException::getTrace() that exists in
					// all PHP 5.2 versions. @see http://bugs.php.net/bug.php?id=45895
					for ($i = count($trace) - 1; $i > 0; --$i)
					{
						if (isset($trace[$i - 1]['args']))
						{
							// Re-position the args
							$trace[$i]['args'] = $trace[$i - 1]['args'];
							// Remove the args
							unset($trace[$i - 1]['args']);
						}
					}
				}
			}
			// Create a text version of the exception
			$error = self::text($e);
			if (is_object(BootPHP::$log))
			{
				// Add this exception to the log
				BootPHP::$log->add(Log::ERROR, $error);
				$strace = self::text($e) . "\n--\n" . $e->getTraceAsString();
				BootPHP::$log->add(Log::STRACE, $strace);
				// Make sure the logs are written
				BootPHP::$log->write();
			}
			if (!headers_sent())
			{
				// Make sure the proper http header is sent
				$http_header_status = ($e instanceof HTTP_Exception) ? $code : 500;
				header('Content-Type: ' . self::$error_view_content_type . '; charset=' . BootPHP::$charset, true, $http_header_status);
			}
			if (Request::$current !== NULL && Request::current()->is_ajax() === true)
			{
				// Just display the text of the exception
				echo "\n{$error}\n";
				exit(1);
			}
			// Start an output buffer
			ob_start();
			// Include the exception HTML
			if ($view_file = BootPHP::find_file('views', self::$error_view))
			{
				include $view_file;
			}
			else
			{
				throw new BootPHP_Exception('Error view file does not exist: views/:file', array(
				':file' => self::$error_view,
				));
			}
			// Display the contents of the output buffer
			echo ob_get_clean();
			exit(1);
		}
		catch (Exception $e)
		{
			// Clean the output buffer if one exists
			ob_get_level() and ob_clean();
			// Display the exception text
			echo self::text($e), "\n";
			// Exit with an error status
			exit(1);
		}
	}

	/**
	 * Get a single line of text representing the exception:
	 *
	 * Error [ Code ]: Message ~ File [ Line ]
	 *
	 * @param object Exception
	 * @return string
	 */
	public static function text(Exception $e)
	{
		return sprintf('%s [ %s ]: %s ~ %s [ %d ]', get_class($e), $e->getCode(), strip_tags($e->getMessage()), Debug::path($e->getFile()), $e->getLine());
	}

}
