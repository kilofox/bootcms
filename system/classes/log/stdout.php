<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * STDOUT log writer. Writes out messages to STDOUT.
 * @package BootPHP
 * @category 日志
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 * @license    http://bootphpphp.com/license
 */
class Log_StdOut extends Log_Writer {

	/**
	 * Writes each of the messages to STDOUT.
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
			fwrite(STDOUT, PHP_EOL . strtr($format, $message));
		}
	}

}
