<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 邮件发送。
 *
 * @package		BootPHP/邮件
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 *
 * ### 用法举例：
 * $mail = new Mail(array(
 * 			'debug'			=> 1,
 * 			'method'		=> 'mail',
 * 			'html'			=> 1,
 * 			'debug_path'	=> '/tmp/_mail',
 * 			'charset'		=> 'utf-8',
 * 		)
 * );
 * $mail->setFrom('support@kilofox.net', "这是\r\n一封邮件");
 * $mail->setTo('me@mydomain.com');
 * $mail->addBCC('myfriend@mydomain.com');
 * $mail->addBCC('myotherfriend@mydomain.com');
 * $mail->setSubject('这是一个测试！');
 * $mail->setBody('<span style="font-weight:bold;">我们拥有 HTML 能力！</span><br /><br /><span style="font-style:italic;">但这只是一个测试……</span>');
 * $mail->send();
 * </code>
 */
class Mail {

	/**
	 * 发件人邮件地址
	 *
	 * @var	string
	 */
	private $from = '';

	/**
	 * 发件人邮件地址（用于显示）
	 *
	 * @var	string
	 */
	private $from_display = '';

	/**
	 * 收件人邮件地址
	 *
	 * @var	string
	 */
	private $to = '';

	/**
	 * 邮件标题
	 *
	 * @var	string
	 */
	private $subject = '';

	/**
	 * 邮件内容
	 *
	 * @var	string
	 */
	private $message = '';

	/**
	 * PHP mail() 额外参数
	 *
	 * @var	string
	 */
	private $extra_opts = '';

	/**
	 * 普通文本内容
	 *
	 * @var	string
	 */
	private $pt_message = '';

	/**
	 * 附件：部分
	 *
	 * @var	array
	 */
	private $parts = array();

	/**
	 * BCC 邮件地址
	 *
	 * @var	array
	 */
	private $bcc = array();

	/**
	 * 邮件头
	 *
	 * @var	array
	 */
	private $mail_headers = array();

	/**
	 * 头部 EOL
	 * ### RFC 规定为 \r\n
	 * ### 但是大部分服务器只支持 \n
	 *
	 * @var	string
	 */
	const header_eol = "\n";

	/**
	 * 附件：多部分
	 *
	 * @var	string
	 */
	private $multipart = '';

	/**
	 * 附件：边界
	 *
	 * @var	string
	 */
	private $boundry = "----=_NextPart_000_0022_01C1BD6C.D0C0F9F0";

	/**
	 * HTML 邮件标记
	 *
	 * @var	integer
	 */
	private $html_email = 0;

	/**
	 * Email 字符集
	 *
	 * @var	string
	 */
	private $char_set = 'utf-8';

	/**
	 * SMTP 源
	 *
	 * @var	resource
	 */
	private $smtp_fp = NULL;

	/**
	 * SMTP 内容
	 *
	 * @var	string
	 */
	public $smtp_msg = '';

	/**
	 * SMTP 端口
	 *
	 * @var	integer
	 */
	private $smtp_port = 25;

	/**
	 * SMTP 主机
	 *
	 * @var	string
	 */
	private $smtp_host = 'localhost';

	/**
	 * SMTP 用户名
	 *
	 * @var	string
	 */
	private $smtp_user = '';

	/**
	 * SMTP 密码
	 *
	 * @var	string
	 */
	private $smtp_pass = '';

	/**
	 * SMTP: HELO 或 EHLO
	 *
	 * @var	string
	 */
	public $smtp_helo = 'HELO';

	/**
	 * SMTP: 返回代码
	 *
	 * @var	string
	 */
	public $smtp_code = '';

	/**
	 * SMTP: 将邮件地址包在括号内的标记
	 *
	 * @var 	boolean
	 */
	private $wrap_brackets = false;

	/**
	 * 默认邮递方式（mail 或 smtp）
	 *
	 * @var	string
	 */
	private $mail_method = 'mail';

	/**
	 * 将邮件转储到 Flat File，用于测试
	 *
	 * @var	integer
	 */
	private $temp_dump = 0;

	/**
	 * 邮件转储路径
	 *
	 * @var	string
	 */
	private $temp_dump_path = '';

	/**
	 * 错误信息
	 *
	 * @var	string
	 */
	public $error_msg = '';

	/**
	 * 错误描述
	 *
	 * @var	string
	 */
	public $error_help = '';

	/**
	 * 错误标志
	 *
	 * @var	boolean
	 */
	public $error = false;

