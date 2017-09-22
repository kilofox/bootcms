<?php

defined('SYSPATH') || exit('Access Denied.');

class HTTP_Exception_505 extends HTTP_Exception {

	/**
	 * @var integer HTTP 505 HTTP Version Not Supported
	 */
	protected $_code = 505;

}
