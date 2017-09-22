<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 基于 Cookie 的 Session 类。
 *
 * @package BootPHP
 * @category Session
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Session_Cookie extends Session {

	/**
	 * @param string  $id  session id
	 * @return string
	 */
	protected function _read($id = NULL)
	{
		return Cookie::get($this->_name, NULL);
	}

	/**
	 * @return NULL
	 */
	protected function _regenerate()
	{
		// Cookie sessions have no id
		return NULL;
	}

	/**
	 * @return bool
	 */
	protected function _write()
	{
		return Cookie::set($this->_name, $this->__toString(), $this->_lifetime);
	}

	/**
	 * @return bool
	 */
	protected function _restart()
	{
		return true;
	}

	/**
	 * @return bool
	 */
	protected function _destroy()
	{
		return Cookie::delete($this->_name);
	}

}
