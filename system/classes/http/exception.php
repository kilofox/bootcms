<?php

defined('SYSPATH') || exit('Access Denied.');

class HTTP_Exception extends BootPHP_Exception {

	/**
	 * @var intHTTP状态码
	 */
	protected $_code = 0;

	/**
	 * 创建一个新的翻译了的异常。
	 *
	 * 	throw new BootPHP_Exception('出现了可怕的错误，:user',
	 * 		array(':user' => $user));
	 *
	 * @param string 状态消息，自定义显示的错误内容
	 * @param array 翻译变量
	 * @param	integer	HTTP状态码
	 * @return void
	 */
	public function __construct($message = NULL, array $variables = NULL, $code = 0)
	{
		if ($code == 0)
		{
			$code = $this->_code;
		}
		if (!isset(Response::$messages[$code]))
			throw new BootPHP_Exception('无法识别的HTTP状态码：:code 。只有有效的HTTP状态码是可以接受的，请参见 RFC 2616。', array(':code' => $code));
		parent::__construct($message, $variables, $code);
	}

}
