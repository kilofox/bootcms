<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 订单-商品模型。
 *
 * @package	BootCMS
 * @category	模型
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Model_Order_Product extends Model {

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
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "order_products` WHERE `id` = $id";
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
		if ($name)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "order_products` WHERE `product_name` = '$name'";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建新订单-商品。
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
			return $this->_db->insert('order_products', $values);
		}
		return false;
	}

	/**
	 * 更新订单-商品。
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
			return $this->_db->update('order_products', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除订单-商品。
	 *
	 * @return	mixed	执行结果
	 */
	public function delete()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('order_products', $where);
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
				->rule('user_id', 'not_empty')
				->rule('order_id', 'not_empty')
				->rule('product_id', 'not_empty')
				->rule('price', 'not_empty')
				->rule('quantity', 'not_empty')
				->rule('user_id', 'digit')
				->rule('order_id', 'digit')
				->rule('product_id', 'digit')
				->rule('price', 'numeric')
				->rule('quantity', 'digit');
	}

	/**
	 * 取得所有订单-商品。
	 *
	 * @return	array	数据
	 */
	public function findAll()
	{
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "order_products`";
		return $this->_db->selectArray($sql);
	}

	/**
	 * 根据订单号取得订单-商品。
	 *
	 * @param	string	订单号
	 * @return	array	数据
	 */
	public function findByOrder($orderId = 0)
	{
		$sql = "SELECT op.*, p.product_name FROM `" . $this->_db->tablePrefix . "order_products` op LEFT JOIN `" . $this->_db->tablePrefix . "products` p ON op.product_id = p.id WHERE op.`order_id` = $orderId";
		return $this->_db->selectArray($sql);
	}

}