	/**
	 * Mail 实例
	 */
	protected static $_instance;

	/**
	 * 单例模式
	 * @return Mail
	 */
	public static function instance($opts = array())
	{
		if (!isset(self::$_instance))
		{
			self::$_instance = new Mail($opts);
		}
		return self::$_instance;
	}

	/**
	 * 构造方法
	 *
	 * @param	array	要初始化的类的参数
	 * @return	void
	 */
	public function __construct($opts = array())
	{
		$this->mail_method = $opts['method'] && in_array(strtolower($opts['method']), array('smtp', 'mail')) ? strtolower($opts['method']) : 'mail';
		$this->temp_dump = isset($opts['debug']) && $opts['debug'] ? 1 : 0;
		$this->temp_dump_path = isset($opts['debug_path']) && $opts['debug_path'] ? $opts['debug_path'] : '';
		$this->html_email = isset($opts['html']) && $opts['html'] ? 1 : 0;
		$this->char_set = isset($opts['charset']) && $opts['charset'] ? $opts['charset'] : 'utf-8';
		$this->smtp_host = isset($opts['smtp_host']) && $opts['smtp_host'] ? $opts['smtp_host'] : '';
		$this->smtp_port = isset($opts['smtp_port']) && $opts['smtp_port'] ? intval($opts['smtp_port']) : 25;
		$this->smtp_user = isset($opts['smtp_user']) && $opts['smtp_user'] ? $opts['smtp_user'] : '';
		$this->smtp_pass = isset($opts['smtp_pass']) && $opts['smtp_pass'] ? $opts['smtp_pass'] : '';
		$this->smtp_helo = isset($opts['smtp_helo']) && $opts['smtp_helo'] ? $opts['smtp_helo'] : 'HELO';
		$this->wrap_brackets = isset($opts['wrap_brackets']) && $opts['wrap_brackets'] ? true : false;
		$this->extra_opts = isset($opts['extra_opts']) && $opts['extra_opts'] ? $opts['extra_opts'] : '';
		if ($this->mail_method == 'smtp')
		{
			$this->_smtpConnect();
		}
	}

	/**
	 * 析构方法
	 *
	 * @return	void
	 */
	public function __destruct()
	{
		if ($this->mail_method == 'smtp')
		{
			$this->_smtpDisconnect();
		}
	}

	/**
	 * 清除存储的数据，为新邮件做准备。
	 *
	 * ### 用于防止反复关闭/重新打开 SMTP 连接。
	 *
	 * @return	void
	 */
	public function clearEmail()
	{
		$this->from = '';
		$this->from_display = '';
		$this->to = '';
		$this->bcc = array();
		$this->subject = '';
		$this->message = '';
		$this->pt_message = '';
		$this->parts = array();
		$this->mail_headers = array();
		$this->multipart = '';
		$this->smtp_msg = '';
		$this->smtp_code = '';
	}

	/**
	 * 清除存储的错误，为新邮件作准备。
	 *
	 * @return	void
	 */
	public function clearError()
	{
		$this->error_msg = '';
		$this->error_help = '';
		$this->error = false;
	}

	/**
	 * 设置发件人邮件地址
	 *
	 * @param	string	发件人邮件地址
	 * @param	string	[Optional] 要显示的发件人
	 * @return	boolean
	 */
	public function setFrom($email, $display = '')
	{
		$this->from = $this->_cleanEmail($email);
		$this->from_display = $display;
		return true;
	}

	/**
	 * 手动设置头部
	 *
	 * @param	string	头部键
	 * @param	string	头部值
	 * @return	boolean
	 */
	public function setHeader($key, $value)
	{
		$this->mail_headers[$key] = $value;
		return true;
	}

	/**
	 * 设置收件人邮件地址
	 *
	 * @param	string	收件人邮件地址
	 * @return	boolean
	 */
	public function setTo($email)
	{
		$this->to = $this->_cleanEmail($email);
		return true;
	}

	/**
	 * 添加密送
	 *
	 * @param	string	收件人邮件地址
	 * @return	boolean
	 */
	public function addBCC($email)
	{
		$this->bcc[] = $this->_cleanEmail($email);
		return true;
	}

