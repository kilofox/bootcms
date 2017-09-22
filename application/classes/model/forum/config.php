<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 论坛设置模型。
 *
 * @package	BootCMS
 * @category	论坛/模型
 * @author		Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Model_Forum_Config extends Model {

	private $_values = NULL;
	private $_loaded = false;

	/**
	 * 创建并返回一个新的模型对象。
	 *
	 * @return 对象
	 */
	public static function factory($name)
	{
		return parent::factory($name);
	}

	/**
	 * 根据主键加载数据，并返回对象。
	 *
	 * @param 名称
	 * @return 对象
	 */
	public function loadByName($name = '')
	{
		if ($name)
		{
			$sql = "SELECT * FROM `{$this->_db->tablePrefix}bb_config` WHERE `name` = '$name'";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建新设置。
	 *
	 * @param array 键值对
	 * @return mixed 插入的数据ID 或 false
	 */
	public function create($data)
	{
		if (is_object($data))
		{
			// 对象转数组（将来将 Validation 改写成操作对象后，就可以省略此步）
			$_arr = get_object_vars($data);
			foreach ($_arr as $key => $val)
			{
				$values[$key] = $val;
			}
			// 验证数据
			$validation = self::getValidation($values);
			if (!$validation->check())
			{
				throw new Validation_Exception('user', $validation);
			}
			return $this->_db->insert('bb_config', $values);
		}
		return false;
	}

	/**
	 * 更新设置。
	 *
	 * @return 执行结果
	 */
	public function update()
	{
		if ($this->_loaded)
		{
			// 对象转数组（将来将 Validation 改写成操作对象后，就可以省略此步）
			$_arr = get_object_vars($this->_values);
			foreach ($_arr as $key => $val)
			{
				$values[$key] = $val;
			}
			// 验证数据
			$validation = self::getValidation($values);
			if (!$validation->check())
			{
				throw new Validation_Exception('user', $validation);
			}
			$where = "`name` = '{$this->_values->name}'";
			return $this->_db->update('bb_config', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除设置。
	 *
	 * @return mixed 执行结果
	 */
	public function delete()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('bb_config', $where);
		}
		return false;
	}

	/**
	 * 数据验证。
	 *
	 * @param array 要验证的数据
	 * @return Validation
	 */
	public static function getValidation($values)
	{
		return Validation::factory($values)
				->rule('name', 'not_empty');
	}

	/**
	 * 取得所有设置。
	 *
	 * @param string	排序
	 * @return array 数据
	 */
	public function findAll()
	{
		$sql = "SELECT * FROM `{$this->_db->tablePrefix}bb_config`";
		$result = $this->_db->selectArray($sql);
		$configs = array();
		foreach ($result as &$node)
		{
			$configs[$node->name] = $node->content;
		}
		return $configs;
	}

	/**
	 * 根据名称取得设置值。
	 *
	 * @param mixed	名称列表
	 * @return array 设置值列表
	 */
	public function findByName($names = array())
	{
		if (!$names)
			return array();
		$condition = "= '$names'";
		if (is_array($names))
		{
			$condition = '';
			foreach ($names as $val)
			{
				$condition .= '\'' . $val . '\',';
			}
			$condition = substr($condition, 0, -1);
			$condition = "IN ($condition)";
		}
		$sql = "SELECT `name`, `content` FROM `{$this->_db->tablePrefix}bb_config` WHERE `name` $condition";
		return $this->_db->selectArray($sql);
	}

	/**
	 * 根据名称设置配置项。
	 *
	 * @param string	要设置的配置项
	 * @param mixed	新的值
	 * @param bool	是否与当前值相加
	 * @return mixed 执行结果
	 */
	public function setByName($name, $value, $add = false)
	{
		if (!$name)
			return false;
		if ($add)
		{
			$sql = "SELECT `content` FROM `{$this->_db->tablePrefix}bb_config` WHERE `name` = '$name'";
			$values = $this->_db->select($sql);
			$value += $values->content;
		}
		$where = "`name` = '$name'";
		return $this->_db->update('bb_config', array('content' => $value), $where);
	}

}
