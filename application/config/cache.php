<?php

defined('SYSPATH') || exit('Access Denied.');
return array(
	/** 86400 = 1 天
	 * 31536000 = 1 年
	 * 您可以随时从管理后台手动清除缓存。
	 */
	'memcache' => array(
		'driver' => 'memcache',
		'default_expire' => 86400,
		'compression' => false, // 使用 Zlib 压缩（用整数可能会有问题）
		'servers' => array(
			array(
				'host' => 'localhost', // Memcache 服务器
				'port' => 11211, // Memcache 端口号
				'persistent' => false, // 持久连接
				'weight' => 1,
				'timeout' => 1,
				'retry_interval' => 15,
				'status' => true,
			),
		),
		'instant_death' => true, // 服务器首次失败后立即脱机（不做重试）
	),
	'memcachetag' => array(
		'driver' => 'memcachetag',
		'default_expire' => 86400,
		'compression' => false, // 使用 Zlib 压缩（用整数可能会有问题）
		'servers' => array(
			array(
				'host' => 'localhost', // Memcache 服务器
				'port' => 11211, // Memcache 端口号
				'persistent' => false, // 持久连接
				'weight' => 1,
				'timeout' => 1,
				'retry_interval' => 15,
				'status' => true,
			),
		),
		'instant_death' => true,
	),
	'apc' => array(
		'driver' => 'apc',
		'default_expire' => 86400,
	),
	'wincache' => array(
		'driver' => 'wincache',
		'default_expire' => 86400,
	),
	'sqlite' => array(
		'driver' => 'sqlite',
		'default_expire' => 86400,
		'database' => APPPATH . 'cache/bootphp-cache.sql3',
		'schema' => 'CREATE TABLE caches(id VARCHAR(127) PRIMARY KEY, tags VARCHAR(255), expiration INTEGER, cache TEXT)',
	),
	'eaccelerator' => array(
		'driver' => 'eaccelerator',
	),
	'xcache' => array(
		'driver' => 'xcache',
		'default_expire' => 86400,
	),
	'file' => array(
		'driver' => 'file',
		'cache_dir' => APPPATH . 'cache',
		'default_expire' => 86400,
		'ignore_on_delete' => array(
			'.gitignore',
			'.git',
			'.svn'
		)
	)
);