	/**
	 * 设置邮件标题
	 *
	 * @param	string	邮件标题
	 * @return	boolean
	 */
	public function setSubject($subject)
	{
		// 修复编码后的引号等
		$subject = str_replace('&quot;', '"', $subject);
		$subject = str_replace('&#039;', "'", $subject);
		$subject = str_replace('&#39;', "'", $subject);
		$subject = str_replace('&#33;', '!', $subject);
		$subject = str_replace('&#36;', '$', $subject);
		if ($this->mail_method != 'smtp')
		{
			$sheader = $this->_encodeHeaders(array('Subject' => $subject));
			$subject = $sheader['Subject'];
		}
		$this->subject = $subject;
		return true;
	}

	/**
	 * 设置邮件正文
	 *
	 * @param	string	邮件正文
	 * @return	boolean
	 */
	public function setBody($body)
	{
		$this->message = $body;
		return true;
	}

	/**
	 * 清理邮件地址
	 *
	 * @param	string	邮件地址
	 * @return	string	清理后的邮件地址
	 */
	private function _cleanEmail($email)
	{
		$email = str_replace(' ', '', $email);
		$email = str_replace("\t", '', $email);
		$email = str_replace("\r", '', $email);
		$email = str_replace("\n", '', $email);
		$email = str_replace(',,', ',', $email);
		$email = preg_replace("#\#\[\]'\"\(\):;/\$!?\^&\*\{\}#", '', $email);
		return $email;
	}

	/**
	 * Send the mail (All appropriate params must be set by this point)
	 *
	 * @return	boolean		Mail sent successfully
	 */
	public function send()
	{
		// 创建头部
		$this->_buildHeaders();
		// 验证参数都已设置
		if (!$this->to || !$this->from || !$this->subject)
		{
			$this->_fatalError("From, to, or subject empty");
			return false;
		}
		// 调试
		if ($this->temp_dump == 1)
		{
			$debug = $this->subject . "\n------------\n" . $this->rfc_headers . "\n\n" . $this->message;
			if (!is_dir($this->temp_dump_path))
			{
				@mkdir($this->temp_dump_path);
				@chmod($this->temp_dump_path, 0777);
			}
			if (!is_dir($this->temp_dump_path))
			{
				$this->_fatalError('Debugging enabled, but debug path does not exist and cannot be created');
				return false;
			}
			$pathy = $this->temp_dump_path . '/' . date('M-j-Y') . '-' . time() . str_replace('@', '+', $this->to) . uniqid('_') . '.php';
			$fh = @fopen($pathy, 'w');
			@fputs($fh, $debug, strlen($debug));
			@fclose($fh);
		}
		else
		{
			// PHP mail()
			if ($this->mail_method != 'smtp')
			{
				if (!@mail($this->to, $this->subject, $this->message, $this->rfc_headers, $this->extra_opts))
				{
					if (!@mail($this->to, $this->subject, $this->message, $this->rfc_headers))
					{
						$this->_fatalError("Could not send the email", "Failed at 'mail' command");
					}
				}
			}
			// SMTP
			else
			{
				$this->_smtpSendMail();
			}
		}
		$this->clearEmail();
		return $this->error ? false : true;
	}

	/**
	 * 致命错误处理器
	 *
	 * @param	string	错误信息
	 * @param	string	错误帮助或描述
	 * @return	boolean
	 */
	private function _fatalError($msg, $help = '')
	{
		$this->error = true;
		$this->error_msg = $msg;
		$this->error_help = $help;
		return false;
	}

	/**
	 * Build the multipart headers for the email
	 *
	 * @return	string	Multipart headers
	 */
	private function _buildMultipart()
	{
		$multipart = '';
		for ($i = sizeof($this->parts) - 1; $i >= 0; $i--)
		{
			$multipart .= self::header_eol . $this->_encodeAttachment($this->parts[$i]) . "--" . $this->boundry;
		}
		return $multipart . "--\n";
	}

