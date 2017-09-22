<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * Wrapper for configuration arrays. Multiple configuration readers can be
 * attached to allow loading configuration from files, database, etc.
 *
 * Configuration directives cascade across config sources in the same way that
 * files cascade across the filesystem.
 *
 * Directives from sources high in the sources list will override ones from those
 * below them.
 *
 * @package BootPHP
 * @category 配置
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class BootPHP_Config {

	// Configuration readers
	protected $_sources = array();

	/**
	 * Attach a configuration reader. By default, the reader will be added as
	 * the first used reader. However, if the reader should be used only when
	 * all other readers fail, use `false` for the second parameter.
	 *
	 *     $config->attach($reader);		// Try first
	 *     $config->attach($reader, false);	// Try last
	 *
	 * @param   object   BootPHP_Config_Source instance
	 * @param   boolean  add the reader as the first used object
	 * @return  $this
	 */
	public function attach($source, $first = true)
	{
		if ($first === true)
		{
			// Place the log reader at the top of the stack
			array_unshift($this->_sources, $source);
		}
		else
		{
			// Place the reader at the bottom of the stack
			$this->_sources[] = $source;
		}
		return $this;
	}

	/**
	 * Detach a configuration reader.
	 *
	 *     $config->detach($reader);
	 *
	 * @param   object  BootPHP_Config_Source instance
	 * @return  $this
	 */
	public function detach($source)
	{
		if (($key = array_search($source, $this->_sources)) !== false)
		{
			// Remove the writer
			unset($this->_sources[$key]);
		}
		return $this;
	}

	/**
	 * Load a configuration group. Searches all the config sources, merging all the
	 * directives found into a single config group.  Any changes made to the config
	 * in this group will be mirrored across all writable sources.
	 *
	 *     $array = $config->load($name);
	 *
	 * See [BootPHP_Config_Group] for more info
	 *
	 * @param string configuration group name
	 * @return object BootPHP_Config_Group
	 * @throws BootPHP_Exception
	 */
	public function load($group)
	{
		if (!count($this->_sources))
		{
			throw new BootPHP_Exception('No configuration sources attached');
		}
		if (empty($group))
		{
			throw new BootPHP_Exception('Need to specify a config group');
		}
		if (!is_string($group))
		{
			throw new BootPHP_Exception('Config group must be a string');
		}
		if (strpos($group, '.') !== false)
		{
			// Split the config group and path
			list ($group, $path) = explode('.', $group, 2);
		}
		if (isset($this->_groups[$group]))
		{
			if (isset($path))
			{
				return Arr::path($this->_groups[$group], $path, NULL, '.');
			}
			return $this->_groups[$group];
		}
		$config = array();
		// We search from the "lowest" source and work our way up
		$sources = array_reverse($this->_sources);
		foreach ($sources as $source)
		{
			if ($source instanceof Config_File)
			{
				if ($source_config = $source->load($group))
				{
					$config = Arr::merge($config, $source_config);
				}
			}
		}
		$this->_groups[$group] = new Config_Group($this, $group, $config);
		if (isset($path))
		{
			return Arr::path($config, $path, NULL, '.');
		}
		return $this->_groups[$group];
	}

	/**
	 * Copy one configuration group to all of the other writers.
	 *
	 *     $config->copy($name);
	 *
	 * @param string configuration group name
	 * @return $this
	 */
	public function copy($group)
	{
		// Load the configuration group
		$config = $this->load($group);
		foreach ($config->as_array() as $key => $value)
		{
			$this->_write_config($group, $key, $value);
		}
		return $this;
	}

	/**
	 * Callback used by the config group to store changes made to configuration
	 *
	 * @param string  Group name
	 * @param string Variable name
	 * @param mixed The new value
	 * @return BootPHP_Config Chainable instance
	 */
	public function _write_config($group, $key, $value)
	{
		foreach ($this->_sources as $source)
		{
			if (!($source instanceof BootPHP_Config_Writer))
			{
				continue;
			}
			// Copy each value in the config
			$source->write($group, $key, $value);
		}
		return $this;
	}

}
