<?php

/**
 * 设置 PHP 错误报告等级。如果您在 php.ini 中设置了，那么就移除它吧。
 *
 * 在开发您的应用时，强烈建议开启 notices 和 strict 警告。
 * 用 E_ALL | E_STRICT 开启。
 *
 * 在生产环境下，忽略 notices 和 strict 警告是安全的。
 * 用 E_ALL ^ E_NOTICE 关闭。
 *
 * 当 PHP >= 5.3 时，推荐关闭 deprecated notices。
 * 用 E_ALL & ~E_DEPRECATED 关闭。
 */
error_reporting(E_ALL ^ E_NOTICE);

// 设置文档根目录的完整路径
define('DOCROOT', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

// 定义配置目录的绝对路径
define('APPPATH', realpath(DOCROOT . 'application') . DIRECTORY_SEPARATOR);
define('MODPATH', realpath(DOCROOT . 'modules') . DIRECTORY_SEPARATOR);
define('SYSPATH', realpath(DOCROOT . 'system') . DIRECTORY_SEPARATOR);
// 安装检测
if (file_exists('install.php'))
{
	return include 'install.php';
}
if (file_exists('upgrade.php'))
{
	return include 'upgrade.php';
}

// 定义应用的开始时间，用于性能分析。
defined('START_TIME') || define('START_TIME', microtime(true));

// 定义应用开始时的内存使用，用于性能分析。
defined('START_MEMORY') || define('START_MEMORY', memory_get_usage());

// 引导应用程序
require APPPATH . 'bootstrap.php';

/**
 * 执行主请求。可以传递一个 URI 源，例如：$_SERVER['PATH_INFO']。
 * 如果没有指定源，那么将自动检测 URI。
 */
echo Request::factory()
	->execute()
	->send_headers()
	->body();
