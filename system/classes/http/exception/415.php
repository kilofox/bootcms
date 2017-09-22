<?php

defined('SYSPATH') || exit('Access Denied.');

class HTTP_Exception_415 extends HTTP_Exception {

	/**
	 * @var integer HTTP 415 Unsupported Media Type
	 */
	protected $_code = 415;

}
