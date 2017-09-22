<?php

defined('SYSPATH') || exit('Access Denied.');

return array(
	CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; BootPHP v' . BootPHP::VERSION . ' +http://bootphpframework.org/)',
	CURLOPT_CONNECTTIMEOUT => 5,
	CURLOPT_TIMEOUT => 5,
	CURLOPT_HEADER => false,
);
