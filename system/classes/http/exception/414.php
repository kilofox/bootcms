<?php

defined('SYSPATH') || exit('Access Denied.');

class HTTP_Exception_414 extends HTTP_Exception {

	/**
	 * @var integer HTTP 414 Request-URI Too Long
	 */
	protected $_code = 414;

}