	/**
	 * 编码头部 - RFC2047
	 *
	 * @param	array	头部数组
	 * @return	array	Headers encoded per RFCs
	 * @see		http://www.faqs.org/rfcs/rfc822.html
	 * @see		http://www.faqs.org/rfcs/rfc2045
	 * @see		http://www.faqs.org/rfcs/rfc2047
	 * @see		http://us2.php.net/manual/en/function.mail.php#27997
	 */
	private function _encodeHeaders($headers = array())
	{
		$enc_headers = count($headers) ? $headers : $this->mail_headers;
		foreach ($enc_headers as $header => $value)
		{
			$orig_value = $value;
			// 邮件传送代理似乎不喜欢“From”编码，所以剥离后继续
			if ($header == 'From' || $header == 'Content-Type' || $header == 'Content-Disposition')
			{
				$this->mail_headers[$header] = $orig_value;
				$enc_headers[$header] = $orig_value;
				continue;
			}
			// 对于 php mail，标题不保留于头部
			if ($this->mail_method != 'smtp' && $header == 'Subject')
			{
				unset($this->mail_headers[$header]);
			}
			// 不要打扰编码，除非我们有需要进行编码的字符
			if (!preg_match('/(\w*[\x80-\xFF]+\w*)/', $value))
			{
				if ($header != 'Subject')
				{
					$this->mail_headers[$header] = $orig_value;
				}
				$enc_headers[$header] = $orig_value;
				continue;
			}
			// Base64 编码
			$start = '=?' . $this->char_set . '?B?';
			$end = '?=';
			$spacer = $end . self::header_eol . ' ' . $start;
			$length = 75 - strlen($start) - strlen($end);
			$length = $length - ($length % 4);
			$value = base64_encode($value);
			$value = chunk_split($value, $length, $spacer);
			$spacer = preg_quote($spacer);
			$value = preg_replace('/' . $spacer . "$/", '', $value);
			$value = $start . $value . $end;
			if (!count($headers) && $header != 'Subject')
			{
				$this->mail_headers[$header] = $value;
			}
			else
			{
				$enc_headers[$header] = $value;
			}
		}
		return $enc_headers;
	}

	/**
	 * 构建邮件头（MIME、字符集、发件人、BCC、收件人、标题等）
	 *
	 * @return	void
	 */
	private function _buildHeaders()
	{
		$extra_headers = array();
		$extra_header_rfc = '';
		// 如果要发送HTML信息，那么我们将它和纯文本添加到一起。
		$this->pt_message = $this->message;
		$this->pt_message = str_replace('<br />', "\n", $this->pt_message);
		$this->pt_message = str_replace('<br>', "\n", $this->pt_message);
		$this->pt_message = strip_tags($this->pt_message);
		$this->pt_message = html_entity_decode($this->pt_message, ENT_QUOTES);
		$this->pt_message = str_replace('&#092;', '\\', $this->pt_message);
		$this->pt_message = str_replace('&#036;', '$', $this->pt_message);
		// 开启邮件头
		$this->mail_headers['MIME-Version'] = '1.0';
		$this->mail_headers['Date'] = date('r');
		$this->mail_headers['Return-Path'] = $this->from;
		$this->mail_headers['X-Priority'] = '3';
		$this->mail_headers['X-MSMail-Priority'] = 'Normal';
		$this->mail_headers['X-Mailer'] = 'BootPHP Mailer';
		if ($this->from_display && !preg_match('/(\w*[\x80-\xFF]+\w*)/', $this->from_display))
		{
			$this->mail_headers['From'] = '"' . $this->from_display . '" <' . $this->from . '>';
		}
		else
		{
			$this->mail_headers['From'] = '<' . $this->from . '>';
		}
		if ($this->mail_method != 'smtp')
		{
			if (count($this->bcc) > 0)
			{
				$this->mail_headers['Bcc'] = implode(',', $this->bcc);
			}
		}
		else
		{
			if ($this->to)
			{
				$this->mail_headers['To'] = $this->to;
			}
			$this->mail_headers['Subject'] = $this->subject;
		}
		// 有附件吗？
		if (count($this->parts) > 0)
		{
			if (!$this->html_email)
			{
				$extra_headers[0]['Content-Type'] = "multipart/mixed;\n\tboundary=\"" . $this->boundry . "\"";
				$extra_headers[0]['notencode'] = "\n\nThis is a MIME encoded message.\n\n--" . $this->boundry . "\n";
				$extra_headers[1]['Content-Type'] = "text/plain;\n\tcharset=\"" . $this->char_set . "\"";
				$extra_headers[1]['notencode'] = "\n\n" . $this->message . "\n\n--" . $this->boundry;
			}
			else
			{
				$extra_headers[0]['Content-Type'] = "multipart/mixed;\n\tboundary=\"" . $this->boundry . "\"";
				$extra_headers[0]['notencode'] = "\n\nThis is a MIME encoded message.\n\n--" . $this->boundry . "\n";
				$extra_headers[1]['Content-Type'] = "text/html;\n\tcharset=\"" . $this->char_set . "\"";
				$extra_headers[1]['notencode'] = "\n\n" . $this->message . "\n\n--" . $this->boundry;
			}
			$extra_headers[2]['notencode'] = $this->_buildMultipart();
			reset($extra_headers);
			foreach ($extra_headers as $subset => $the_header)
			{
				foreach ($the_header as $k => $v)
				{
					if ($k == 'notencode')
					{
						$extra_headers_rfc .= $v;
					}
					else
					{
						$v = $this->_encodeHeaders(array('v' => $v));
						$extra_headers_rfc .= $k . ': ' . $v['v'] . self::header_eol;
					}
				}
			}
			$this->message = '';
		}
		else
		{
			if ($this->html_email)
			{
				$extra_headers[0]['Content-Type'] = "multipart/alternative;\n\tboundary=\"" . $this->boundry . "\"";
				$extra_headers[0]['notencode'] = "\n\nThis is a MIME encoded message.\n\n--" . $this->boundry . "\n";
				$extra_headers[1]['Content-Type'] = "text/plain;\n\tcharset=\"" . $this->char_set . "\"";
				$extra_headers[1]['notencode'] = "\n\n" . $this->pt_message . "\n\n--" . $this->boundry . "\n";
				$extra_headers[2]['Content-Type'] = "text/html;\n\tcharset=\"" . $this->char_set . "\"";
				$extra_headers[2]['notencode'] = "\n\n" . $this->message . "\n\n--" . $this->boundry . "--";
				reset($extra_headers);
				foreach ($extra_headers as $subset => $the_header)
				{
					foreach ($the_header as $k => $v)
					{
						if ($k == 'notencode')
						{
							$extra_headers_rfc .= $v;
						}
						else
						{
							$v = $this->_encodeHeaders(array('v' => $v));
							$extra_headers_rfc .= $k . ': ' . $v['v'] . self::header_eol;
						}
					}
				}
				$this->message = '';
			}
			else
			{
				$this->mail_headers['Content-type'] = 'text/plain; charset="' . $this->char_set . '"';
			}
		}
		$this->_encodeHeaders();
		foreach ($this->mail_headers as $k => $v)
		{
			$this->rfc_headers .= $k . ": " . $v . self::header_eol;
		}
		// 有额外附件吗？
		if ($extra_headers_rfc)
		{
			$this->rfc_headers .= $extra_headers_rfc;
		}
	}

