<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * STDERR log writer. Writes out messages to STDERR.
 * @package BootPHP
 * @category 日志
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 * @license    http://bootphpphp.com/license
 */
class Log_StdErr extends Log_Writer {

	/**
	 * Writes each of the messages to STDERR.
	 *
	 *     $writer->write($messages);
	 *
	 * @param array messages
	 * @return void
	 */
	public function write(array $messages)
	{
		// Set the log line format
		$format = 'time --- type: body';
		foreach ($messages as $message)
		{
			// Writes out each message
			fwrite(STDERR, PHP_EOL . strtr($format, $message));
		}
	}

}
