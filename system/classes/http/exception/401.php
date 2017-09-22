<?php

defined('SYSPATH') || exit('Access Denied.');

class BootPHP_HTTP_Exception_401 extends HTTP_Exception {

	/**
	 * @var integer HTTP 401 Unauthorized
	 */
	protected $_code = 401;

}
