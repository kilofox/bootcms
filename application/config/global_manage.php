<?php

defined('SYSPATH') || exit('Access Denied.');
/**
 * 后台全局配置
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
return array(
	'views_manage' => array(
		'head' => 'manage/header_manage',
		'foot' => 'manage/footer_manage',
		'css' => array(
			array(
				'assets_manage/css/admin.css',
				array('type' => 'text/css', 'rel' => 'stylesheet')
			)
		),
		'js' => array(
			array(
				'assets_manage/js/jquery.min.js',
				array('type' => 'text/javascript')
			),
			array(
				'assets_manage/js/common.js',
				array('type' => 'text/javascript')
			)
		)
	),
	'views_login' => array(
		'body' => 'manage/login',
		'css' => array(
			array(
				'assets_manage/css/admin.css',
				array('type' => 'text/css', 'rel' => 'stylesheet')
			)
		),
		'js' => array(
			array(
				'assets_manage/js/jquery.min.js',
				array('type' => 'text/javascript')
			),
			array(
				'assets_manage/js/common.js',
				array('type' => 'text/javascript')
			)
		)
	)
);
