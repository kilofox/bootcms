<?php

defined('SYSPATH') || exit('Access Denied.');

class HTTP_Exception_406 extends HTTP_Exception {

	/**
	 * @var integer HTTP 406 Not Acceptable
	 */
	protected $_code = 406;

}
