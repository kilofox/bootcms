<?php

defined('SYSPATH') || exit('Access Denied.');

class HTTP_Exception_503 extends HTTP_Exception {

	/**
	 * @var integer HTTP 503 Service Unavailable
	 */
	protected $_code = 503;

}
