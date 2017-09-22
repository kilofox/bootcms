<?php

defined('SYSPATH') || exit('Access Denied.');
return array(
	'alipay' => array(
		'driver' => 'alipay',
		'service_type' => 0,
		'account' => 'username',
		'partner' => '2088567890123456',
		'key' => 'abcdeabcde1234567890abcdeabcde12',
		'sign_type' => 'MD5',
		'input_charset' => 'utf-8',
		'transport' => 'http',
		'cacert' => getcwd() . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'cacert.pem'
	)
);
