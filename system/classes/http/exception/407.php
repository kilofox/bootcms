<?php

defined('SYSPATH') || exit('Access Denied.');

class HTTP_Exception_407 extends HTTP_Exception {

	/**
	 * @var integer HTTP 407 Proxy Authentication Required
	 */
	protected $_code = 407;

}