	/**
	 * SMTP 连接
	 *
	 * @return	boolean	连接成功
	 */
	private function _smtpConnect()
	{
		$this->smtp_fp = @fsockopen($this->smtp_host, intval($this->smtp_port), $errno, $errstr, 30);
		if (!$this->smtp_fp)
		{
			$this->_smtpError("Could not open a socket to the SMTP server ({$errno}:{$errstr}");
			return false;
		}
		$this->_smtpGetLine();
		$this->smtp_code = substr($this->smtp_msg, 0, 3);
		if ($this->smtp_code == 220)
		{
			// HELO
			$this->_smtpSendCmd("{$this->smtp_helo} " . $this->smtp_host);
			if ($this->smtp_code != 250)
			{
				$this->_smtpError("HELO (using: {$this->smtp_helo})");
				return false;
			}
			if ($this->smtp_user && $this->smtp_pass)
			{
				$this->_smtpSendCmd('AUTH LOGIN');
				if ($this->smtp_code == 334)
				{
					$this->_smtpSendCmd(base64_encode($this->smtp_user));
					if ($this->smtp_code != 334)
					{
						$this->_smtpError('Username not accepted from the server');
						return false;
					}
					$this->_smtpSendCmd(base64_encode($this->smtp_pass));
					if ($this->smtp_code != 235)
					{
						$this->_smtpError('Password not accepted from the server');
						return;
					}
				}
				else
				{
					$this->_smtpError('This server does not support authorisation');
					return;
				}
			}
		}
		else
		{
			$this->_smtpError('Could not connect to the SMTP server');
			return false;
		}
		return true;
	}

	/**
	 * SMTP 断开
	 *
	 * @return	boolean	断开成功了吗？
	 */
	private function _smtpDisconnect()
	{
		$this->_smtpSendCmd('quit');
		if ($this->smtp_code != 221)
		{
			$this->_smtpError("Unable to exit SMTP server with 'quit' command");
			return false;
		}
		return @fclose($this->smtp_fp);
	}

