<?php

defined('SYSPATH') || exit('Access Denied.');

class HTTP_Exception_410 extends HTTP_Exception {

	/**
	 * @var integer HTTP 410 Gone
	 */
	protected $_code = 410;

}
