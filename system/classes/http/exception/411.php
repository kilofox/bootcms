<?php

defined('SYSPATH') || exit('Access Denied.');

class HTTP_Exception_411 extends HTTP_Exception {

	/**
	 * @var integer HTTP 411 Length Required
	 */
	protected $_code = 411;

}