	/**
	 * SMTP: 得到下一行
	 *
	 * @return	void
	 */
	private function _smtpGetLine()
	{
		$this->smtp_msg = '';
		while ($line = @fgets($this->smtp_fp, 515))
		{
			$this->smtp_msg .= $line;
			if (substr($line, 3, 1) == ' ')
				break;
		}
	}

	/**
	 * SMTP 发送命令
	 *
	 * @param	string		SMTP 命令
	 * @return	boolean		命令成功了吗？
	 */
	private function _smtpSendCmd($cmd)
	{
		$this->smtp_msg = '';
		$this->smtp_code = '';
		@fputs($this->smtp_fp, $cmd . "\r\n");
		$this->_smtpGetLine();
		$this->smtp_code = substr($this->smtp_msg, 0, 3);
		return $this->smtp_code == '' ? false : true;
	}

	/**
	 * 编码数据以使其安全传输
	 *
	 * @param	string	原始数据
	 * @return	string	CRLF 编码的数据
	 */
	private function _smtpCrlfEncode($data)
	{
		$data.= "\n";
		$data = str_replace("\n", "\r\n", str_replace("\r", '', $data));
		$data = str_replace("\n.\r\n", "\n. \r\n", $data);
		return $data;
	}

	/**
	 * SMTP 错误处理
	 *
	 * @param	string	SMTP 错误
	 * @return	boolean
	 */
	private function _smtpError($err = '')
	{
		$this->smtp_msg = $err;
		$this->_fatalError($err);
		return false;
	}

	/**
	 * 发送 SMTP 邮件
	 *
	 * @return	void
	 */
	private function _smtpSendMail()
	{
		$data = $this->_smtpCrlfEncode($this->rfc_headers . "\n\n" . $this->message);
		//-----------------------------------------
		// Wrap in brackets
		//-----------------------------------------
		if ($this->wrap_brackets)
		{
			if (!preg_match("/^</", $this->from))
			{
				$this->from = '<' . $this->from . '>';
			}
		}
		// 发件人
		$this->_smtpSendCmd('MAIL FROM:' . $this->from);
		if ($this->smtp_code != 250)
		{
			$this->_smtpError('Mail from command failed');
			return false;
		}
		$toArr = array($this->to);
		if (count($this->bcc) > 0)
		{
			foreach ($this->bcc as $bcc)
			{
				$toArr[] = $bcc;
			}
		}
		// 收件人
		foreach ($toArr as $to_email)
		{
			if ($this->wrap_brackets)
			{
				$this->_smtpSendCmd('RCPT TO:<' . $to_email . '>');
			}
			else
			{
				$this->_smtpSendCmd('RCPT TO:' . $to_email);
			}
			if ($this->smtp_code != 250)
			{
				$this->_smtpError("Incorrect email address: $to_email");
			}
		}
		// 发送邮件
		$this->_smtpSendCmd('DATA');
		if ($this->smtp_code == 354)
		{
			fputs($this->smtp_fp, $data . "\r\n");
		}
		else
		{
			$this->_smtpError('Error writing email body to SMTP server');
			return false;
		}
		// 去吧，滚！
		$this->_smtpSendCmd('.');
		if ($this->smtp_code != 250)
		{
			$this->_smtpError('Email was not sent successfully');
			return false;
		}
	}

	/**
	 * 添加附件到当前邮件
	 *
	 * @param	string	文件数据
	 * @param	string	文件名
	 * @param	string	文件类型（MIME）
	 * @return	void
	 */
	public function addAttachment($data = '', $name = '', $ctype = 'application/octet-stream')
	{
		$this->parts[] = array(
			'ctype' => $ctype,
			'data' => $data,
			'encode' => 'base64',
			'name' => $name
		);
	}

	/**
	 * 加密附件
	 *
	 * @param	array	原始数据 [ctype,encode,name,data]
	 * @return	string	处理的数据
	 */
	private function _encodeAttachment($part = array())
	{
		$msg = chunk_split(base64_encode($part['data']));
		$headers = array();
		$header_str = '';
		$headers['Content-Type'] = $part['ctype'] . ($part['name'] ? "; name =\"" . $part['name'] . "\"" : '');
		$headers['Content-Transfer-Encoding'] = $part['encode'];
		$headers['Content-Disposition'] = "attachment; filename=\"" . $part['name'] . "\"";
		$headers = $this->_encodeHeaders($headers);
		foreach ($headers as $k => $v)
		{
			$header_str .= $k . ': ' . $v . self::header_eol;
		}
		$header_str .= "\n\n" . $msg . "\n";
		return $header_str;
	}

}
