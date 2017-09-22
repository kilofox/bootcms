<?php

defined('SYSPATH') || exit('Access Denied.');

class HTTP_Exception_502 extends HTTP_Exception {

	/**
	 * @var integer HTTP 502 Bad Gateway
	 */
	protected $_code = 502;

}
