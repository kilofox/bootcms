<?php

defined('SYSPATH') || exit('Access Denied.');

class HTTP_Exception_409 extends HTTP_Exception {

	/**
	 * @var integer HTTP 409 Conflict
	 */
	protected $_code = 409;

}
