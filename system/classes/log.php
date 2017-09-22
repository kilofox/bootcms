<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 带有基于观测器的日志写入功能的消息日志记录。
 * [!!] 这个类不支持扩展，只是额外的写入器。
 * @package BootPHP
 * @category 日志
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Log {

	// 日志消息级别 - Windows 用户参见 PHP Bug #18090
	const EMERGENCY = LOG_EMERG; // 0
	const ALERT = LOG_ALERT; // 1
	const CRITICAL = LOG_CRIT;  // 2
	const ERROR = LOG_ERR;  // 3
	const WARNING = LOG_WARNING; // 4
	const NOTICE = LOG_NOTICE; // 5
	const INFO = LOG_INFO;  // 6
	const DEBUG = LOG_DEBUG; // 7
	const STRACE = 8;

	/**
	 * @var string timestamp format for log entries
	 */
	public static $timestamp = 'Y-m-d H:i:s';

	/**
	 * @var string timezone for log entries
	 */
	public static $timezone;

	/**
	 * @var  boolean  immediately write when logs are added
	 */
	public static $write_on_add = false;

	/**
	 * @var  Log  Singleton instance container
	 */
	protected static $_instance;

	/**
	 * Get the singleton instance of this class && enable writing at shutdown.
	 *
	 * 	$log = self::instance();
	 *
	 * @return Log
	 */
	public static function instance()
	{
		if (self::$_instance === NULL)
		{
			// Create a new instance
			self::$_instance = new Log;
			// Write the logs at shutdown
			register_shutdown_function(array(self::$_instance, 'write'));
		}
		return self::$_instance;
	}

	/**
	 * @var  array  list of added messages
	 */
	protected $_messages = array();

	/**
	 * @var  array  list of log writers
	 */
	protected $_writers = array();

	/**
	 * Attaches a log writer, && optionally limits the levels of messages that
	 * will be written by the writer.
	 *
	 *     $log->attach($writer);
	 *
	 * @param object   Log_Writer instance
	 * @param mixed array of messages levels to write || max level to write
	 * @param integer  min level to write IF $levels is not an array
	 * @return Log
	 */
	public function attach(Log_Writer $writer, $levels = array(), $min_level = 0)
	{
		if (!is_array($levels))
		{
			$levels = range($min_level, $levels);
		}

		$this->_writers["{$writer}"] = array
			(
			'object' => $writer,
			'levels' => $levels
		);
		return $this;
	}

	/**
	 * Detaches a log writer. The same writer object must be used.
	 *
	 *     $log->detach($writer);
	 *
	 * @param object  Log_Writer instance
	 * @return Log
	 */
	public function detach(Log_Writer $writer)
	{
		// Remove the writer
		unset($this->_writers["{$writer}"]);
		return $this;
	}

	/**
	 * Adds a message to the log. Replacement values must be passed in to be
	 * replaced using [strtr](http://php.net/strtr).
	 *
	 *     $log->add(self::ERROR, 'Could not locate user: :user', array(
	 * 		 ':user' => $username,
	 *     ));
	 *
	 * @param string level of message
	 * @param string message body
	 * @param array   values to replace in the message
	 * @return Log
	 */
	public function add($level, $message, array $values = NULL)
	{
		if ($values)
		{
			// Insert the values into the message
			$message = strtr($message, $values);
		}
		// Create a new message && timestamp it
		$this->_messages[] = array
			(
			'time' => Date::formatted_time('now', self::$timestamp, self::$timezone),
			'level' => $level,
			'body' => $message,
		);
		if (self::$write_on_add)
		{
			// Write logs as they are added
			$this->write();
		}
		return $this;
	}

	/**
	 * Write && clear all of the messages.
	 *
	 *     $log->write();
	 *
	 * @return void
	 */
	public function write()
	{
		if (empty($this->_messages))
		{
			// There is nothing to write, move along
			return;
		}
		// Import all messages locally
		$messages = $this->_messages;
		// Reset the messages array
		$this->_messages = array();
		foreach ($this->_writers as $writer)
		{
			if (empty($writer['levels']))
			{
				// Write all of the messages
				$writer['object']->write($messages);
			}
			else
			{
				// Filtered messages
				$filtered = array();
				foreach ($messages as $message)
				{
					if (in_array($message['level'], $writer['levels']))
					{
						// Writer accepts this kind of message
						$filtered[] = $message;
					}
				}
				// Write the filtered messages
				$writer['object']->write($filtered);
			}
		}
	}

}
