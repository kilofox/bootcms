<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * File-based configuration reader. Multiple configuration directories can be
 * used by attaching multiple instances of this class to [BootPHP_Config].
 * @package		BootPHP
 * @category	配置
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Config_File {

	/**
	 * The directory where config files are located
	 * @var string
	 */
	protected $_directory = '';

	/**
	 * Creates a new file reader using the given directory as a config source
	 *
	 * @param string Configuration directory to search
	 */
	public function __construct($directory = 'config')
	{
		// Set the configuration directory name
		$this->_directory = trim($directory, '/');
	}

	/**
	 * Load and merge all of the configuration files in this group.
	 *
	 *     $config->load($name);
	 *
	 * @param   string  configuration group name
	 * @return  $this   current object
	 * @uses    BootPHP::load
	 */
	public function load($group)
	{
		$config = array();
		if ($files = BootPHP::find_file($this->_directory, $group, NULL, true))
		{
			foreach ($files as $file)
			{
				// Merge each file to the configuration array
				$config = Arr::merge($config, BootPHP::load($file));
			}
		}
		return $config;
	}

}
