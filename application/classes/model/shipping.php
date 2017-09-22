<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 配送方式模型。
 *
 * @package	BootCMS
 * @category	模型
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Model_Shipping extends Model {

	private $_values = NULL;
	private $_loaded = false;

	/**
	 * 创建并返回一个新的模型对象。
	 *
	 * @return	对象
	 */
	public static function factory($name)
	{
		return parent::factory($name);
	}

	/**
	 * 根据主键加载数据，并返回对象。
	 *
	 * @return	对象
	 */
	public function load($id = 0)
	{
		if (is_numeric($id) && $id > 0)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "shippings` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 根据名称加载数据，并返回对象。
	 *
	 * @return	对象
	 */
	public function loadByName($name)
	{
		if ($id)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "shippings` WHERE `name` = '$name'";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建新配送方式。
	 *
	 * @param	array	键值对
	 * @return	mixed	插入的数据ID 或 false
	 */
	public function create($data)
	{
		if (is_object($data))
		{
			// 对象转数组（将来将 Validation 改写成操作对象后，就可以省略此步）
			$values = array();
			foreach ($data as $key => $val)
			{
				$values[$key] = $val;
			}
			// 验证数据
			$validation = self::getValidation($values);
			if (!$validation->check())
			{
				throw new Validation_Exception('user', $validation);
			}
			return $this->_db->insert('shippings', $values);
		}
		return false;
	}

	/**
	 * 更新配送方式。
	 *
	 * @return	执行结果
	 */
	public function update()
	{
		if ($this->_loaded)
		{
			// 对象转数组（将来将 Validation 改写成操作对象后，就可以省略此步）
			$values = array();
			foreach ($this->_values as $key => $val)
			{
				$values[$key] = $val;
			}
			// 验证数据
			$validation = self::getValidation($values);
			if (!$validation->check())
			{
				throw new Validation_Exception('user', $validation);
			}
			$where = "`id` = {$this->_values->id}";
			return $this->_db->update('shippings', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除配送方式。
	 *
	 * @return	mixed	执行结果
	 */
	public function delete()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('shippings', $where);
		}
		return false;
	}

	/**
	 * 数据验证。
	 *
	 * @param	array	$values
	 * @return	Validation
	 */
	public static function getValidation($values)
	{
		return Validation::factory($values)
				->rule('shipping_name', 'not_empty')
				->rule('insurance', 'not_empty')
				->rule('support_cod', 'not_empty');
	}

	/**
	 * 取得所有配送方式。
	 *
	 * @return	array	数据
	 */
	public function findAll($status = 0)
	{
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "shippings`";
		return $this->_db->selectArray($sql);
	}

	/**
	 * 根据状态取得配送方式。
	 *
	 * @param	integer	状态
	 * @return	array	数据
	 */
	public function findByStatus($status = 0)
	{
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "shippings` WHERE `status` = '$status' ORDER BY `list_order`";
		return $this->_db->selectArray($sql);
	}

}
