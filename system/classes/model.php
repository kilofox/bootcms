<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 模型基类。所有的模型都应该继承这个类。
 *
 * @package BootPHP
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
abstract class Model {

	protected $_db = NULL;

	public function __construct()
	{
		if (!is_object($this->_db))
		{
			$this->_db = Database::instance();
		}
	}

	/**
	 * 创建一个新的模型实例
	 *
	 * 	$model = Model::factory($name);
	 *
	 * @param string 模型名
	 * @return 模型
	 */
	public static function factory($name)
	{
		// 添加模型前缀
		$class = 'Model_' . ucfirst($name);
		return new $class;
	}

}
