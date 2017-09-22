<?php

defined('SYSPATH') || exit('Access Denied.');

class HTTP_Exception_412 extends HTTP_Exception {

	/**
	 * @var integer HTTP 412 Precondition Failed
	 */
	protected $_code = 412;

}
