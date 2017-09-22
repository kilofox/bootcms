<?php

defined('SYSPATH') || exit('Access Denied.');

class HTTP_Exception_405 extends HTTP_Exception {

	/**
	 * @var integer HTTP 405 Method Not Allowed
	 */
	protected $_code = 405;

}
