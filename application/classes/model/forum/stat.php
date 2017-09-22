<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 论坛统计模型。
 *
 * @package	BootCMS
 * @category	论坛/模型
 * @author		Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Model_Forum_Stat extends Model {

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
			$sql = "SELECT * FROM `{$this->_db->tablePrefix}bb_stats` WHERE `name` = '$name'";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建新统计。
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
			return $this->_db->insert('bb_stats', $values);
		}
		return false;
	}

	/**
	 * 更新统计。
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
			return $this->_db->update('bb_stats', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除统计。
	 *
	 * @return mixed 执行结果
	 */
	public function delete()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('bb_stats', $where);
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
	 * 取得所有统计。
	 *
	 * @param string	排序
	 * @return array 数据
	 */
	public function findAll($order_by = '')
	{
		$order_by = $order_by ? $order_by : 'id';
		$sql = "SELECT * FROM `{$this->_db->tablePrefix}bb_stats` ORDER BY $order_by";
		$result = $this->_db->selectArray($sql);
		return $result;
	}

	/**
	 * 根据名称取得统计值。
	 *
	 * @param mixed	名称列表
	 * @return array 统计值列表
	 */
	public function findByName($names = array())
	{
		if (!$names || !is_array($names))
			return array();
		$condition = '';
		foreach ($names as $val)
		{
			$condition .= '\'' . $val . '\',';
		}
		$condition = substr($condition, 0, -1);
		$sql = "SELECT `name`, `content` FROM `{$this->_db->tablePrefix}bb_stats` WHERE `name` IN ($condition)";
		return $this->_db->selectArray($sql);
	}

	/**
	 * 根据名称设置统计值。
	 *
	 * @param string	要设置的统计值
	 * @param mixed	新的值
	 * @param bool	是否与当前值相加
	 * @return mixed 执行结果
	 */
	public function setByName($name, $value = 0, $add = false)
	{
		if (!$name)
			return false;
		if ($add)
		{
			$sql = "SELECT `content` FROM `{$this->_db->tablePrefix}bb_stats` WHERE `name` = '$name'";
			$values = $this->_db->select($sql);
			$value += $values->content;
		}
		$where = "`name` = '$name'";
		return $this->_db->update('bb_stats', array('content' => $value), $where);
	}

}
