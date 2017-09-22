<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * Syslog log writer.
 * @package BootPHP
 * @category 日志
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Log_Syslog extends Log_Writer {

	/**
	 * @var  string  The syslog identifier
	 */
	protected $_ident;

	/**
	 * @var  array  log levels
	 */
	protected $_syslog_levels = array('ERROR' => LOG_ERR,
		'CRITICAL' => LOG_CRIT,
		'STRACE' => LOG_ALERT,
		'ALERT' => LOG_WARNING,
		'INFO' => LOG_INFO,
		'DEBUG' => LOG_DEBUG);

	/**
	 * Creates a new syslog logger.
	 *
	 * @see http://us2.php.net/openlog
	 *
	 * @param string  syslog identifier
	 * @param int     facility to log to
	 * @return void
	 */
	public function __construct($ident = 'BootPHPPHP', $facility = LOG_USER)
	{
		$this->_ident = $ident;
		// Open the connection to syslog
		openlog($this->_ident, LOG_CONS, $facility);
	}

	/**
	 * Writes each of the messages into the syslog.
	 *
	 * @param array messages
	 * @return void
	 */
	public function write(array $messages)
	{
		foreach ($messages as $message)
		{
			syslog($message['level'], $message['body']);
		}
	}

	/**
	 * Closes the syslog connection
	 *
	 * @return void
	 */
	public function __destruct()
	{
		// Close connection to syslog
		closelog();
	}

}
