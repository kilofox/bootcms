<?php

defined('SYSPATH') || exit('Access Denied.');

class HTTP_Exception_400 extends HTTP_Exception {

	/**
	 * @var integer HTTP 400 Bad Request
	 */
	protected $_code = 400;

}
