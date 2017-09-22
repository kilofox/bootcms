<?php

defined('SYSPATH') || exit('Access Denied.');

class HTTP_Exception_416 extends HTTP_Exception {

	/**
	 * @var integer HTTP 416 Request Range Not Satisfiable
	 */
	protected $_code = 416;

}
