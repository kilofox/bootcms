<?php

defined('SYSPATH') || exit('Access Denied.');
/**
 * 全局配置
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
return array(
	'views' => array(
		'head' => 'header',
		'foot' => 'footer',
		'css' => array(
			array(
				'assets/css/common.css',
				array('type' => 'text/css', 'rel' => 'stylesheet')
			),
		),
		'js' => array(
			array(
				'assets/js/jquery.min.js',
				array('type' => 'text/javascript')
			),
			array(
				'assets/js/common.js',
				array('type' => 'text/javascript')
			),
		),
	),
	'homepage' => array(
		'head' => 'header',
		'foot' => 'footer',
		'css' => array(
			array(
				'assets/css/common.css',
				array('type' => 'text/css', 'rel' => 'stylesheet')
			),
		),
		'js' => array(
			array(
				'assets/js/jquery.min.js',
				array('type' => 'text/javascript')
			),
			array(
				'assets/js/common.js',
				array('type' => 'text/javascript')
			),
		)
	)
);
