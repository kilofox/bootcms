<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 验证异常
 *
 * @package BootPHP
 * @category 异常
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Validation_Exception extends BootPHP_Exception {

	/**
	 * 验证对象的数组
	 * @var array
	 */
	protected $_objects = array();

	/**
	 * 创建该异常的模型的别名
	 * @var string
	 */
	protected $_alias = NULL;

	/**
	 * 为指定模型构造一个新的异常
	 *
	 * @param string 查找错误信息时使用的别名
	 * @param Validation 模型的 Validation 对象
	 * @param string 错误信息
	 * @param array 错误信息的值的数组
	 * @param integer 异常的错误代码
	 * @return void
	 */
	public function __construct($alias, Validation $object, $message = 'Failed to validate array', array $values = NULL, $code = 0)
	{
		$this->_alias = $alias;
		$this->_objects['_object'] = $object;
		$this->_objects['_has_many'] = false;
		parent::__construct($message, $values, $code);
	}

	/**
	 * 返回一个该异常的所有 Validation 对象的错误的合并了的数组
	 *
	 *     // 加载来自 messages/validation/user.php 的 Model_User 错误
	 *     $e->errors('validation');
	 *
	 * @param string 错误信息所在目录
	 * @param mixed 翻译信息
	 * @return array
	 */
	public function errors($directory = NULL, $translate = true)
	{
		return $this->generateErrors($this->_alias, $this->_objects, $directory, $translate);
	}

	/**
	 * 以递归方式来获取异常中的所有错误
	 *
	 * @param string 信息文件的别名
	 * @param array 从中取得错误的验证对象的数组
	 * @param string 错误信息所在目录
	 * @param mixed 翻译信息
	 * @return array
	 */
	protected function generateErrors($alias, array $array, $directory, $translate)
	{
		$errors = array();
		foreach ($array as $key => $object)
		{
			if (is_array($object))
			{
				$errors[$key] = $this->generate_errors($key, $object, $directory, $translate);
			}
			elseif ($object instanceof Validation)
			{
				if ($directory === NULL)
				{
					// 返回原始的错误
					$file = NULL;
				}
				else
				{
					$file = trim($directory . '/' . $alias, '/');
				}
				// 合并错误数组
				$errors += $object->errors($file, $translate);
			}
		}
		return $errors;
	}

}
