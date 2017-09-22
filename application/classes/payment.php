<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 支付方式库。
 *
 * @package	BootCMS
 * @category	辅助类
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
abstract class Payment {

	// Payment 实例
	protected static $_instance;
	public static $default = 'alipay';

	/**
	 * 单例模式。
	 *
	 * @return Payment
	 */
	public static function instance($group = NULL)
	{
		if (!isset(self::$_instance))
		{
			// 如果没有提供组，则使用默认设置
			if ($group === NULL)
			{
				$group = self::$default;
			}
			$config = BootPHP::$config->load('payment');
			if (!$config->offsetExists($group))
			{
				throw new Cache_Exception(
				'Failed to load BootPHP Payment group: :group', array(':group' => $group)
				);
			}
			$config = $config->get($group);
			// 设置支付类名
			$class = 'Payment_' . ucfirst($config['driver']);
			// 创建一个新的支付实例
			self::$_instance = new $class($config);
		}
		return self::$_instance;
	}

	/**
	 * 加载 Session 和配置选项
	 * @return	void
	 */
	public function __construct($config = array())
	{
		// 保存对象中的配置信息
		$this->config = $config;
	}

}
